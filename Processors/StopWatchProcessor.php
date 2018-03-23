<?php

namespace Qubit\Bundle\LogBundle\Processors;

use Qubit\Bundle\LogBundle\Wrapper\StopWatchWrapper;

/**
 * StopWatchProcessor
 * @package Qubit\Bundle\LogBundle\Processors\StopWatchProcessor
 */
class StopWatchProcessor
{
    private $stopWatch;
    
    /**
     * __construct
     *
     * @param object $stopWatch StopWatchWrapper Object
     */
    public function __construct(StopWatchWrapper $stopWatch)
    {
        $this->stopWatch = $stopWatch;
    }
    
    /**
     * __invoke
     * 
     * @param array $record Array with the records to log with monolog
     * 
     * @return array
     */
    public function __invoke(array $record)
    {
        $record['extra']['duration_time'] = $this->stopWatch->getDurationLap();
        
        return $record;
    }
}
