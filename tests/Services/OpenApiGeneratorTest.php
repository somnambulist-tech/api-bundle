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

        $this->assertCount(14, $def->paths);
        $this->assertCount(1, $def->components);
        $this->assertCount(4, $def->components->schemas);
        
        $this->assertArrayNotHasKey('/doc', $def->paths);
    }

    public function testBuildsContentOnMethodsOnRoutes()
    {
        $gen = static::$container->get(OpenApiGenerator::class);

        $def = $gen->discover();

        $route = $def->paths->get('/test/resolvers/external_id');

        $this->assertEquals('/test/resolvers/external_id', $route->get->summary);

        $route = $def->paths->get('/test/create_user');

        $this->assertEquals('Create a new user', $route->summary);
        $this->assertEquals('postCreateUser', $route->post->operationId);

        $route = $def->paths->get('/test/{userId}');

        $this->assertEquals('Update the User', $route->summary);
        $this->assertEquals('putUpdateUserDetails', $route->put->operationId);
        $this->assertEquals('Update specific User properties', $route->patch->summary);
    }
}
