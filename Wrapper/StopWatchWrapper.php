<?php

namespace Qubit\Bundle\LogBundle\Wrapper;

use Symfony\Component\Stopwatch\Stopwatch;

/**
 * StopWatchWrapper
 * @package Qubit\Bundle\LogBundle\StopWatchWrapper
 */
class StopWatchWrapper
{
    const DURATION_CODE_HEADER_NAME = 'X-Durations';

    private $stopwatch;
    private $name;
    
    /**
     * __construct
     */
    public function __construct()
    {
        $this->stopwatch = new Stopwatch();
        
        $this->name = 'log';
        
        $this->stopwatch->start($this->name);
    }
    
    /**
     * startStopWatch
     */
    public function startStopWatch()
    {
        $this->stopwatch->start($this->name);
    }
    
    /**
     * getDurationLap
     *
     * @return int Duration of the event
     */
    public function getDurationLap()
    {
        $this->stopwatch->lap($this->name);
        
        return $this->stopwatch->getEvent($this->name)->getDuration();
    }
    
    /**
     * finishStopWatch
     *
     * @return int Duration of the event
     */
    public function finishStopWatch()
    {
        $this->stopwatch->stop($this->name);
        
        return $this->stopwatch->getEvent($this->name)->getDuration();
    }
    
}
