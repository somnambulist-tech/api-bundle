<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Request\Filters\Decoders;

use PHPUnit\Framework\TestCase;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Decoders\OpenStackApiFilterDecoder;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Decoders\SimpleApiFilterDecoder;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Expression\Expression;
use Somnambulist\Bundles\ApiBundle\Request\FormRequest;
use Somnambulist\Components\ApiClient\Client\Query\Encoders\OpenStackApiEncoder;
use Somnambulist\Components\ApiClient\Client\Query\Encoders\SimpleEncoder;
use Somnambulist\Components\ApiClient\Client\Query\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

use function http_build_query;
use function parse_str;

/**
 * @group request
 * @group request-filters
 */
class SimpleApiFilterDecoderTest extends TestCase
{
    public function testDecodingOpenStackFilterRequest()
    {
        $qb = new QueryBuilder();
        $qb
            ->where($qb->expr()->eq('this', 'that'))
            ->andWhere($qb->expr()->eq('foo', 'bar'))
            ->andWhere($qb->expr()->eq('baz', 'foo'))
            ->andWhere($qb->expr()->eq('bar', 'bar'))
        ;

        $queryString = http_build_query((new SimpleEncoder())->encode($qb));

        $GET = [];
        parse_str($queryString, $GET);

        $formRequest = new FormRequest(new Request($GET));
        $parser      = new SimpleApiFilterDecoder();
        $result      = $parser->decode($formRequest);

        $this->assertCount(4, $result);

        foreach ($result->getParts() as $part) {
            $this->assertInstanceOf(Expression::class, $part);
            $this->assertEquals('=', $part->operator);
        }
    }

    public function testCanDecodeCustomFiltersKey()
    {
        $qb = new QueryBuilder();
        $qb
            ->where($qb->expr()->eq('this', 'that'))
            ->andWhere($qb->expr()->eq('foo', 'bar'))
            ->andWhere($qb->expr()->eq('baz', 'foo'))
            ->andWhere($qb->expr()->eq('bar', 'bar'))
        ;

        $queryString = http_build_query((new SimpleEncoder())->useNameForFiltersField('filters')->encode($qb));

        $GET = [];
        parse_str($queryString, $GET);

        $formRequest = new FormRequest(new Request($GET));
        $parser      = new SimpleApiFilterDecoder();
        $result      = $parser->useFiltersQueryName('filters')->decode($formRequest);

        $this->assertCount(4, $result);

        foreach ($result->getParts() as $part) {
            $this->assertInstanceOf(Expression::class, $part);
            $this->assertEquals('=', $part->operator);
        }
    }
}
