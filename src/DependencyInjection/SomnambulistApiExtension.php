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

class SomnambulistApiExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $container->setParameter('somnambulist.api_bundle.openapi.config_path', (string)$config['openapi']['path']);
        $container->setParameter('somnambulist.api_bundle.openapi.cache_time', (string)$config['openapi']['cache_time']);

        $reference = $container->getDefinition(OpenApiGenerator::class);
        $reference->setArgument('$config', $config['openapi']);

        $reference = $container->getDefinition(RequestIdInjectorSubscriber::class);
        if ($container->hasParameter('somnambulist.api_bundle.request_id_header')) {
            $reference->setArgument(0, $container->getParameter('somnambulist.api_bundle.request_id_header'));
        }

        $reference = $container->getDefinition(ConvertExceptionToJSONResponseSubscriber::class);
        $reference->setArgument('$debug', $container->getParameter('kernel.debug'));
        $reference->setArgument('$apiRoot', $config['exception_handler']['api_root'] ?? '/api');
        $reference->setArgument('$docRoot', $config['exception_handler']['doc_root'] ?? '/api/doc');

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
