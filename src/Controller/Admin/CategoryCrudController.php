<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Category;
use App\Service\Admin\CrudService;
use App\Service\Modules\LangService;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;

class CategoryCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly CrudService $crudService,
        private readonly LangService $langService
    ) {}
    
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Category) return;

        $this->crudService->setEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Category) return;

        $this->crudService->setEntity($entityManager, $entityInstance);
    }

    public function configureFields(string $pageName): iterable
    {
        /**
         * on forms
         */
        foreach($this->langService->getLangs() as $lang){
            yield FormField::addTab($lang->getLangsName());
            yield TextField::new('name_'.$lang->getLangsCode())
                ->hideOnIndex();
            yield TextField::new('title_'.$lang->getLangsCode())->hideOnIndex();
            yield TextField::new('short_desc_'.$lang->getLangsCode())->hideOnIndex();
            yield Field::new('text_'.$lang->getLangsCode())
                ->setFormType(CKEditorType::class)
                ->onlyOnForms();
            yield TextField::new('meta_desc_'.$lang->getLangsCode())->hideOnIndex();
            /*
            yield Field::new('image', 'Image')
                ->setFormType(FileType::class)
                ->setFormTypeOptions([
                    'required' => false,
                    'mapped' => false,
                ])
                ->onlyOnForms();
            */
        }
        
        /**
         * index
         */
        yield TextField::new('name_hu')
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
        yield TextField::new('slug_hu')->onlyOnIndex();
        yield ImageField::new('image')
            ->setBasePath('/uploads/categories') // path where it's accessible
            ->onlyOnIndex();
        yield DateField::new('created_at')->hideOnForm();
        yield DateField::new('modified_at')->hideOnForm();
        yield AssociationField::new('articles')->hideOnForm();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->addFormTheme('@FOSCKEditor/Form/ckeditor_widget.html.twig');
    }
}
