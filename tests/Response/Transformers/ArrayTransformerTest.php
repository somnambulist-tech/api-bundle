<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Response\Transformers;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Somnambulist\Bundles\ApiBundle\Response\Transformers\ArrayTransformer;

class ArrayTransformerTest extends TestCase
{

    #[Group("response")]
    #[Group("response-transformers")]
    public function testTransform()
    {
        $test = [
            'id' => 123,
            'name' => 'foo bar',
        ];

        $this->assertSame($test, (new ArrayTransformer())->transform($test));
    }
}
