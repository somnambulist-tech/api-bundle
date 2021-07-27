<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\Attributes;

use Attribute;

/**
 * Attribute OpenApiExamples
 *
 * This attribute can be added to FormRequests to add support for examples in the documentation
 * output. Define a public method e.g. `examples()` and add the attribute and the use statement
 * for the OpenApiExamples class. Only one method can be tagged with this attribute.
 *
 * There are two ways to use this:
 *
 *  * for query arguments
 *  * for body requests
 *
 * For query arguments, you should structure the array as:
 *
 * <code>
 * return [
 *     'rule_field_name' => 'the example value in this field',
 * ];
 * </code>
 *
 * Note that query examples are per query argument; route parameters are not tagged with examples.
 * Complex query examples are not supported by the OpenApi spec; however you can specify multiple
 * examples for the key using an array with summary and value keys:
 *
 * <code>
 * return [
 *     'rule_field_name' => [
 *         'example_1' => [
 *             'summary' => '',
 *             'value'   => 'the example value in this field',
 *         ],
 *         'example_2' => [
 *             'summary' => '',
 *             'value'   => 'the example value in this field',
 *         ],
 *     ],
 * ];
 * </code>
 *
 * For body requests, the array is structured as:
 *
 * <code>
 * return [
 *     'example_name' => [
 *         'summary' => '',
 *         'value'   => [
 *             'field' => 'value', // the required fields in the rules
 *         ].
 *     ],
 *     // more examples using other fields as needed
 * ];
 * </code>
 *
 * @package    Somnambulist\Bundles\ApiBundle\Services\Attributes
 * @subpackage Somnambulist\Bundles\ApiBundle\Services\Attributes\OpenApiExamples
 */
#[Attribute(Attribute::TARGET_METHOD)]
class OpenApiExamples
{

}
