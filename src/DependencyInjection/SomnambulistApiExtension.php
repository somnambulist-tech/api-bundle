<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\DependencyInjection;

use Somnambulist\ApiBundle\Services\ExceptionConverter;
use Somnambulist\ApiBundle\Subscribers\ConvertExceptionToJSONResponseSubscriber;
use Somnambulist\ApiBundle\Subscribers\ConvertJSONToPOSTRequestSubscriber;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class SomnambulistApiExtension
 *
 * @package Somnambulist\ApiBundle\DependencyInjection
 * @subpackage Somnambulist\ApiBundle\DependencyInjection\SomnambulistApiExtension
 */
class SomnambulistApiExtension extends Extension
{

    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('somnambulist.api_bundle.request.per_page', (int)$config['request_handler']['per_page']);
        $container->setParameter('somnambulist.api_bundle.request.max_per_page', (int)$config['request_handler']['max_per_page']);
        $container->setParameter('somnambulist.api_bundle.request.limit', (int)$config['request_handler']['limit']);

        $reference = $container->getDefinition(ConvertExceptionToJSONResponseSubscriber::class);
        $reference->setArgument(1, $container->getParameter('kernel.debug'));

        $reference = $container->getDefinition(ExceptionConverter::class);
        $reference->setArgument(1, $config['exception_handler']['converters'] ?? []);

        if (false === $config['subscribers']['json_to_post']) {
            $container->removeDefinition(ConvertJSONToPOSTRequestSubscriber::class);
        }
        if (false === $config['subscribers']['exception_to_json']) {
            $container->removeDefinition(ConvertExceptionToJSONResponseSubscriber::class);
        }
    }
}
