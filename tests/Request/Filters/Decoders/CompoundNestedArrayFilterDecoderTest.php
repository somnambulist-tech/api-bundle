<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Request\Filters\Decoders;

use PHPUnit\Framework\TestCase;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Decoders\CompoundNestedArrayFilterDecoder;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Expression\CompositeExpression;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Expression\Expression;
use Somnambulist\Bundles\ApiBundle\Request\FormRequest;
use Somnambulist\Components\ApiClient\Client\Query\Encoders\CompoundNestedArrayEncoder;
use Somnambulist\Components\ApiClient\Client\Query\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

use function http_build_query;
use function parse_str;

/**
 * @group request
 * @group request-filters
 */
class CompoundNestedArrayFilterDecoderTest extends TestCase
{
    public function testDecodingCompoundNestedArrayOfFilters()
    {
        $qb = new QueryBuilder();
        $qb
            ->where(
                $qb->expr()->and(
                    $qb->expr()->eq('this', 'that'),
                    $qb->expr()->neq('foo', 'bar'),
                ),
                $qb->expr()->or(
                    $qb->expr()->like('that', 'foo'),
                    $qb->expr()->notLike('this2', 'bar'),
                )
            )
            ->orWhere($qb->expr()->eq('baz', true))
        ;

        $queryString = http_build_query((new CompoundNestedArrayEncoder())->encode($qb));

        $GET = [];
        parse_str($queryString, $GET);

        $formRequest = new FormRequest(new Request($GET));
        $parser      = new CompoundNestedArrayFilterDecoder();
        $result      = $parser->decode($formRequest);

        $this->assertEquals($qb->getWhere()->count(), $result->count());
        $this->assertEquals($qb->getWhere()->getType(), $result->getType());

        $this->assertInstanceOf(CompositeExpression::class, $result[0]);
        $this->assertInstanceOf(Expression::class, $result[1]);
        $this->assertTrue($result[0]->isAnd());

        $this->assertInstanceOf(CompositeExpression::class, $result[0][0]);
        $this->assertInstanceOf(Expression::class, $result[0][0][0]);
        $this->assertTrue($result[0][0]->isAnd());
        $this->assertInstanceOf(CompositeExpression::class, $result[0][1]);
        $this->assertTrue($result[0][1]->isOr());
    }
}
