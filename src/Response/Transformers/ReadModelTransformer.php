<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Response\Transformers;

use League\Fractal\TransformerAbstract;
use Somnambulist\ReadModels\Model;

/**
 * Class ReadModelTransformer
 *
 * Basic transformer that will cast ReadModels to arrays (if used).
 *
 * @package    Somnambulist\ApiBundle\Response\Transformers
 * @subpackage Somnambulist\ApiBundle\Response\Transformers\ReadModelTransformer
 */
final class ReadModelTransformer extends TransformerAbstract
{

    public function transform(Model $model): array
    {
        return $model->toArray();
    }
}
