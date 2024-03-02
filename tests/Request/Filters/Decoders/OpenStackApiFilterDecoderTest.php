<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Request\Filters\Decoders;

use PHPUnit\Framework\TestCase;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Decoders\OpenStackApiFilterDecoder;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Expression\Expression;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Forms\SearchFormRequest;
use Somnambulist\Components\ApiClient\Client\Query\Encoders\OpenStackApiEncoder;
use Somnambulist\Components\ApiClient\Client\Query\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use function http_build_query;
use function parse_str;

/**
 * @group request
 * @group request-filters
 */
class OpenStackApiFilterDecoderTest extends TestCase
{
    public function testDecodingOpenStackFilterRequest()
    {
        $qb = new QueryBuilder();
        $qb
            ->include('foo', 'bar', 'this.that')
            ->where(
                $qb->expr()->and(
                    $qb->expr()->neq('this', 'that'),
                    $qb->expr()->eq('foo', 'bar'),
                ),
                $qb->expr()->and(
                    $qb->expr()->gte('baz', 'foo'),
                    $qb->expr()->lt('bar', 'bar'),
                )
            )
            ->page(1)
            ->perPage(30)
            ->orderBy('this')
            ->addOrderBy('that', 'desc')
        ;

        $queryString = http_build_query((new OpenStackApiEncoder())->encode($qb));

        $GET = [];
        parse_str($queryString, $GET);

        $formRequest = new SearchFormRequest(new Request($GET));
        $parser      = new OpenStackApiFilterDecoder();
        $result      = $parser->decode($formRequest);

        foreach ($result->parts() as $part) {
            $this->assertInstanceOf(Expression::class, $part);
            $this->assertMatchesRegularExpression('/(=|!=|>=|<)/', $part->operator);
        }
    }

    public function testDecodingMultipleSameFilters()
    {
        $qb = new QueryBuilder();
        $qb
            ->where(
                $qb->expr()->and(
                    $qb->expr()->neq('this', 'that'),
                    $qb->expr()->eq('this', 'bar'),
                ),
                $qb->expr()->and(
                    $qb->expr()->gte('baz', 'foo'),
                    $qb->expr()->lt('this', 'bar'),
                )
            )
        ;

        $args = (new OpenStackApiEncoder())->encode($qb);
        $queryString = http_build_query($args);

        $GET = [];
        parse_str($queryString, $GET);

        $formRequest = new SearchFormRequest(new Request($GET));
        $parser      = new OpenStackApiFilterDecoder();
        $result      = $parser->decode($formRequest);

        $this->assertCount(4, $result);

        foreach ($result->parts() as $part) {
            $this->assertInstanceOf(Expression::class, $part);
            $this->assertMatchesRegularExpression('/(=|!=|>=|<)/', $part->operator);
        }
    }
}
