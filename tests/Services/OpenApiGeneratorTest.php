<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Services;

use LogicException;
use Somnambulist\Bundles\ApiBundle\Services\OpenApiGenerator;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Behaviours\BootKernel;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Entities\MyMultitonEnum;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Entities\MyValueMultitonEnum;
use Somnambulist\Components\Collection\MutableCollection;
use Somnambulist\Components\Utils\EntityAccessor;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\RouterInterface;

class OpenApiGeneratorTest extends KernelTestCase
{
    use BootKernel;

    public function testExtractApiData()
    {
        $gen = static::getContainer()->get(OpenApiGenerator::class);

        $def = $gen->discover();

        $this->assertInstanceOf(MutableCollection::class, $def);
        $this->assertArrayHasKey('info', $def);
        $this->assertArrayHasKey('components', $def);
        $this->assertArrayHasKey('paths', $def);
        $this->assertArrayHasKey('tags', $def);

        $this->assertEquals('1.0.0', $def->get('info')->get('version'));
        $this->assertEquals('1.0.0', $def->info->version);

        $this->assertCount(14, $def->paths);
        // components should contain only non-empty collections
        $this->assertCount(2, $def->components);
        $this->assertCount(4, $def->components->schemas);
        $this->assertCount(1, $def->components->securitySchemes);
        $this->assertCount(5, $def->tags);

        $this->assertEquals('user', $def->tags->first()->name);
        $this->assertEquals('Endpoints related to the User.', $def->tags->first()->description);

        $this->assertArrayNotHasKey('/doc', $def->paths);

        $this->assertFalse($def->security->has('api_key'));
    }

    public function testInvalidSecuritySchemeRaisesException()
    {
        $router = static::getContainer()->get(RouterInterface::class);
        $gen    = static::getContainer()->get(OpenApiGenerator::class);

        $router->getRouteCollection()->get('test.not_found')->setDefault('security', ['bob' => []]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Route "/test/not_found" has referenced security scheme "bob" but this is not configured');

        $gen->discover();
    }

    public function testBuildsContentOnMethodsOnRoutes()
    {
        $gen = static::getContainer()->get(OpenApiGenerator::class);

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

        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs(['my_prop' => "required~~$ruleSpec~~nullable"]);
        $props  = $this->props($schema);

        $this->assertEquals($format, $props['my_prop']['format']);
    }

    public function getFormatTestData(): array
    {
        return [
            ['integer', 'int64'],
            ['float', 'double'],
            ['ip', 'ip'],
            ['ipv4', 'ipv4'],
            ['ipv6', 'ipv6'],
            ['uploaded_file', 'binary'],
            ['date', 'date'],
            ['date:Y-m-d H:i:s', 'date-time'],
            ['date:H:i:s', 'date-time'],
            ['date:Y-m-d \H:\i:\s', 'date'],
            ['datetime', 'date-time'],
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
            'my_parent.*.my_child.*.my_prop' => 'nullable~~uploaded_file:0,1M',
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
            'my_parent.*.my_child.*.my_prop' => 'integer~~required~~min:1',
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
            'my_parent.*.my_child.*.my_prop' => 'integer~~present~~min:1',
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
        $props  = $this->props($schema);

        $propSchema = $props['my_parent']['items']['properties']['my_child']['items'];

        $this->assertArrayHasKey('required', $propSchema);
        $this->assertIsArray($propSchema['required']);
        $this->assertContains('my_required', $propSchema['required']);
        $this->assertContains('my_present', $propSchema['required']);
    }

    public function testEnumDefinedWithEloquentMultitonEnumClass()
    {
        $enumValues = array_values(MyMultitonEnum::keys());

        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs([
            'my_enum' => ['enum' => MyMultitonEnum::class],
        ]);
        $prop   = $this->props($schema)['my_enum'];

        $this->assertArrayHasKey('enum', $prop);
        $this->assertSame($enumValues, $prop['enum']);

        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs([
            'my_enum' => 'enum:' . MyMultitonEnum::class,
        ]);
        $prop   = $this->props($schema)['my_enum'];

        $this->assertArrayHasKey('enum', $prop);
        $this->assertSame($enumValues, $prop['enum']);
    }

    public function testEnumDefinedWithEloquentValueMultitonEnumClass()
    {
        $enumValues = array_values(MyValueMultitonEnum::values());

        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs([
            'my_enum' => ['enum' => MyValueMultitonEnum::class],
        ]);
        $prop   = $this->props($schema)['my_enum'];

        $this->assertArrayHasKey('enum', $prop);
        $this->assertSame($enumValues, $prop['enum']);

        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs([
            'my_enum' => 'enum:' . MyValueMultitonEnum::class,
        ]);
        $prop   = $this->props($schema)['my_enum'];

        $this->assertArrayHasKey('enum', $prop);
        $this->assertSame($enumValues, $prop['enum']);
    }

    public function testEnumDefinedWithArrayOfStrings()
    {
        $enumValues = ['foo', 'bar', 'baz'];

        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs([
            'my_enum' => ['enum' => $enumValues],
        ]);
        $prop   = $this->props($schema)['my_enum'];

        $this->assertArrayHasKey('enum', $prop);
        $this->assertSame($enumValues, $prop['enum']);

        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs([
            'my_enum' => 'enum:' . implode(',', $enumValues),
        ]);
        $prop   = $this->props($schema)['my_enum'];

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
        $prop   = $this->props($schema)['my_enum'];

        $this->assertArrayHasKey('enum', $prop);
        $this->assertSame($expected, $prop['enum']);

        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs([
            'my_enum' => 'enum:' . implode(',', $enumValues),
        ]);
        $prop   = $this->props($schema)['my_enum'];

        $this->assertArrayHasKey('enum', $prop);
        $this->assertSame($expected, $prop['enum']);
    }

    public function testInvalidEnumStringWithNoValuesReturnsEmptyArray()
    {
        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs([
            'my_enum' => 'enum:',
        ]);
        $prop   = $this->props($schema)['my_enum'];

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
        $prop   = $this->props($schema)['my_enum'];

        $this->assertArrayHasKey('enum', $prop);
        $this->assertSame($expected, $prop['enum']);

        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs([
            'my_enum' => ['enum' => $enumValues],
        ]);
        $prop   = $this->props($schema)['my_enum'];

        $this->assertArrayHasKey('enum', $prop);
        $this->assertSame($expected, $prop['enum']);
    }

    public function testUnsupportedRulesAreIgnored()
    {
        // should not throw an exception from RuleConverters
        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs([
            'my_enum' => 'country:GBR,USA,CAN',
        ]);
        $prop   = $this->props($schema)['my_enum'];

        $this->assertEquals(['type' => 'string'], $prop);
    }

    public function testRegexMatchesOnOrderInclude()
    {
        // should not throw an exception from RuleConverters
        $schema = $this->callBuildRequestBodySchemaFromRuleSpecs([
            'include' => ['nullable', 'regex:/[(roles|permissions).,]/'],
            'order'   => ['default' => 'name', 'regex' => '/[(name|id|created_at),-]/',],
        ]);
        $prop   = $this->props($schema)['include'];

        $this->assertEquals(
            ['type' => 'string', 'nullable' => true, 'pattern' => '/[(roles|permissions).,]/', 'title' => 'The string must match the regular expression'],
            $prop
        );

        $prop   = $this->props($schema)['order'];
        $this->assertEquals(
            ['type' => 'string', 'pattern' => '/[(name|id|created_at),-]/', 'default' => 'name', 'title' => 'The string must match the regular expression'],
            $prop
        );
    }

    /**
     * @todo Refactor to test the builders
     */
    private function callBuildRequestBodySchemaFromRuleSpecs(array $rules, array $examples = [])
    {
        $builder = EntityAccessor::get(
            static::getContainer()->get(OpenApiGenerator::class),
            'body',
        );

        return EntityAccessor::call($builder, 'buildRequestBodySchemaFromRuleSpecs', null, $rules, $examples);
    }

    private function props(array $schema)
    {
        return reset($schema['content'])['schema']['properties'];
    }
}
