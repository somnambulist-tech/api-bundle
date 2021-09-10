<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\Builders;

use LogicException;
use Symfony\Component\Routing\Route;
use function explode;
use function is_null;
use function sprintf;
use function str_replace;

/**
 * Class ResponseBuilder
 *
 * @package    Somnambulist\Bundles\ApiBundle\Services\Builders
 * @subpackage Somnambulist\Bundles\ApiBundle\Services\Builders\ResponseBuilder
 */
class ResponseBuilder
{
    public function build(Route $route): array
    {
        if (is_null($route->getDefault('responses'))) {
            throw new LogicException(sprintf('Route "%s" has no responses defined. A response is required.', $route->getPath()));
        }

        $responses = [];

        foreach ($route->getDefault('responses') as $code => $template) {
            if (!$template) {
                $responses[$code]['description'] = 'No response body is returned from this request';
                continue;
            }
            [$refType, $schema] = explode('/', $template, 2);
            $schema = str_replace('/', '.', $schema);

            $responses[$code] = [
                'description' => sprintf('A %s to be returned', $schema),
                'content'     => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => sprintf('#/components/%s/%s', $refType, $schema),
                        ],
                    ],
                ],
            ];
        }

        return $responses;
    }
}
