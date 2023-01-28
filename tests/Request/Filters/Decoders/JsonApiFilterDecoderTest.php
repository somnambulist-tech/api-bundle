<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Request\Filters\Decoders;

use PHPUnit\Framework\TestCase;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Decoders\JsonApiFilterDecoder;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Expression\Expression;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Forms\SearchFormRequest;
use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;
use Somnambulist\Components\ApiClient\Client\Query\Encoders\JsonApiEncoder;
use Somnambulist\Components\ApiClient\Client\Query\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

use function http_build_query;
use function parse_str;

/**
 * @group request
 * @group request-filters
 */
class JsonApiFilterDecoderTest extends TestCase
{
    public function testDecodingJsonApiFilterRequest()
    {
        $qb = new QueryBuilder();
        $qb
            ->include('foo', 'bar', 'this.that')
            ->where(
                $qb->expr()->eq('this', 'that'),
                $qb->expr()->eq('foo', 'bar'),
                $qb->expr()->eq('bar', 3456),
            )
            ->page(1)
            ->perPage(30)
            ->orderBy('this')
            ->addOrderBy('that', 'desc')
        ;

        $queryString = http_build_query((new JsonApiEncoder())->encode($qb));

        $GET = [];
        parse_str($queryString, $GET);

        $formRequest = new SearchFormRequest(new Request($GET));
        FormRequest::appendValidationData($formRequest, $GET);
        $parser      = new JsonApiFilterDecoder();
        $result      = $parser->decode($formRequest);

        $this->assertTrue($result->isAnd());
        $this->assertCount(3, $result);

        foreach ($result->parts() as $part) {
            $this->assertInstanceOf(Expression::class, $part);
            $this->assertEquals('=', $part->operator);
        }
    }

    public function testDecodingArrayValues()
    {
        $qb = new QueryBuilder();
        $qb->where($qb->expr()->in('this', ['that', 'bar']));

        $queryString = http_build_query((new JsonApiEncoder())->encode($qb));

        $GET = [];
        parse_str($queryString, $GET);

        $formRequest = new SearchFormRequest(new Request($GET));
        FormRequest::appendValidationData($formRequest, $GET);
        $parser      = new JsonApiFilterDecoder();
        $result      = $parser->decode($formRequest);

        $this->assertEquals('IN', $result[0]->operator);
    }
}
