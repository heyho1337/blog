<?php

namespace App\Service\Pages;

use App\Entity\EvcBlog;
use App\Entity\EvcMenu;
use App\Repository\EvcBlogRepository;
use App\Service\Modules\BlogService;
use App\Service\Modules\LangService;
use App\Service\Modules\SeoService;
use App\Service\Modules\ModulService;
use App\Service\Modules\TextService;

class BlogArticleService extends SubPageService implements \App\Interface\PageInterface
{
    private string $alias;
    private string $url;
    private string $title;
    private string $blogAlias;
    private string $content;

    public function __construct(
        private readonly SeoService              $seoService,
        private readonly LangService             $langService,
        private readonly EvcBlogRepository       $blogRepo,
        private readonly ModulService            $modulService,
        private readonly TextService             $textService,
        private readonly BlogService             $blogService
    )
    {
        parent::__construct($seoService,$langService);
    }

    public function getData(EvcMenu $currentPage, $additionalData = []): array
    {
        $lastPage = end($additionalData);

        $blogAlias = "blog_alias_{$this->langService->getCurrentLang()}";

        $this->blogArticle = $this->blogRepo->findOneBy([$blogAlias => $lastPage]);

        $this->alias = $currentPage->getMenuAlias();
        $this->title = $this->blogArticle->getBlogCim();
        $this->content = $this->textService->setText($this->blogArticle->getBlogSzoveg());
        $this->blogAlias = $this->blogArticle->getBlogAlias();

        $ajanlat = $this->textService->setText($this->modulService->getModul(15));

        $additionalData = [
            'content' => $this->content,
            'title' => $this->title,
            'ajanlat' => $ajanlat
        ];

        return parent::getData($currentPage, $additionalData);
    }

    public function getBreadCrumbs(string $url): string
    {

        $breadCrumbs = $this->seoService->getMainBreadCrumbs();
        $breadCrumbs["itemListElement"][] = [
            "@type" => "ListItem",
            "position" => 2,
            "name" => $this->seoService->getTitle(),
            "item" => $this->seoService->getSiteurl().$url
        ];

        $breadCrumbs["itemListElement"][] = [
            "@type" => "ListItem",
            "position" => 3,
            "name" => $this->title,
            "item" => "{$this->seoService->getSiteurl()}{$this->alias}/{$this->blogAlias}"
        ];

        $this->url = $this->seoService->getSiteurl().$this->alias."/".$this->blogAlias;

        return json_encode($breadCrumbs);
    }

    public function setSchema(EvcMenu $currentPage): void
    {
        parent::setSchema($currentPage);

        $articleSchema = [
            "@context" => "https://schema.org",
            "@type" => "NewsArticle",
            "headline" => $this->title,
            "image" => [
                $this->blogService->getSiteUrl()."public/assets/uploaded_images/blog/".$this->blogArticle->getBlogKezdoKep()
            ],
            "datePublished" => $this->blogArticle->getBlogDate()?->format('Y-m-d'),
            "dateModified" => $this->blogArticle->getBlogDatetime()?->format('Y-m-d')
        ];

        $finalSchema = json_encode([
            ...$articleSchema
        ],JSON_UNESCAPED_UNICODE);

        $this->seoService->setSchema($finalSchema);
    }

    public function setMeta(EvcMenu $currentPage): void
    {
        //dd($this->blogArticle);
        $this->seoService->setTitle($this->blogArticle->getBlogSeoTitle());

        $this->seoService->setDesc($this->blogArticle->getMetaDesc());

        $this->seoService->setImg("/assets/main/logo.svg");
        $this->seoService->setBreadCrumbs($this->getBreadCrumbs($currentPage->getMenuAlias()));
    }
}