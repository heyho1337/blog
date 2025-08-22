<?php

namespace App\Controller\Admin;

use App\Entity\Blog;
use App\Service\CrudService;
use App\Service\ImageService;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;


class BlogCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private ImageService $imageService,
        private readonly CrudService $crudService
    ) {}
    
    public static function getEntityFqcn(): string
    {
        return Blog::class;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Blog) return;

        $file = $this->getContext()->getRequest()->files->get('Blog')['image'] ?? null;
        $this->imageService->processImage($file, $entityInstance);

        $this->crudService->setEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Blog) return;

        /** @var UploadedFile|null $file */
        $file = $this->getContext()->getRequest()->files->get('Blog')['image'] ?? null;
        $this->imageService->processImage($file, $entityInstance);

        $this->crudService->setEntity($entityManager, $entityInstance);
    }

    public function configureFields(string $pageName): iterable
    {
        /**
         * on forms
         */
        yield TextField::new('name')
            ->hideOnIndex();
        yield TextField::new('title')->hideOnIndex();
        yield AssociationField::new('category')
            ->setRequired(true)
            ->autocomplete()
            ->hideOnIndex();
        
        yield AssociationField::new('tags')
            ->setRequired(true)
            ->autocomplete()
            ->hideOnIndex();
        yield Field::new('image', '')
            ->setFormType(FileType::class)
            ->setFormTypeOptions([
                'required' => false,
                'mapped' => false,
            ])
            ->onlyOnForms();
        yield TextareaField::new('short_desc')->hideOnIndex();
        yield Field::new('text')
            ->setFormType(CKEditorType::class)
            ->onlyOnForms();
        yield TextField::new('meta_desc')->hideOnIndex();
        
        /**
         * index
         */
        yield TextField::new('name')
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
        yield TextField::new('slug')->onlyOnIndex();
        yield ImageField::new('image')
            ->setBasePath('/uploads/blog')
            ->formatValue(function ($value, $entity) {
                if (!$value) {
                    return null;
                }

                return "/uploads/blog/{$value}_cropped.webp";
            })
            ->onlyOnIndex();
        yield DateField::new('created_at')->hideOnForm();
        yield DateField::new('modified_at')->hideOnForm();
        yield AssociationField::new('category')->onlyOnIndex();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->addFormTheme('@FOSCKEditor/Form/ckeditor_widget.html.twig')
            ->addFormTheme('admin/form/image_upload_with_preview.html.twig')
            ->addFormTheme('@EasyAdmin/crud/form_theme.html.twig');
    }
}
