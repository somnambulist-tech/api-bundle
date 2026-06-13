<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('somnambulist.api_bundle.request.request_id_header', 'X-Request-Id');
    $parameters->set('somnambulist.api_bundle.openapi.path', '%kernel.project_dir%/config/openapi');
    $parameters->set('somnambulist.api_bundle.openapi.cache_time', 43200);

    $services->defaults()
        ->private()
        ->autowire()
        ->autoconfigure();

    $services->set(\Somnambulist\Bundles\ApiBundle\Subscribers\ConvertExceptionToJSONResponseSubscriber::class, \Somnambulist\Bundles\ApiBundle\Subscribers\ConvertExceptionToJSONResponseSubscriber::class)
        ->args([service(\Somnambulist\Bundles\ApiBundle\Response\ExceptionConverter::class)])
        ->call('setLogger', [service('logger')->ignoreOnInvalid()])
        ->tag('kernel.subscriber');

    $services->set(\Somnambulist\Bundles\ApiBundle\Subscribers\ConvertJSONToPOSTRequestSubscriber::class, \Somnambulist\Bundles\ApiBundle\Subscribers\ConvertJSONToPOSTRequestSubscriber::class)
        ->tag('kernel.subscriber');

    $services->set(\Somnambulist\Bundles\ApiBundle\Subscribers\RequestIdInjectorSubscriber::class, \Somnambulist\Bundles\ApiBundle\Subscribers\RequestIdInjectorSubscriber::class)
        ->tag('kernel.subscriber')
        ->tag('kernel.reset', ['method' => 'reset'])
        ->tag('monolog.processor');

    $services->set(\Somnambulist\Bundles\ApiBundle\Response\ResponseConverter::class, \Somnambulist\Bundles\ApiBundle\Response\ResponseConverter::class)
        ->public()
        ->args([
            service(\Somnambulist\Bundles\ApiBundle\Response\Serializers\ArraySerializer::class),
            service(\League\Fractal\Manager::class),
            service('debug.stopwatch')->ignoreOnInvalid(),
        ]);

    $services->set(\Somnambulist\Bundles\ApiBundle\Response\ExceptionConverter::class, \Somnambulist\Bundles\ApiBundle\Response\ExceptionConverter::class)
        ->args([tagged_locator('somnambulist.api_bundle.exception_converter')]);

    $services->load('Somnambulist\\Bundles\\ApiBundle\\Response\\ExceptionConverters\\', '../../Response/ExceptionConverters/')
        ->tag('somnambulist.api_bundle.exception_converter');

    $services->set(\Somnambulist\Bundles\ApiBundle\Response\Serializers\ArraySerializer::class, \Somnambulist\Bundles\ApiBundle\Response\Serializers\ArraySerializer::class);

    $services->load('Somnambulist\\Bundles\\ApiBundle\\Response\\Transformers\\', '../../Response/Transformers/');

    $services->set(\Somnambulist\Bundles\ApiBundle\Services\OpenApiGenerator::class, \Somnambulist\Bundles\ApiBundle\Services\OpenApiGenerator::class)
        ->public();

    $services->set(\Somnambulist\Bundles\ApiBundle\Services\RuleConverters::class, \Somnambulist\Bundles\ApiBundle\Services\RuleConverters::class)
        ->args([tagged_iterator('somnambulist.api_bundle.openapi.rule_converter')]);

    $services->load('Somnambulist\\Bundles\\ApiBundle\\Services\\RuleConverters\\', '../../Services/RuleConverters/')
        ->tag('somnambulist.api_bundle.openapi.rule_converter');

    $services->set(\Somnambulist\Bundles\ApiBundle\Controllers\ApiDocController::class, \Somnambulist\Bundles\ApiBundle\Controllers\ApiDocController::class)
        ->args(['$cacheTime' => '%somnambulist.api_bundle.openapi.cache_time%'])
        ->tag('controller.service_arguments');
};
