<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Tag;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
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
        Tag::setCurrentLang($this->lang);
        $this->getContext()->getRequest()->setLocale($this->lang);
        $this->translator->getCatalogue($this->lang);
        $this->translator->setLocale($this->lang);
        
        
        /**
         * on forms
         */
        yield FormField::addTab($this->translateService->translateSzavak("options"));
            yield Field::new('image', $this->translateService->translateSzavak("image"))
                ->setFormType(FileType::class)
                ->setFormTypeOptions([
                    'required' => false,
                    'mapped' => false,
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
            yield TextField::new('meta_desc_'.$this->langService->getDefault(), $this->translateService->translateSzavak("meta_desc","meta desc"))->hideOnIndex();
        
        foreach($this->langService->getLangs() as $lang){
            if(!$lang->isDefault()){
                yield FormField::addTab($this->translateService->translateSzavak($lang->getName()));
                yield TextField::new('name_'.$lang->getCode(), $this->translateService->translateSzavak("name"))
                    ->hideOnIndex();
                yield TextField::new('slug_'.$lang->getCode(), $this->translateService->translateSzavak("url"))
                ->hideOnIndex();
                yield TextField::new('title_'.$lang->getCode(), $this->translateService->translateSzavak("title"))->hideOnIndex();
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
        yield DateField::new('created_at', $this->translateService->translateSzavak("created_at", "created"))->hideOnForm();
        yield DateField::new('modified_at',$this->translateService->translateSzavak("modified_at", "modified"))->hideOnForm();
        yield BooleanField::new('active', $this->translateService->translateSzavak("active"))
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
