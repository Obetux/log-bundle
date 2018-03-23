<?php

namespace Qubit\Bundle\LogBundle\Tests;

use Qubit\Bundle\LogBundle\Processors\TrackingCodeProcessor;
use Qubit\Bundle\UtilsBundle\Generator\TrackingCode;
use Qubit\Bundle\LogBundle\Wrapper\StopWatchWrapper;
use Qubit\Bundle\LogBundle\Processors\StopWatchProcessor;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Qubit\Bundle\LogBundle\Listener\LogRequestListener;
use Qubit\Bundle\LogBundle\Tests\Core\TestKernel;

class BaseLoggerTest extends \PHPUnit\Framework\TestCase
{
    private $container;
    private $trackingCode;
    private $trackingCodeProcessor;
    private $stopWatchWrapper;
    private $stopWatchProcessor;
    
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        
        require_once __DIR__.'/Core/TestKernel.php';
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $this->container = $kernel->getContainer();
        
        $this->trackingCode = new TrackingCode();
        $this->trackingCodeProcessor = new TrackingCodeProcessor($this->trackingCode);
        
        $this->stopWatchWrapper = new StopWatchWrapper();
        $this->stopWatchWrapper->startStopWatch();
        $this->stopWatchProcessor = new StopWatchProcessor($this->stopWatchWrapper);
    }
    
    /**
     * testGetLogger
     */
    public function testGetTrackingCodeProcessor()
    {
        $apiLogger = $this->container->get('qubit.logger.trackingcode.processor');

        $this->assertEquals(
            TrackingCodeProcessor::class,
            get_class($apiLogger),
            'ContainerBuilder class assert instanceof TrackingCodeProcessor class'
        );
    }
    
    /*
     * testTrackingCodeProcessor
     */
    public function testTrackingCodeProcessor()
    {
        $record = $this->trackingCodeProcessor->__invoke(array());
        $this->assertArrayHasKey('extra', $record);
        
        $record2 = $this->trackingCodeProcessor->__invoke(array());
        $this->assertArrayHasKey('extra', $record2);
        $this->assertEquals($record2['extra'], $record['extra']);
        
        $newTrackingCode = new TrackingCode();
        $newTrackingCodeProcessor = new TrackingCodeProcessor($newTrackingCode);
        $record3 = $newTrackingCodeProcessor->__invoke(array());
        $this->assertNotEquals($record3, $record);
    }
    
    /**
     * testGetStopWatchWrapper
     */
    public function testGetStopWatchWrapper()
    {
        $stopWatchWrapper = $this->container->get('qubit.logger.stopwatch_wrapper');

        $this->assertEquals(
            StopWatchWrapper::class,
            get_class($stopWatchWrapper),
            'ContainerBuilder class assert instanceof $stopWatchWrapper class'
        );
    }
    
    /**
     * testStopWatch
     */
    public function testStopWatchWrapper()
    {
        $time1 = $this->stopWatchWrapper->getDurationLap('log');
        $this->assertEquals(0, $time1);

        sleep(5);
        
        $time2 = $this->stopWatchWrapper->getDurationLap('log');
        $this->assertNotEquals($time1, $time2);
    }
    
    /**
     * testGetStopWatchProcessor
     */
    public function testGetStopWatchProcessor()
    {
        $stopWatchProcessor = $this->container->get('qubit.logger.stopwatch.processor');

        $this->assertEquals(
            StopWatchProcessor::class,
            get_class($stopWatchProcessor),
            'ContainerBuilder class assert instanceof StopWatchProcessor class'
        );
    }
    
    /**
     * testStopWatchProcessor
     */
    public function testStopWatchProcessor()
    {
        $record = $this->stopWatchProcessor->__invoke(array());
        $this->assertArrayHasKey('extra', $record);
        $this->assertArrayHasKey('duration_time', $record['extra']);
    }
    
    /**
     * testKernelRequest
     */
    public function testKernelRequest()
    {
        $dispatcher = new EventDispatcher();
        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')->getMock();
        
        $logRequestListener = new LogRequestListener(
            $this->stopWatchWrapper,
            $this->container->get('logger'),
            $this->trackingCode
        );
        
        $dispatcher->addListener(KernelEvents::REQUEST, array($logRequestListener, 'onKernelRequest'));
        $dispatcher->addListener(KernelEvents::RESPONSE, array($logRequestListener, 'onKernelResponse'));
        
        $request = new Request();
        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);
        
        $eventDispatcherResponse = $dispatcher->dispatch(KernelEvents::REQUEST, $event);
        $event = new FilterResponseEvent(
            $kernel,
            $eventDispatcherResponse->getRequest(),
            HttpKernelInterface::MASTER_REQUEST,
            new Response()
        );
        $eventResponseDispatcherResponse = $dispatcher->dispatch(KernelEvents::RESPONSE, $event);
        
        $this->assertTrue($eventDispatcherResponse->getRequest()->get('request-logged'));
        
        $this->assertTrue($eventResponseDispatcherResponse->getResponse()->headers->get('response-logged'));
    }
}
