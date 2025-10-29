<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Blog;
use App\Service\Admin\CrudService;
use App\Service\Modules\ImageService;
use App\Entity\Category;
use App\Service\Modules\LangService;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use App\Service\Modules\TranslateService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;


class BlogCrudController extends AbstractCrudController
{

    private string $lang;

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private ImageService $imageService,
        private readonly CrudService $crudService,
        private readonly LangService $langService,
        private readonly TranslateService $translateService,
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
    ) {
        $this->lang = $this->langService->getDefault();
        if($this->requestStack->getCurrentRequest()){
            $locale = $this->requestStack->getCurrentRequest()->getSession()->get('_locale');
            if($locale){
                $this->lang = $this->requestStack->getCurrentRequest()->getSession()->get('_locale');
                $this->translateService->setLangs($this->lang);
                $this->langService->setLang($this->lang);
            }
        }
    }
    
    public static function getEntityFqcn(): string
    {
        return Blog::class;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Blog) return;

        $file = $this->getContext()->getRequest()->files->get('Blog')['image'] ?? null;
        $this->imageService->processImage($file, $entityInstance,"blog",$_ENV['BLOG_w'],$_ENV['BLOG_H']);
        
        $this->crudService->setEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Blog) return;

        /** @var UploadedFile|null $file */
        $file = $this->getContext()->getRequest()->files->get('Blog')['image'] ?? null;
        $this->imageService->processImage($file, $entityInstance,"blog",$_ENV['BLOG_w'],$_ENV['BLOG_H']);
        
        $this->crudService->setEntity($entityManager, $entityInstance);
    }

    public function configureFields(string $pageName): iterable
    {
        Category::setCurrentLang($this->lang);
        $this->getContext()->getRequest()->setLocale($this->lang);
        $this->translator->getCatalogue($this->lang);
        $this->translator->setLocale($this->lang);
        /**
         * on forms
         */
        yield FormField::addTab($this->translateService->translateSzavak("options"));
            yield AssociationField::new('category', $this->translateService->translateSzavak("category"))
                ->setRequired(true)
                ->autocomplete()
                ->hideOnIndex()
                ->setCrudController(CategoryCrudController::class);
            yield AssociationField::new('tags', $this->translateService->translateSzavak("tags"))
                ->setRequired(false)
                ->autocomplete()
                ->hideOnIndex();
            yield Field::new('image', $this->translateService->translateSzavak('image'))
                ->setFormType(FileType::class)
                ->setFormTypeOptions([
                    'required' => false,
                    'mapped' => false,
                    'attr' => [
                        'upload_base_path' => '/uploads/blog',
                    ],
                ])
                ->onlyOnForms();
            yield BooleanField::new('active',$this->translateService->translateSzavak("active"))
                ->renderAsSwitch(true)
                ->setFormTypeOptions(['data' => true])
                ->onlyOnForms();

        yield FormField::addTab($this->translateService->translateSzavak($this->langService->getDefaultObject()->getName()));
            yield TextField::new('name_'.$this->langService->getDefault(), $this->translateService->translateSzavak("name"))
                ->hideOnIndex();
            yield TextField::new('slug_'.$this->langService->getDefault(), $this->translateService->translateSzavak("url"))
                ->hideOnIndex();
            yield TextField::new('title_'.$this->langService->getDefault(), $this->translateService->translateSzavak("title"))->hideOnIndex();
            yield TextareaField::new('short_desc_'.$this->langService->getDefault(), $this->translateService->translateSzavak("short_description","short description"))->hideOnIndex();
            yield Field::new('text_'.$this->langService->getDefault(), $this->translateService->translateSzavak("text"))
                ->setFormType(CKEditorType::class)
                ->onlyOnForms();
            yield TextField::new('meta_desc_'.$this->langService->getDefault(), $this->translateService->translateSzavak("meta_desc","meta desc"))->hideOnIndex();
        
        foreach($this->langService->getLangs() as $lang){
            if(!$lang->isDefault()){
                yield FormField::addTab($this->translateService->translateSzavak($lang->getName()));
                yield TextField::new('name_'.$lang->getCode(), $this->translateService->translateSzavak("name"))
                    ->hideOnIndex();
                yield TextField::new('slug_'.$lang->getCode(), $this->translateService->translateSzavak("url"))
                ->hideOnIndex();
                yield TextField::new('title_'.$lang->getCode(), $this->translateService->translateSzavak("title"))->hideOnIndex();
                yield TextareaField::new('short_desc_'.$lang->getCode(), $this->translateService->translateSzavak("short_description","short description"))->hideOnIndex();
                yield Field::new('text_'.$lang->getCode(), $this->translateService->translateSzavak("text"))
                    ->setFormType(CKEditorType::class)
                    ->onlyOnForms();
                yield TextField::new('meta_desc_'.$lang->getCode(), $this->translateService->translateSzavak("meta_desc","meta desc"))->hideOnIndex();
            }
        }
        
        /**
         * index
         */
        yield TextField::new('name_'.$this->langService->getDefault(), $this->translateService->translateSzavak("name"))
            ->formatValue(function ($value, $entity) {
                $url = $this->adminUrlGenerator
                    ->setController(self::class)
                    ->setAction('edit')
                    ->setEntityId($entity->getId())
                    ->generateUrl();

                return sprintf('<a href="%s">%s</a>', $url, htmlspecialchars($value));
            })
            ->onlyOnIndex()
            ->renderAsHtml();
        yield TextField::new('slug_'.$this->langService->getDefault(), $this->translateService->translateSzavak("url"))->onlyOnIndex();
        yield ImageField::new('image', $this->translateService->translateSzavak("image"))
            ->setBasePath('/uploads/blog')
            ->formatValue(function ($value, $entity) {
                if (!$value) {
                    return null;
                }

                return "/uploads/blog/{$value}.webp";
            })
            ->onlyOnIndex();
        yield DateField::new('created_at', $this->translateService->translateSzavak("created_at","created"))->hideOnForm();
        yield DateField::new('modified_at',$this->translateService->translateSzavak("modified_at","modified"))->hideOnForm();
        yield AssociationField::new('category',$this->translateService->translateSzavak("category"))->onlyOnIndex();
        yield BooleanField::new('active', $this->translateService->translateSzavak("active"))
            ->renderAsSwitch(true)
            ->onlyOnIndex();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->addFormTheme('@FOSCKEditor/Form/ckeditor_widget.html.twig')
            ->addFormTheme('admin/blog/blog_upload_with_preview.html.twig')
            ->addFormTheme('@EasyAdmin/crud/form_theme.html.twig');
    }
}
