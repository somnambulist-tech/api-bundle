<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Request\Filters;

use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;
use Somnambulist\Bundles\ApiBundle\Request\Filters\ApplyApiExpressionsToDBALQueryBuilder;
use Somnambulist\Bundles\ApiBundle\Request\Filters\Decoders\CompoundNestedArrayFilterDecoder;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Forms\SearchFormRequest;
use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;
use Somnambulist\Components\ApiClient\Client\Query\Encoders\CompoundNestedArrayEncoder;
use Somnambulist\Components\ApiClient\Client\Query\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

use function http_build_query;
use function parse_str;

/**
 * @group request
 * @group request-filters
 * @group request-filters-dbal-converter
 */
class ApplyApiExpressionsToDBALQueryBuilderTest extends TestCase
{
    public function testConvertApiExpressionToDbal()
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

        $formRequest = new SearchFormRequest(new Request($GET));
        FormRequest::appendValidationData($formRequest, $GET);
        $parser      = new CompoundNestedArrayFilterDecoder();
        $result      = $parser->decode($formRequest);

        $qb = new \Doctrine\DBAL\Query\QueryBuilder(DriverManager::getConnection(['url' => 'sqlite:///:in-memory:']));

        (new ApplyApiExpressionsToDBALQueryBuilder([
            'this' => 'table.name',
            'foo' => 'table2.created_at',
            'that' => 'table.nickname',
            'this2' => 'table.description',
            'baz' => 'table.created_at',
        ]))->apply($result, $qb);

        $this->assertEquals(
            'SELECT  WHERE (((table2.created_at <> :table2_created_at_0) AND (table.name = :table_name_1)) AND ((table.nickname LIKE :table_nickname_2) OR (table.description NOT LIKE :table_description_3))) OR (table.created_at = :table_created_at_4)',
            $qb->getSQL()
        );
    }

    public function testNullClauses()
    {
        $qb = new QueryBuilder();
        $qb->where($qb->expr()->isNull('this'));

        $queryString = http_build_query((new CompoundNestedArrayEncoder())->encode($qb));

        $GET = [];
        parse_str($queryString, $GET);

        $formRequest = new SearchFormRequest(new Request($GET));
        FormRequest::appendValidationData($formRequest, $GET);
        $parser      = new CompoundNestedArrayFilterDecoder();
        $result      = $parser->decode($formRequest);

        $qb = new \Doctrine\DBAL\Query\QueryBuilder(DriverManager::getConnection(['url' => 'sqlite:///:in-memory:']));

        (new ApplyApiExpressionsToDBALQueryBuilder([
            'this' => 'table.name',
        ]))->apply($result, $qb);

        $this->assertEquals('SELECT  WHERE table.name IS NULL', $qb->getSQL());
    }

    public function testInClauses()
    {
        $qb = new QueryBuilder();
        $qb->where($qb->expr()->in('this', ['that', 'bar']));

        $queryString = http_build_query((new CompoundNestedArrayEncoder())->encode($qb));

        $GET = [];
        parse_str($queryString, $GET);

        $formRequest = new SearchFormRequest(new Request($GET));
        FormRequest::appendValidationData($formRequest, $GET);
        $parser      = new CompoundNestedArrayFilterDecoder();
        $result      = $parser->decode($formRequest);

        $qb = new \Doctrine\DBAL\Query\QueryBuilder(DriverManager::getConnection(['url' => 'sqlite:///:in-memory:']));

        (new ApplyApiExpressionsToDBALQueryBuilder([
            'this' => 'table.name',
        ]))->apply($result, $qb);

        $this->assertEquals('SELECT  WHERE table.name IN (:table_name_0, :table_name_1)', $qb->getSQL());
    }

    public function testOperatorMapping()
    {
        $qb = new QueryBuilder();
        $qb->where($qb->expr()->eq('this', 'that'));

        $queryString = http_build_query((new CompoundNestedArrayEncoder())->encode($qb));

        $GET = [];
        parse_str($queryString, $GET);

        $formRequest = new SearchFormRequest(new Request($GET));
        FormRequest::appendValidationData($formRequest, $GET);
        $parser      = new CompoundNestedArrayFilterDecoder();
        $result      = $parser->decode($formRequest);

        $qb = new \Doctrine\DBAL\Query\QueryBuilder(DriverManager::getConnection(['url' => 'sqlite:///:in-memory:']));

        (new ApplyApiExpressionsToDBALQueryBuilder(
            [
                'this' => 'table.name',
            ],
            [
                'this' => 'LIKE',
            ]
        ))->apply($result, $qb);

        $this->assertEquals('SELECT  WHERE table.name LIKE :table_name_0', $qb->getSQL());
    }

    public function testOperatorMappingOfILike()
    {
        $qb = new QueryBuilder();
        $qb->where($qb->expr()->comparison('this', 'ilike', 'that'));

        $queryString = http_build_query((new CompoundNestedArrayEncoder())->encode($qb));

        $GET = [];
        parse_str($queryString, $GET);

        $formRequest = new SearchFormRequest(new Request($GET));
        FormRequest::appendValidationData($formRequest, $GET);
        $parser      = new CompoundNestedArrayFilterDecoder();
        $result      = $parser->decode($formRequest);

        $qb = new \Doctrine\DBAL\Query\QueryBuilder(DriverManager::getConnection(['url' => 'sqlite:///:in-memory:']));

        (new ApplyApiExpressionsToDBALQueryBuilder(
            [
                'this' => 'table.name',
            ],
            [
                'this' => 'ILIKE',
            ]
        ))->apply($result, $qb);

        $this->assertEquals('SELECT  WHERE table.name ILIKE :table_name_0', $qb->getSQL());
    }
}
