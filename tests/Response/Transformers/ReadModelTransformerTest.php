<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Response\Transformers;

use PHPUnit\Framework\TestCase;
use Somnambulist\Bundles\ApiBundle\Response\Transformers\ReadModelTransformer;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Entities\MyModel;
use Somnambulist\Components\ReadModels\Manager;

class ReadModelTransformerTest extends TestCase
{

    /**
     * @group response
     * @group response-transformers
     */
    public function testTransform()
    {
        new Manager();

        $test = new MyModel(['id' => 123, 'name' => 'foo bar', 'baz' => 'bar']);

        $this->assertSame(['id' => 123, 'name' => 'foo bar'], (new ReadModelTransformer())->transform($test));
    }
}
