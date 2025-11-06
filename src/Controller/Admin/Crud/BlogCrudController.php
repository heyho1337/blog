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
        yield FormField::addTab($this->translateService->translateWords("options"));
            yield AssociationField::new('category', $this->translateService->translateWords("category"))
                ->setRequired(true)
                ->autocomplete()
                ->hideOnIndex()
                ->setCrudController(CategoryCrudController::class);
            yield AssociationField::new('tags', $this->translateService->translateWords("tags"))
                ->setRequired(false)
                ->autocomplete()
                ->hideOnIndex();
            yield Field::new('image', $this->translateService->translateWords('image'))
                ->setFormType(FileType::class)
                ->setFormTypeOptions([
                    'required' => false,
                    'mapped' => false,
                    'attr' => [
                        'upload_base_path' => '/uploads/blog',
                    ],
                ])
                ->onlyOnForms();
            yield BooleanField::new('active',$this->translateService->translateWords("active"))
                ->renderAsSwitch(true)
                ->setFormTypeOptions(['data' => true])
                ->onlyOnForms();

        // ✅ Default language tab - use custom getter/setter
        yield FormField::addTab($this->translateService->translateWords($this->langService->getDefaultObject()->getName()));
            yield TextField::new('name', $this->translateService->translateWords("name"))
                ->setFormTypeOption('getter', function(Blog $entity) {
                    return $entity->getName($this->langService->getDefault());
                })
                ->setFormTypeOption('setter', function(Blog &$entity, $value) {
                    $entity->setName($value, $this->langService->getDefault());
                })
                ->hideOnIndex();
            yield TextField::new('slug', $this->translateService->translateWords("url"))
                ->setFormTypeOption('getter', function(Blog $entity) {
                    return $entity->getSlug($this->langService->getDefault());
                })
                ->setFormTypeOption('setter', function(Blog &$entity, $value) {
                    $entity->setSlug($value, $this->langService->getDefault());
                })
                ->hideOnIndex();
            yield TextField::new('title', $this->translateService->translateWords("title"))
                ->setFormTypeOption('getter', function(Blog $entity) {
                    return $entity->getTitle($this->langService->getDefault());
                })
                ->setFormTypeOption('setter', function(Blog &$entity, $value) {
                    $entity->setTitle($value, $this->langService->getDefault());
                })
                ->hideOnIndex();
            yield TextareaField::new('short_desc', $this->translateService->translateWords("short_description","short description"))
                ->setFormTypeOption('getter', function(Blog $entity) {
                    return $entity->getShortDesc($this->langService->getDefault());
                })
                ->setFormTypeOption('setter', function(Blog &$entity, $value) {
                    $entity->setShortDesc($value, $this->langService->getDefault());
                })
                ->hideOnIndex();
            yield Field::new('text', $this->translateService->translateWords("text"))
                ->setFormType(CKEditorType::class)
                ->setFormTypeOption('getter', function(Blog $entity) {
                    return $entity->getText($this->langService->getDefault());
                })
                ->setFormTypeOption('setter', function(Blog &$entity, $value) {
                    $entity->setText($value, $this->langService->getDefault());
                })
                ->onlyOnForms();
            yield TextField::new('meta_desc', $this->translateService->translateWords("meta_desc","meta desc"))
                ->setFormTypeOption('getter', function(Blog $entity) {
                    return $entity->getMetaDesc($this->langService->getDefault());
                })
                ->setFormTypeOption('setter', function(Blog &$entity, $value) {
                    $entity->setMetaDesc($value, $this->langService->getDefault());
                })
                ->hideOnIndex();
        
        // ✅ Other language tabs - use custom getter/setter for each
        foreach($this->langService->getLangs() as $lang){
            if(!$lang->isDefault()){
                $langCode = $lang->getCode();
                
                yield FormField::addTab($this->translateService->translateWords($lang->getName()));
                
                yield TextField::new('name_' . $langCode, $this->translateService->translateWords("name"))
                    ->setFormTypeOption('getter', function(Blog $entity) use ($langCode) {
                        return $entity->getName($langCode);
                    })
                    ->setFormTypeOption('setter', function(Blog &$entity, $value) use ($langCode) {
                        $entity->setName($value, $langCode);
                    })
                    ->hideOnIndex();
                    
                yield TextField::new('slug_' . $langCode, $this->translateService->translateWords("url"))
                    ->setFormTypeOption('getter', function(Blog $entity) use ($langCode) {
                        return $entity->getSlug($langCode);
                    })
                    ->setFormTypeOption('setter', function(Blog &$entity, $value) use ($langCode) {
                        $entity->setSlug($value, $langCode);
                    })
                    ->hideOnIndex();
                    
                yield TextField::new('title_' . $langCode, $this->translateService->translateWords("title"))
                    ->setFormTypeOption('getter', function(Blog $entity) use ($langCode) {
                        return $entity->getTitle($langCode);
                    })
                    ->setFormTypeOption('setter', function(Blog &$entity, $value) use ($langCode) {
                        $entity->setTitle($value, $langCode);
                    })
                    ->hideOnIndex();
                    
                yield TextareaField::new('short_desc_' . $langCode, $this->translateService->translateWords("short_description","short description"))
                    ->setFormTypeOption('getter', function(Blog $entity) use ($langCode) {
                        return $entity->getShortDesc($langCode);
                    })
                    ->setFormTypeOption('setter', function(Blog &$entity, $value) use ($langCode) {
                        $entity->setShortDesc($value, $langCode);
                    })
                    ->hideOnIndex();
                    
                yield Field::new('text_' . $langCode, $this->translateService->translateWords("text"))
                    ->setFormType(CKEditorType::class)
                    ->setFormTypeOption('getter', function(Blog $entity) use ($langCode) {
                        return $entity->getText($langCode);
                    })
                    ->setFormTypeOption('setter', function(Blog &$entity, $value) use ($langCode) {
                        $entity->setText($value, $langCode);
                    })
                    ->onlyOnForms();
                    
                yield TextField::new('meta_desc_' . $langCode, $this->translateService->translateWords("meta_desc","meta desc"))
                    ->setFormTypeOption('getter', function(Blog $entity) use ($langCode) {
                        return $entity->getMetaDesc($langCode);
                    })
                    ->setFormTypeOption('setter', function(Blog &$entity, $value) use ($langCode) {
                        $entity->setMetaDesc($value, $langCode);
                    })
                    ->hideOnIndex();
            }
        }
        
        /**
         * index
         */
        yield TextField::new('name', $this->translateService->translateWords("name"))
            ->formatValue(function ($value, Blog $entity) {
                $default = $this->langService->getDefault();
                $name = $entity->getName($default);
                
                $url = $this->adminUrlGenerator
                    ->setController(self::class)
                    ->setAction('edit')
                    ->setEntityId($entity->getId())
                    ->generateUrl();

                return sprintf('<a href="%s">%s</a>', $url, htmlspecialchars($name));
            })
            ->onlyOnIndex()
            ->renderAsHtml();
        yield TextField::new('slug', $this->translateService->translateWords("url"))
            ->formatValue(function ($value, Blog $entity) {
                $default = $this->langService->getDefault();
                return $entity->getSlug($default);
            })
            ->onlyOnIndex();
        yield ImageField::new('image', $this->translateService->translateWords("image"))
            ->setBasePath('/uploads/blog')
            ->formatValue(function ($value, $entity) {
                if (!$value) {
                    return null;
                }

                return "/uploads/blog/{$value}.webp";
            })
            ->onlyOnIndex();
        yield DateField::new('created_at', $this->translateService->translateWords("created_at","created"))->hideOnForm();
        yield DateField::new('modified_at',$this->translateService->translateWords("modified_at","modified"))->hideOnForm();
        yield AssociationField::new('category',$this->translateService->translateWords("category"))->onlyOnIndex();
        yield BooleanField::new('active', $this->translateService->translateWords("active"))
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