<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Tests\Response\Transformers;

use PHPUnit\Framework\TestCase;
use Somnambulist\ApiBundle\Response\Transformers\StdClassTransformer;
use stdClass;

/**
 * Class StdClassTransformerTest
 *
 * @package    Somnambulist\ApiBundle\Tests\Response\Transformers
 * @subpackage Somnambulist\ApiBundle\Tests\Response\Transformers\StdClassTransformerTest
 */
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
