<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Response\Transformers;

use PHPUnit\Framework\TestCase;
use Somnambulist\Bundles\ApiBundle\Response\Transformers\StdClassTransformer;
use stdClass;

class StdClassTransformerTest extends TestCase
{

    /**
     * @group response
     * @group response-transformers
     */
    public function testTransform()
    {
        $test = new stdClass();
        $test->id = 123;
        $test->name = 'foo bar';

        $this->assertSame(['id' => 123, 'name' => 'foo bar'], (new StdClassTransformer())->transform($test));
    }
}
