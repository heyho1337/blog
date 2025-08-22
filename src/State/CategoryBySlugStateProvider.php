<?php
// src/State/CategoryBySlugStateProvider.php

namespace App\State;

use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Service\Modules\CacheService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use App\Service\Modules\LangService;
use App\Service\Modules\TranslateService;

final class CategoryBySlugStateProvider implements ProviderInterface
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private CacheService $cache,
        private readonly LoggerInterface $logger,
        private readonly LangService $langService,
        private readonly TranslateService $translateService,
    )
    {

    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?Category
    {
        

        if ($operation->getName() !== 'get_category_by_slug') {
            return null;
        }

        if (!isset($uriVariables['slug'])) {
            throw new NotFoundHttpException('Slug parameter missing.');
        }

        $request = $context['request'] ?? null;

        $slug_column = "slug_".$this->langService->getCurrentLang();

        $alias = $request->attributes->get('slug');
        $category = $this->cache->getFromCache("category_by_slug",$alias,function() use ($alias,$slug_column) {
            return $this->categoryRepository->findOneBy([$slug_column => $alias]);
        });

        if (!$category) {
            throw new NotFoundHttpException('Category not found.');
        }

        return $category;
    }
}

