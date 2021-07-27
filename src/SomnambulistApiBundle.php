<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle;

use Somnambulist\Bundles\ApiBundle\Services\Contracts\RuleConverterInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class SomnambulistApiBundle
 *
 * @package    Somnambulist\Bundles\ApiBundle
 * @subpackage Somnambulist\Bundles\ApiBundle\SomnambulistApiBundle
 */
class SomnambulistApiBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container
            ->registerForAutoconfiguration(RuleConverterInterface::class)
            ->addTag('somnambulist.api_bundle.openapi.rule_converter')
        ;
    }
}
