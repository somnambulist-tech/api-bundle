<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\Builders\Behaviours;

use ReflectionClass;
use Somnambulist\Bundles\ApiBundle\Services\Attributes\OpenApiExamples;
use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;
use Symfony\Component\Routing\Route;
use function explode;
use function is_a;

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
                $ref   = new ReflectionClass((string)$parameter->getType());
                $class = (string)$parameter->getType();
                /*
                 * This is "bad", but there is (at the time of writing) apparently no way to dynamically create an
                 * anonymous class from the contents of a variable. This is needed for PHP 8.2+ as dynamic properties
                 * are deprecated and the base FormRequest __really__ should not have a public random array property
                 * just for this use case.
                 */

                $form  = "return new class extends $class {
                    public function __construct() {}
                    public array \$__meta__ = ['examples' => null, 'auth' => []];
                };";
                $form  = eval($form);

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
