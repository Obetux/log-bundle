<?php

namespace Qubit\Bundle\LogBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Qubit\Bundle\UtilsBundle\Generator\TrackingCode;
use Qubit\Bundle\LogBundle\Wrapper\StopWatchWrapper;
use Monolog\Logger;

class LogRequestListener
{
    protected $container;
    protected $stopWatchWrapper;
    protected $logger;
    protected $trackingCodeService;
    
    /**
     * __construct
     *
     * @param object $container ContainerInterface Object
     */
    public function __construct(StopWatchWrapper $stopwatchWrapper, Logger $logger, TrackingCode $trackingCode)
    {
        $this->stopWatchWrapper = $stopwatchWrapper;
        $this->logger = $logger;
        $this->trackingCodeService = $trackingCode;
    }
    
    /**
     * onKernelRequest
     * Start the stopwatch event
     *
     * @param object $event GetResponseEvent Object
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->stopWatchWrapper->startStopWatch();
        $this->logger->info('Request recibida');
        
        $event->getRequest()->attributes->add(array('request-logged' => true));
        
        // Traqueo en request
        $request = $event->getRequest();
        
        $trackingCodeHeader = $request->headers->get(TrackingCode::TRACKING_CODE_HEADER_NAME);
       
        if (!is_null($trackingCodeHeader)) {
            $this->trackingCodeService->setTrackingCode($trackingCodeHeader);
            $this->trackingCodeService->setTrackingCodeHeader();
        }
    }
    
    /**
     * onKernelTerminate
     * Response the stopwatch event
     *
     * @param object $event FilterResponseEvent Object
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $this->stopWatchWrapper->startStopWatch();
        $this->logger->info('Request terminada');
        
        $event->getResponse()->headers->add(array('response-logged' => true));
        $event->getResponse()->headers->set(TrackingCode::TRACKING_CODE_HEADER_NAME, $this->trackingCodeService->getTrackingCode());
        $event->getResponse()->headers->set(StopWatchWrapper::DURATION_CODE_HEADER_NAME, $this->stopWatchWrapper->getDurationLap());
    }
}
