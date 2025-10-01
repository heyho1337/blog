<?php

namespace App\State;

use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Blog;
use App\Repository\BlogRepository;
use App\Service\Modules\CacheService;
use App\Service\Modules\LangService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Cache\CacheInterface;

final class BlogBySlugStateProvider implements ProviderInterface
{
    public function __construct(
        private readonly BlogRepository $blogRepository,
        private CacheService $cache,
        private readonly LangService $langService
    )
    {

    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?Blog
    {
        

        if ($operation->getName() !== 'get_blog_by_slug') {
            return null;
        }

        if (!isset($uriVariables['slug'])) {
            throw new NotFoundHttpException('Slug parameter missing.');
        }

        $alias = $context['request']->attributes->get('slug');

        $slug_column = "slug_".$this->langService->getCurrentLang();
        $blog = $this->cache->getFromCache("blog_by_slug",$alias,function() use ($alias,$slug_column) {
            return $this->blogRepository->findOneBy([$slug_column => $alias]);
        });

        if (!$blog) {
            throw new NotFoundHttpException('Blog not found.');
        }

        return $blog;
    }
}

