<?php

namespace App\Service\Modules;

use App\Entity\EvcBlog;
use App\Service\Modules\LangService;

class BlogService
{
    private string $siteUrl;
    private array $blogSchema;

    public function __construct(
        private readonly CommonService $commonService,
        private readonly LangService $langService,
    )
    {
        $this->siteUrl = $this->commonService->getConfig()->getSiteurl();
    }

    public function setBlogListData(EvcBlog $blog): void
    {
        $this->blogSchema[] = [
            "@context" => "https://schema.org",
            "@type" => "NewsArticle",
            "headline" => $blog->getBlogCim(),
            "image" => [
                $this->siteUrl . "public/assets/uploaded_images/blog/" . $blog->getBlogKezdoKep()
            ],
            "datePublished" => $blog->getBlogDate()?->format('Y-m-d'),
            "dateModified" => $blog->getBlogDatetime()?->format('Y-m-d'),
            "description" => $blog->getMetaDesc(),
            "alternativeHeadline" => $blog->getBlogSeoTitle(),
        ];
    }

    public function getSiteUrl(): string
    {
        return $this->siteUrl;
    }

    public function getBlogSchema(): array
    {
        return $this->blogSchema;
    }
}