<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\Builders\Behaviours;

use Doctrine\Instantiator\Instantiator;
use ReflectionClass;
use Somnambulist\Bundles\ApiBundle\Services\Attributes\OpenApiExamples;
use Somnambulist\Bundles\ApiBundle\Services\Contracts\HasOpenApiExamples;
use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;
use Symfony\Component\Routing\Route;
use function explode;
use function is_a;

/**
 * Trait GetFormRequestFromRoute
 *
 * @package    Somnambulist\Bundles\ApiBundle\Services\Builders\Behaviours
 * @subpackage Somnambulist\Bundles\ApiBundle\Services\Builders\Behaviours\GetFormRequestFromRoute
 */
trait GetFormRequestFromRoute
{
    private function getClassAndMethodFromController(Route $route): array
    {
        if (str_contains($route->getDefault('_controller'), '::')) {
            return explode('::', $route->getDefault('_controller'));
        }

        return [$route->getDefault('_controller'), '__invoke'];
    }

    private function getFormRequestFromMethodArguments(Route $route): ?FormRequest
    {
        [$controller, $method] = $this->getClassAndMethodFromController($route);

        $class = new ReflectionClass($controller);

        foreach ($class->getMethod($method)->getParameters() as $parameter) {
            if (is_a((string)$parameter->getType(), FormRequest::class, true)) {
                $ref            = new ReflectionClass((string)$parameter->getType());
                $form           = (new Instantiator())->instantiate((string)$parameter->getType());
                $form->__meta__ = ['examples' => null, 'auth' => []];

                foreach ($ref->getMethods() as $method) {
                    if (!empty($method->getAttributes(OpenApiExamples::class))) {
                        $form->__meta__['examples'] = $method;
                        break;
                    }
                }

                return $form;
            }
        }

        return null;
    }
}
