<?php

namespace App\Controller\Admin;

use App\Entity\CandidatureTesteur;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CandidatureTesteurCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CandidatureTesteur::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Candidature testeur')
            ->setEntityLabelInPlural('Candidatures testeurs')
            ->setDefaultSort(['dateCreation' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnIndex();
        yield TextField::new('nom', 'Nom');
        yield EmailField::new('email', 'E-mail');
        yield TextareaField::new('motivation', 'Motivation')->hideOnIndex();
        yield DateTimeField::new('dateCreation', 'Reçue le');
    }
}
