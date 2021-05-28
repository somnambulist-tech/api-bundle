<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\DependencyInjection;

use Somnambulist\Bundles\ApiBundle\Response\ExceptionConverter;
use Somnambulist\Bundles\ApiBundle\Services\OpenApiGenerator;
use Somnambulist\Bundles\ApiBundle\Subscribers\ConvertExceptionToJSONResponseSubscriber;
use Somnambulist\Bundles\ApiBundle\Subscribers\ConvertJSONToPOSTRequestSubscriber;
use Somnambulist\Bundles\ApiBundle\Subscribers\RequestIdInjectorSubscriber;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class SomnambulistApiExtension
 *
 * @package    Somnambulist\Bundles\ApiBundle\DependencyInjection
 * @subpackage Somnambulist\Bundles\ApiBundle\DependencyInjection\SomnambulistApiExtension
 */
class SomnambulistApiExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $container->setParameter('somnambulist.api_bundle.request.per_page', (int)$config['request_handler']['per_page']);
        $container->setParameter('somnambulist.api_bundle.request.max_per_page', (int)$config['request_handler']['max_per_page']);
        $container->setParameter('somnambulist.api_bundle.request.limit', (int)$config['request_handler']['limit']);
        $container->setParameter('somnambulist.api_bundle.request.request_id_header', (string)$config['request_handler']['request_id_header']);
        $container->setParameter('somnambulist.api_bundle.openapi.config_path', (string)$config['openapi']['path']);
        $container->setParameter('somnambulist.api_bundle.openapi.title', (string)$config['openapi']['title']);
        $container->setParameter('somnambulist.api_bundle.openapi.version', (string)$config['openapi']['version']);
        $container->setParameter('somnambulist.api_bundle.openapi.description', (string)$config['openapi']['description']);
        $container->setParameter('somnambulist.api_bundle.openapi.cache_time', (string)$config['openapi']['cache_time']);
        $container->setParameter('somnambulist.api_bundle.openapi.tags', (array)$config['openapi']['tags'] ?? []);

        $reference = $container->getDefinition(OpenApiGenerator::class);
        $reference->setArgument('$config', $config['openapi']);

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
        if (false === $config['subscribers']['request_id']) {
            $container->removeDefinition(RequestIdInjectorSubscriber::class);
        }
    }
}
