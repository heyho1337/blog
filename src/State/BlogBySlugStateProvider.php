<?php

namespace App\State;

use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Blog;
use App\Repository\BlogRepository;
use App\Service\Modules\CacheService;
use App\Service\Modules\LangService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        $blog = $this->cache->get("blog:alias:".$alias.":".$this->langService->getCurrentLang());

        if (!$blog) {
            throw new NotFoundHttpException('Blog not found.');
        }

        return $blog;
    }
}

