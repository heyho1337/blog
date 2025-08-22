<?php

namespace App\Controller\Admin;

use App\Entity\Tag;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\CrudService;

class TagCrudController extends AbstractCrudController
{
    
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly CrudService $crudService
    ) {}
    
    public static function getEntityFqcn(): string
    {
        return Tag::class;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Tag) return;

        $this->crudService->setEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Tag) return;

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
        yield DateField::new('created_at')->hideOnForm();
        yield DateField::new('modified_at')->hideOnForm();
    }
    
}
