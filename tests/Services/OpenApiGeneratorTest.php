<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Services;

use Somnambulist\Bundles\ApiBundle\Services\OpenApiGenerator;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Behaviours\BootKernel;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Entities\MyEnum;
use Somnambulist\Components\Collection\MutableCollection;
use Somnambulist\Components\Domain\Utils\EntityAccessor;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

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
        $this->assertArrayHasKey('tags', $def);

        $this->assertEquals('1.0.0', $def->get('info')->get('version'));
        $this->assertEquals('1.0.0', $def->info->version);

        $this->assertCount(14, $def->paths);
        $this->assertCount(1, $def->components);
        $this->assertCount(4, $def->components->schemas);
        $this->assertCount(1, $def->tags);

        $this->assertEquals('user', $def->tags->first()->name);
        $this->assertEquals('Endpoints related to the User.', $def->tags->first()->description);

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

    public function testCanHandleRuleWithEmptySpecAndStringIsDefaultType()
    {
        $result = $this->callBuildRequestBodySchemaFromRuleSpecs(['a_string' => '']);
        $props  = $this->props($result);

        $this->assertEquals(['a_string' => ['type' => 'string']], $props);
    }

    /**
     * @dataProvider getFormatTestData
     */
    public function testFormat(string $ruleSpec, string $format)
    {
        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs(['my_prop' => $ruleSpec]);
        $props  = $this->props($schema);

        $this->assertEquals($format, $props['my_prop']['format']);

        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs(['my_prop' => "required|$ruleSpec|nullable"]);
        $props  = $this->props($schema);

        $this->assertEquals($format, $props['my_prop']['format']);
    }

    public function getFormatTestData(): array
    {
        return [
            ['integer',             'int64'],
            ['float',               'double'],
            ['ip',                  'ip'],
            ['ipv4',                'ipv4'],
            ['ipv6',                'ipv6'],
            ['uploaded_file',       'binary'],
            ['date',                'date'],
            ['date:Y-m-d H:i:s',    'date-time'],
            ['date:H:i:s',          'date-time'],
            ['date:Y-m-d \H:\i:\s', 'date'],
            ['datetime',            'date-time'],
        ];
    }

    public function testRequestBodyContentTypeWithoutUploadedFile()
    {
        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs(['my_prop' => 'integer']);

        $this->assertArrayHasKey('application/x-www-form-urlencoded', $schema['content']);
    }

    public function testRequestBodyContentTypeWithUploadedFile()
    {
        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs(['my_prop' => 'uploaded_file:0,1M']);

        $this->assertArrayHasKey('multipart/form-data', $schema['content']);
    }

    public function testRequestBodyContentTypeWithDeeplyNestedUploadedFile()
    {
        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs([
            'my_parent.*.my_child.*.my_prop' => 'nullable|uploaded_file:0,1M',
        ]);

        $this->assertArrayHasKey('multipart/form-data', $schema['content']);
    }

    public function testContentBodyRequiredWhenSchemaHasRequiredRule()
    {
        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs(['my_prop' => 'required']);

        $this->assertTrue($schema['required']);
    }

    public function testContentBodyRequiredWhenSchemaHasDeeplyNestedRequiredRule()
    {
        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs([
            'my_parent.*.my_child.*.my_prop' => 'integer|required|min:1',
        ]);

        $this->assertTrue($schema['required']);
    }

    public function testContentBodyRequiredWhenSchemaHasPresentRule()
    {
        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs(['my_prop' => 'present']);

        $this->assertTrue($schema['required']);
    }

    public function testContentBodyRequiredWhenSchemaHasDeeplyNestedPresentRule()
    {
        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs([
            'my_parent.*.my_child.*.my_prop' => 'integer|present|min:1',
        ]);

        $this->assertTrue($schema['required']);
    }

    public function testContentBodyNotRequiredWhenSchemaHasNoRequirements()
    {
        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs(['my_prop' => 'integer']);

        $this->assertFalse($schema['required']);
    }

    public function testRequiredPropertiesAreListed()
    {
        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs([
            'my_required' => 'required',
            'my_present'  => 'present',
        ]);

        $contentSchema = reset($schema['content'])['schema'];

        $this->assertArrayHasKey('required', $contentSchema);
        $this->assertIsArray($contentSchema['required']);
        $this->assertContains('my_required', $contentSchema['required']);
        $this->assertContains('my_present', $contentSchema['required']);
    }

    public function testDeeplyNestedRequiredPropertiesAreListed()
    {
        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs([
            'my_parent.*.my_child.*.my_required' => 'required',
            'my_parent.*.my_child.*.my_present'  => 'present',
        ]);
        $props = $this->props($schema);

        $propSchema = $props['my_parent']['items']['properties']['my_child']['items'];

        $this->assertArrayHasKey('required', $propSchema);
        $this->assertIsArray($propSchema['required']);
        $this->assertContains('my_required', $propSchema['required']);
        $this->assertContains('my_present', $propSchema['required']);
    }

    public function testEnumDefinedWithEnumerationClass()
    {
        $enumValues = array_values(MyEnum::values());

        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs([
            'my_enum' => ['enum' => MyEnum::class],
        ]);
        $prop = $this->props($schema)['my_enum'];

        $this->assertArrayHasKey('enum', $prop);
        $this->assertSame($enumValues, $prop['enum']);

        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs([
            'my_enum' => 'enum:' . MyEnum::class,
        ]);
        $prop = $this->props($schema)['my_enum'];

        $this->assertArrayHasKey('enum', $prop);
        $this->assertSame($enumValues, $prop['enum']);
    }

    public function testEnumDefinedWithArrayOfStrings()
    {
        $enumValues = ['foo', 'bar', 'baz'];

        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs([
            'my_enum' => ['enum' => $enumValues],
        ]);
        $prop = $this->props($schema)['my_enum'];

        $this->assertArrayHasKey('enum', $prop);
        $this->assertSame($enumValues, $prop['enum']);

        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs([
            'my_enum' => 'enum:' . implode(',', $enumValues),
        ]);
        $prop = $this->props($schema)['my_enum'];

        $this->assertArrayHasKey('enum', $prop);
        $this->assertSame($enumValues, $prop['enum']);
    }

    public function testEnumIgnoresBlankAndEmptyValues()
    {
        $enumValues = ['foo', '    ', 'bar', '', 'baz'];
        $expected   = ['foo', 'bar', 'baz'];

        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs([
            'my_enum' => ['enum' => $enumValues],
        ]);
        $prop = $this->props($schema)['my_enum'];

        $this->assertArrayHasKey('enum', $prop);
        $this->assertSame($expected, $prop['enum']);

        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs([
            'my_enum' => 'enum:' . implode(',', $enumValues),
        ]);
        $prop = $this->props($schema)['my_enum'];

        $this->assertArrayHasKey('enum', $prop);
        $this->assertSame($expected, $prop['enum']);
    }

    public function testInvalidEnumStringWithNoValuesReturnsEmptyArray()
    {
        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs([
            'my_enum' => 'enum:',
        ]);
        $prop = $this->props($schema)['my_enum'];

        $this->assertArrayHasKey('enum', $prop);
        $this->assertIsArray($prop['enum']);
        $this->assertEmpty($prop['enum']);
    }

    public function testEnumDefinedWithAZeroValueDoesNotOmitTheZero()
    {
        // Because `array_filter()` will omit "0".

        $enumValues = [0, 1, 2, 3];
        $expected   = array_map('strval', $enumValues);

        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs([
            'my_enum' => 'enum:' . implode(',', $enumValues),
        ]);
        $prop = $this->props($schema)['my_enum'];

        $this->assertArrayHasKey('enum', $prop);
        $this->assertSame($expected, $prop['enum']);

        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs([
            'my_enum' => ['enum' => $enumValues],
        ]);
        $prop = $this->props($schema)['my_enum'];

        $this->assertArrayHasKey('enum', $prop);
        $this->assertSame($expected, $prop['enum']);
    }

    private function callBuildRequestBodySchemaFromRuleSpecs(array $rules, array $examples = [])
    {
        return EntityAccessor::call(
            static::getContainer()->get(OpenApiGenerator::class),
            'buildRequestBodySchemaFromRuleSpecs',
            null,
            $rules,
            $examples,
        );
    }

    private function props(array $schema)
    {
        return reset($schema['content'])['schema']['properties'];
    }
}
