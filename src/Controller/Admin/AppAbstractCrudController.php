<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\{Action, Actions, Crud};
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

abstract class AppAbstractCrudController extends AbstractCrudController
{
    public function configureActions(Actions $actions): Actions
    {
        return $actions

            // Page INDEX
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->setIcon('fas fa-eye text-info')
                    ->setLabel(false);
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action
                    ->setIcon('fas fa-edit text-primary')
                    ->setLabel(false);
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fas fa-trash text-danger')
                    ->setLabel(false);
            })
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action
                    ->setIcon('fas fa-plus-circle')
                    ->setCssClass('btn btn-success');
            })

            // Page EDIT
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->update(Crud::PAGE_EDIT, Action::DETAIL, function (Action $action) {
                return $action
                    ->setIcon('fas fa-eye')
                    ->setCssClass('btn btn-info');
            })
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->update(Crud::PAGE_EDIT, Action::INDEX, function (Action $action) {
                return $action
                    ->setIcon('fas fa-list')
                    ->setCssClass('btn btn-muted');
            })

            // Page DETAIL
            ->update(Crud::PAGE_DETAIL, Action::EDIT, function (Action $action) {
                return $action->setIcon('fas fa-edit');
            })
            ->update(Crud::PAGE_DETAIL, Action::INDEX, function (Action $action) {
                return $action->setIcon('fas fa-list');
            })

            // Page NEW
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action
                    ->setIcon('fas fa-check')
                    ->setCssClass('btn btn-success');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
                return $action
                    ->setIcon('fas fa-redo');
            })
        ;
    }
}
