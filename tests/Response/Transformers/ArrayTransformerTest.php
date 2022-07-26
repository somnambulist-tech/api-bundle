<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Response\Transformers;

use PHPUnit\Framework\TestCase;
use Somnambulist\Bundles\ApiBundle\Response\Transformers\ArrayTransformer;

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
