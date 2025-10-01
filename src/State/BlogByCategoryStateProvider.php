<?php

namespace App\State;

use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\Operation;
use App\Repository\BlogRepository;
use App\Service\Modules\CacheService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class BlogByCategoryStateProvider implements ProviderInterface
{

    public function __construct(
        private BlogRepository $blogRepository,
        private CacheService $cache
    )
    {

    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?Paginator
    {
        if ($operation->getName() !== 'get_blog_by_category') {
            return null;
        }

        if (!isset($uriVariables['id'])) {
            throw new NotFoundHttpException('Category ID parameter missing.');
        }

        //dd($context);
        $limit = $_ENV['PAGINATION_PER_PAGE'];
        $page = $context['filters']['page'] ?? 0;
        $offset = 0;
        if($page > 1){
            $offset = $page * $limit;
        }

        $blogList = $this->cache->getFromCache("blog_by_category",$uriVariables['id']."_".$limit."_".$offset,function() use ($uriVariables, $limit, $offset) {
            return $this->blogRepository->findByCategoryPaginated($uriVariables['id'], $limit, $offset);
        });

        $totalItems = $this->cache->getFromCache("blog_total_by_category",$uriVariables['id'],function() use ($uriVariables) {
            return $this->blogRepository->countByCategory($uriVariables['id']);
        });

        if (!$blogList) {
            throw new NotFoundHttpException('BlogList not found.');
        }

        return new Paginator($blogList, $totalItems);
    }

}
