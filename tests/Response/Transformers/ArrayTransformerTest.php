<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Tests\Response\Transformers;

use PHPUnit\Framework\TestCase;
use Somnambulist\ApiBundle\Response\Transformers\ArrayTransformer;

/**
 * Class ArrayTransformerTest
 *
 * @package    Somnambulist\ApiBundle\Tests\Response\Transformers
 * @subpackage Somnambulist\ApiBundle\Tests\Response\Transformers\ArrayTransformerTest
 */
class ArrayTransformerTest extends TestCase
{

    /**
     * @group response
     * @group response-transformers
     */
    public function testTransform()
    {
        $test = [
            'id' => 123,
            'name' => 'foo bar',
        ];

        $this->assertSame($test, (new ArrayTransformer())->transform($test));
    }
}
