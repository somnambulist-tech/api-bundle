<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Response\Transformers;

use League\Fractal\TransformerAbstract;
use Somnambulist\Components\ReadModels\Model;

/**
 * Basic transformer that will cast ReadModels to arrays (if used).
 */
final class ReadModelTransformer extends TransformerAbstract
{
    public function transform(Model $model): array
    {
        return $model->toArray();
    }
}
