<?php

namespace Qubit\Bundle\LogBundle\Processors;

use Qubit\Bundle\UtilsBundle\Generator\TrackingCode;

/**
 * TrackingCodeProcessor
 * @package Qubit\Bundle\LogBundle\Processors\TrackingCodeProcessor
 */
class TrackingCodeProcessor
{
    private $trackingCodeService;

    /**
     * __construct
     *
     * @param object $trackingCode TrackingCode Object
     */
    public function __construct(TrackingCode $trackingCode)
    {
        $this->trackingCodeService = $trackingCode;
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
        $record['extra']['tracking_code'] = $this->trackingCodeService->getTrackingCode();
        
        return $record;
    }
    
}
