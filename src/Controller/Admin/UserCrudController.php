<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\{Crud, Filters};
use EasyCorp\Bundle\EasyAdminBundle\Field\{
    AvatarField,
    BooleanField,
    ChoiceField,
    ColorField,
    DateTimeField,
    EmailField,
    FormField,
    IdField,
    TelephoneField,
    TextEditorField,
    TextField
};

class UserCrudController extends AppAbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('User')
            ->setEntityLabelInPlural('Users')
            ->setDefaultSort(['pseudo' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addFieldset('Authentication', 'fas fa-user-lock text-info'),
            AvatarField::new('email', 'Avatar')
                ->setIsGravatarEmail()
                ->setHeight($pageName === Crud::PAGE_DETAIL ? 'xl' : 'lg'),
            TextField::new('pseudo')->setColumns(1),
            EmailField::new('email')->setColumns(2),
            BooleanField::new('isVerified')->setColumns(1),
            ChoiceField::new('roles')
                ->setChoices(User::ROLES)
                ->renderAsBadges([
                    'ROLE_CLOSE' => 'danger',
                    'ROLE_USER' => 'info',
                    'ROLE_APP' => 'success',
                    'ROLE_ADMIN' => 'warning',
                ])
                ->allowMultipleChoices()
                ->setColumns(3),

            FormField::addFieldset('Qualification', 'fas fa-info text-info'),
            TextField::new('firstname')->setColumns(2),
            TextField::new('lastname')->setColumns(2),
            TelephoneField::new('phone')->setColumns(2),
            ColorField::new('color')->setColumns(1),
            TextEditorField::new('description')->setColumns(12),

            FormField::addFieldset('Database', 'fas fa-database text-info'),
            IdField::new('id')
                ->setColumns(1)
                ->hideOnIndex()
                ->setDisabled(),
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
            ->add('id')
            ->add('pseudo')
            ->add('email')
            ->add('firstname')
            ->add('lastname')
            ->add('description')
            ->add('roles')
        ;
    }
}
