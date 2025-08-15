<?php

namespace App\Controller\Admin;

use App\Entity\Log;
use App\Form\JsonCodeEditorType;
use EasyCorp\Bundle\EasyAdminBundle\Config\{Crud, Filters};
use EasyCorp\Bundle\EasyAdminBundle\Field\{
    AssociationField,
    ChoiceField,
    CodeEditorField,
    DateTimeField,
    FormField,
    IdField,
    TextareaField,
    TextField,
};

class LogCrudController extends AppAbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Log::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Log')
            ->setEntityLabelInPlural('Logs')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addFieldset('Identification', 'fas fa-user-lock text-info'),
            TextareaField::new('message')->setColumns(12),
            DateTimeField::new('createdAt')
                ->setColumns(2)
                ->setDisabled(),
            AssociationField::new('user')->setColumns(2),

            FormField::addFieldset('Datas', 'fas fa-info text-info'),
            CodeEditorField::new('context')
                ->setColumns(6)
                ->setFormType(JsonCodeEditorType::class)
                ->onlyOnForms(),
            CodeEditorField::new('extra')
                ->setColumns(6)
                ->setFormType(JsonCodeEditorType::class)
                ->onlyOnForms(),

            FormField::addFieldset('Qualification', 'fas fa-info text-info'),
            ChoiceField::new('level')
                ->setChoices(array_flip(Log::LEVELS))
                ->renderAsBadges([
                    100 => 'info',
                    200 => 'success',
                    250 => 'warning',
                    300 => 'warning',
                    400 => 'danger',
                    500 => 'danger',
                    550 => 'danger',
                    600 => 'danger',
                ])
                ->setColumns(2),
            TextField::new('levelName')->setColumns(2)->hideOnIndex()->setDisabled(),
            TextField::new('channel')->setColumns(1)->hideOnIndex()->setDisabled(),

            FormField::addFieldset('Database', 'fas fa-database text-info'),
            IdField::new('id')
                ->setColumns(1)
                ->hideOnIndex()
                ->setDisabled(),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('id')
            ->add('message')
            ->add('user')
            ->add('createdAt')
            ->add('level')
            ->add('levelName')
            ->add('channel')
            ->add('context')
            ->add('extra')
        ;
    }
}
