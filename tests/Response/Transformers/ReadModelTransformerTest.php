<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Tests\Response\Transformers;

use PHPUnit\Framework\TestCase;
use Somnambulist\ApiBundle\Response\Transformers\ReadModelTransformer;
use Somnambulist\ApiBundle\Tests\Support\Stubs\Entities\MyModel;

/**
 * Class ReadModelTransformerTest
 *
 * @package    Somnambulist\ApiBundle\Tests\Response\Transformers
 * @subpackage Somnambulist\ApiBundle\Tests\Response\Transformers\ReadModelTransformerTest
 */
class ReadModelTransformerTest extends TestCase
{

    /**
     * @group response
     * @group response-transformers
     */
    public function testTransform()
    {
        $test = new MyModel(['id' => 123, 'name' => 'foo bar', 'baz' => 'bar']);

        $this->assertSame(['id' => 123, 'name' => 'foo bar'], (new ReadModelTransformer())->transform($test));
    }
}
