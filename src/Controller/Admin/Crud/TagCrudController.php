<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Tag;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Admin\CrudService;
use App\Service\Modules\LangService;
use App\Service\Modules\TranslateService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Service\Modules\ImageService;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class TagCrudController extends AbstractCrudController
{
    private string $lang;

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly CrudService $crudService,
        private readonly LangService $langService,
        private readonly TranslateService $translateService,
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
        private ImageService $imageService,
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
        return Tag::class;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Tag) return;

        $file = $this->getContext()->getRequest()->files->get('Tag')['image'] ?? null;
        $this->imageService->processImage($file, $entityInstance,"tag");

        $this->crudService->setEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Tag) return;

        /** @var UploadedFile|null $file */
        $file = $this->getContext()->getRequest()->files->get('Tag')['image'] ?? null;
        $this->imageService->processImage($file, $entityInstance,"tag");

        $this->crudService->setEntity($entityManager, $entityInstance);
    }
    
    public function configureFields(string $pageName): iterable
    {
        $this->getContext()->getRequest()->setLocale($this->lang);
        $this->translator->getCatalogue($this->lang);
        $this->translator->setLocale($this->lang);
        
        /**
         * on forms
         */
        yield FormField::addTab($this->translateService->translateWords("options"));
            yield Field::new('image', $this->translateService->translateWords("image"))
                ->setFormType(FileType::class)
                ->setFormTypeOptions([
                    'required' => false,
                    'mapped' => false,
                ])
                ->onlyOnForms();
            yield BooleanField::new('active',$this->translateService->translateWords("active"))
                ->renderAsSwitch(true)
                ->setFormTypeOptions(['data' => true])
                ->onlyOnForms();

        // ✅ Default language tab - use custom getter/setter
        yield FormField::addTab($this->translateService->translateWords($this->langService->getDefaultObject()->getName()));
            yield TextField::new('name', $this->translateService->translateWords("name"))
                ->setFormTypeOption('getter', function(Tag $entity) {
                    return $entity->getName($this->langService->getDefault());
                })
                ->setFormTypeOption('setter', function(Tag &$entity, $value) {
                    $entity->setName($value, $this->langService->getDefault());
                })
                ->hideOnIndex();
            yield TextField::new('slug', $this->translateService->translateWords("url"))
                ->setFormTypeOption('getter', function(Tag $entity) {
                    return $entity->getSlug($this->langService->getDefault());
                })
                ->setFormTypeOption('setter', function(Tag &$entity, $value) {
                    $entity->setSlug($value, $this->langService->getDefault());
                })
                ->hideOnIndex();
            yield TextField::new('title', $this->translateService->translateWords("title"))
                ->setFormTypeOption('getter', function(Tag $entity) {
                    return $entity->getTitle($this->langService->getDefault());
                })
                ->setFormTypeOption('setter', function(Tag &$entity, $value) {
                    $entity->setTitle($value, $this->langService->getDefault());
                })
                ->hideOnIndex();
            yield TextField::new('meta_desc', $this->translateService->translateWords("meta_desc","meta desc"))
                ->setFormTypeOption('getter', function(Tag $entity) {
                    return $entity->getMetaDesc($this->langService->getDefault());
                })
                ->setFormTypeOption('setter', function(Tag &$entity, $value) {
                    $entity->setMetaDesc($value, $this->langService->getDefault());
                })
                ->hideOnIndex();
        
        // ✅ Other language tabs - use custom getter/setter for each
        foreach($this->langService->getLangs() as $lang){
            if(!$lang->isDefault()){
                $langCode = $lang->getCode();
                
                yield FormField::addTab($this->translateService->translateWords($lang->getName()));
                
                yield TextField::new('name_' . $langCode, $this->translateService->translateWords("name"))
                    ->setFormTypeOption('getter', function(Tag $entity) use ($langCode) {
                        return $entity->getName($langCode);
                    })
                    ->setFormTypeOption('setter', function(Tag &$entity, $value) use ($langCode) {
                        $entity->setName($value, $langCode);
                    })
                    ->hideOnIndex();
                
                yield TextField::new('slug_' . $langCode, $this->translateService->translateWords("url"))
                    ->setFormTypeOption('getter', function(Tag $entity) use ($langCode) {
                        return $entity->getSlug($langCode);
                    })
                    ->setFormTypeOption('setter', function(Tag &$entity, $value) use ($langCode) {
                        $entity->setSlug($value, $langCode);
                    })
                    ->hideOnIndex();
                
                yield TextField::new('title_' . $langCode, $this->translateService->translateWords("title"))
                    ->setFormTypeOption('getter', function(Tag $entity) use ($langCode) {
                        return $entity->getTitle($langCode);
                    })
                    ->setFormTypeOption('setter', function(Tag &$entity, $value) use ($langCode) {
                        $entity->setTitle($value, $langCode);
                    })
                    ->hideOnIndex();
                
                yield TextField::new('meta_desc_' . $langCode, $this->translateService->translateWords("meta_desc","meta desc"))
                    ->setFormTypeOption('getter', function(Tag $entity) use ($langCode) {
                        return $entity->getMetaDesc($langCode);
                    })
                    ->setFormTypeOption('setter', function(Tag &$entity, $value) use ($langCode) {
                        $entity->setMetaDesc($value, $langCode);
                    })
                    ->hideOnIndex();
            }
        }
        
        /**
         * index
         */
        yield TextField::new('name', $this->translateService->translateWords("name"))
            ->formatValue(function ($value, Tag $entity) {
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
            ->formatValue(function ($value, Tag $entity) {
                return $entity->getSlug($this->langService->getDefault());
            })
            ->onlyOnIndex();
        
        yield DateField::new('created_at', $this->translateService->translateWords("created_at", "created"))->hideOnForm();
        yield DateField::new('modified_at',$this->translateService->translateWords("modified_at", "modified"))->hideOnForm();
        yield BooleanField::new('active', $this->translateService->translateWords("active"))
            ->renderAsSwitch(true)
            ->onlyOnIndex();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->addFormTheme('@FOSCKEditor/Form/ckeditor_widget.html.twig')
            ->addFormTheme('admin/tag/tag_upload_with_preview.html.twig')
            ->addFormTheme('@EasyAdmin/crud/form_theme.html.twig');
    }
}
