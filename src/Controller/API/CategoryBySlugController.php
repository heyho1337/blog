<?php

namespace App\Controller\API;

use App\Repository\CategoryRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryBySlugController
{
    private CategoryRepository $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function __invoke(string $slug)
    {
        $category = $this->categoryRepository->findOneBy(['slug' => $slug]);

        if (!$category) {
            throw new NotFoundHttpException('Category not found');
        }

        return $category;
    }
}