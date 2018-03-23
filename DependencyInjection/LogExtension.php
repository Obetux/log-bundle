<?php

namespace Qubit\Bundle\LogBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class LogExtension extends Extension implements PrependExtensionInterface
{
    private $requiredBundles = ['UtilsBundle'];
    
    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        // Obtenemos la configuración específica del qubit_logs bundle
        $qubitLogBundleConfiguration = $container->getExtensionConfig('qubit_logs');
        // Obtenemos los bundles cargados por el kernel
        $bundles = $container->getParameter('kernel.bundles');

        $this->checkRequiredBundles($bundles);

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $qubitLogBundleConfiguration);
        
        // Definimos el proceso del StopWatchWrapper
        $stopWatchWrapper = new Definition('Qubit\Bundle\LogBundle\Wrapper\StopWatchWrapper');
        $stopWatchWrapper->setPublic(true);
        $container->setDefinition('qubit.logger.stopwatch_wrapper', $stopWatchWrapper);
        
        // Definimos como servicio el IntrospectionProcessor para que las apps no tengan que hacerlo
        $introspectionDefinition = new Definition('Monolog\Processor\IntrospectionProcessor');
        $introspectionDefinition->addTag('monolog.processor');
        $container->setDefinition('qubit.logger.introspection.processor', $introspectionDefinition);
        
        // Definimos como servicio el TrackingCodeProcessor para que las apps no tengan que hacerlo
        $trackingCodeDefinition = new Definition('Qubit\Bundle\LogBundle\Processors\TrackingCodeProcessor');
        $trackingCodeDefinition->addArgument(new Reference('qubit.utilities.tracking_code'));
        $trackingCodeDefinition->addTag('monolog.processor');
        $container->setDefinition('qubit.logger.trackingcode.processor', $trackingCodeDefinition);
        
        // Definimos como servicio el StopWatchProcessor para que las apps no tengan que hacerlo
        $stopWatchDefinition = new Definition('Qubit\Bundle\LogBundle\Processors\StopWatchProcessor');
        $stopWatchDefinition->addArgument($container->getDefinition('qubit.logger.stopwatch_wrapper'));
        $stopWatchDefinition->addTag('monolog.processor');
        $container->setDefinition('qubit.logger.stopwatch.processor', $stopWatchDefinition);
        
        // Definimos como servicio el Formatter QubitLineFormatter para que las apps puedan usarlo
        $formatterDefinition = new Definition('Qubit\Bundle\LogBundle\Formatters\QubitLineFormatter');
        $container->setDefinition('qubit.line.formatter', $formatterDefinition);

        // Registramos el servicio Kernel Request
        $container->register(
            'qubit.logger.listener.request_listener',
            'Qubit\Bundle\LogBundle\Listener\LogRequestListener'
        )
            ->addTag(
                'kernel.event_listener',
                array('event' => 'kernel.request', 'method' => 'onKernelRequest', 'priority' => '10000')
            )
            ->addArgument(new Reference('qubit.logger.stopwatch_wrapper'))
            ->addArgument(new Reference('monolog.logger'))
            ->addArgument(new Reference('qubit.utilities.tracking_code'));

        // Registramos el servicio Kernel Response
        $container->register(
            'qubit.logger.listener.terminate_listener',
            'Qubit\Bundle\LogBundle\Listener\LogRequestListener'
        )
            ->addTag('kernel.event_listener', array('event' => 'kernel.response', 'method' => 'onKernelResponse'))
            ->addArgument(new Reference('qubit.logger.stopwatch_wrapper'))
            ->addArgument(new Reference('monolog.logger'))
            ->addArgument(new Reference('qubit.utilities.tracking_code'));
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
    }
    
    /**
     * checkRequiredBundles
     *
     * @param array $loadedBundles Array of bundles loaded by the kernel
     */
    private function checkRequiredBundles(array $loadedBundles)
    {
        // Chequeo que los bundles necesarios estén
        foreach ($this->requiredBundles as $bundle) {
            if (false === isset($loadedBundles[$bundle])) {
                throw new InvalidConfigurationException('LogBundle require que agregues el bundle: ' . $bundle);
            }
        }
        
        $newArray = array_keys($loadedBundles);
        
        // Chequeo que primero este el Utils para evitar romper
        $utilsBundleKey = array_search('UtilsBundle', $newArray);
        $logBundleKey = array_search('LogBundle', $newArray);

        if ($utilsBundleKey > $logBundleKey) {
            throw new InvalidConfigurationException('Utils Bundle debe estar antes del LogBundle en el AppKernel.php');
        }
    }
}
