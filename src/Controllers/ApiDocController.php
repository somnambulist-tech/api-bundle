<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Controllers;

use Psr\Cache\CacheItemPoolInterface;
use RuntimeException;
use Somnambulist\Bundles\ApiBundle\Services\OpenApiGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class ApiDocController
 *
 * @package    Somnambulist\Bundles\ApiBundle\Controllers
 * @subpackage Somnambulist\Bundles\ApiBundle\Controllers\ApiDocController
 */
class ApiDocController extends AbstractController
{

    private OpenApiGenerator $generator;
    private CacheItemPoolInterface $cache;
    private int $cacheTime;

    public function __construct(OpenApiGenerator $generator, CacheItemPoolInterface $cache, int $cacheTime = 43200)
    {
        $this->generator = $generator;
        $this->cache     = $cache;
        $this->cacheTime = $cacheTime;
    }

    public function __invoke()
    {
        if (!$this->has('twig')) {
            throw new RuntimeException('API documentation requires TwigBundle be registered');
        }

        $item = $this->cache->getItem('somnambulist_api_bundle_api_documentation');

        if (!$item->isHit()) {
            $item->expiresAfter($this->cacheTime);
            $item->set($this->generator->discover()->toArray());
        }

        return $this->render('@SomnambulistApi/redoc.twig', [
            'title' => $item->get()['info']['title'],
            'data'  => $item->get(),
        ]);
    }
}
