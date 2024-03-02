<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Controllers;

use Psr\Cache\CacheItemPoolInterface;
use RuntimeException;
use Somnambulist\Bundles\ApiBundle\Services\OpenApiGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ApiDocController extends AbstractController
{
    public function __construct(
        private readonly OpenApiGenerator $generator,
        private readonly CacheItemPoolInterface $cache,
        private readonly int $cacheTime = 43200
    ) {
    }

    public function __invoke(): Response
    {
        if (!$this->container->has('twig')) {
            throw new RuntimeException('API documentation requires TwigBundle be registered');
        }

        $item = $this->cache->getItem('somnambulist_api_bundle_api_documentation');

        if (!$item->isHit()) {
            $item->expiresAfter($this->cacheTime);
            $item->set($this->generator->discover()->toArray());

            $this->cache->save($item);
        }

        return $this->render('@SomnambulistApi/redoc.twig', [
            'title' => $item->get()['info']['title'],
            'data'  => $item->get(),
        ]);
    }
}
