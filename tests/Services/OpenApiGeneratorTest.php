<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Services;

use Somnambulist\Bundles\ApiBundle\Services\OpenApiGenerator;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Behaviours\BootKernel;
use Somnambulist\Components\Collection\MutableCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use function json_encode;

/**
 * Class OpenApiGeneratorTest
 *
 * @package    Somnambulist\Bundles\ApiBundle\Tests\Services
 * @subpackage Somnambulist\Bundles\ApiBundle\Tests\Services\OpenApiGeneratorTest
 */
class OpenApiGeneratorTest extends KernelTestCase
{

    use BootKernel;

    public function testExtractApiData()
    {
        $gen = static::$container->get(OpenApiGenerator::class);

        $def = $gen->discover();

        $this->assertInstanceOf(MutableCollection::class, $def);
        $this->assertArrayHasKey('info', $def);
        $this->assertArrayHasKey('components', $def);
        $this->assertArrayHasKey('paths', $def);

        $this->assertEquals('1.0.0', $def->get('info')->get('version'));
        $this->assertEquals('1.0.0', $def->info->version);

        $this->assertCount(13, $def->paths);
        $this->assertCount(1, $def->components);
        $this->assertCount(4, $def->components->schemas);
        
        $this->assertArrayNotHasKey('/doc', $def->paths);
    }
}
