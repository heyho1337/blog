<?php

namespace App\Service\Pages;

use App\Interface\PageInterface;
use App\Repository\EvcBlogRepository;
use App\Service\Modules\BlogService;
use App\Service\Modules\CommonService;
use App\Service\Modules\LangService;
use App\Service\Modules\ModulService;
use App\Service\Modules\SeoService;
use App\Service\Modules\TextService;
use App\Entity\EvcMenu;

class BlogListService extends SubPageService implements PageInterface
{

    public function __construct(
        private readonly SeoService              $seoService,
        private readonly LangService             $langService,
        private readonly EvcBlogRepository       $blogRepo,
        private readonly CommonService           $commonService,
        private readonly ModulService            $modulService,
        private readonly TextService             $textService,
        private readonly BlogService             $blogService
    )
    {
        parent::__construct($seoService,$langService);
    }

    public function getData(EvcMenu $currentPage, $additionalData = []): array
    {

        $header = $this->textService->setText($this->modulService->getModul(1));
        $idopont = $this->textService->setText($this->modulService->getModul(2));

        $blogList = $this->blogRepo->findBy(['blog_aktiv' => 1],['blog_datetime' => 'DESC']);

        foreach($blogList as $blog){
            $this->blogService->setBlogListData($blog);
        }

        $additionalData = [
            'blogList' => $blogList,
            'header' => $header,
            'idopont' => $idopont,
        ];

        return parent::getData($currentPage, $additionalData);
    }

    public function setSchema(EvcMenu $currentPage): void
    {
        parent::setSchema($currentPage);

        $finalSchema = json_encode([
            ...$this->blogService->getBlogSchema(),
        ],JSON_UNESCAPED_UNICODE);

        $this->seoService->setSchema($finalSchema);
    }
}
