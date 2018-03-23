<?php

namespace Qubit\Bundle\LogBundle\Tests\Core;

use Symfony\Bundle\MonologBundle\MonologBundle;
use Qubit\Bundle\LogBundle\LogBundle;
use Qubit\Bundle\UtilsBundle\UtilsBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    /**
     * registerBundles
     * 
     * @return array Array of bundles
     */
    public function registerBundles()
    {
        $bundles = array();
        
        if (in_array($this->getEnvironment(), array('test'))) {
            $bundles[] = new MonologBundle();
            $bundles[] = new UtilsBundle();
            $bundles[] = new LogBundle();
        }
        
        return $bundles;
    }
    
    /**
     * registerContainerConfiguration
     * 
     * @param object $loader LoaderInterface Object
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config.yml');
    }
    
}
