<?php

namespace App\Controller\Admin;

use App\Entity\Tablette;
use EasyCorp\Bundle\EasyAdminBundle\Config\{Crud, Filters};
use EasyCorp\Bundle\EasyAdminBundle\Field\{
    AssociationField,
    ColorField,
    DateTimeField,
    FormField,
    IdField,
    SlugField,
    TextEditorField,
    TextField,
};

class TabletteCrudController extends AppAbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Tablette::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Tablette')
            ->setEntityLabelInPlural('Tablettes')
            ->setDefaultSort(['name' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addFieldset('Identification', 'fas fa-circle-info text-info'),
            TextField::new('name')->setColumns(2),
            AssociationField::new('parent')->setColumns(2),

            FormField::addFieldset('Qualification', 'fas fa-info text-info'),
            TextField::new('icon')->setColumns(2),
            ColorField::new('color')->setColumns(1),
            TextEditorField::new('description')->setColumns(12),

            FormField::addFieldset('Database', 'fas fa-database text-info'),
            IdField::new('id')
                ->setColumns(1)
                ->hideOnIndex()
                ->setDisabled(),
            SlugField::new('slug')
                ->setTargetFieldName('name')
                ->setColumns(2)
                ->hideOnIndex(),
            DateTimeField::new('createdAt')
                ->setColumns(2)
                ->setDisabled()
                ->hideOnIndex(),
            DateTimeField::new('updatedAt')
                ->setColumns(2)
                ->setDisabled()
                ->hideOnIndex(),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add('icon')
            ->add('color')
            ->add('description')
            ->add('parent')
        ;
    }
}
