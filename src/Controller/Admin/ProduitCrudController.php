<?php

namespace App\Controller\Admin;

use App\Entity\Produit;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProduitCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Produit::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Produit')
            ->setEntityLabelInPlural('Produits')
            ->setDefaultSort(['dateCreation' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('nom', 'Nom'),
            AssociationField::new('categorie', 'Catégorie'),
            TextEditorField::new('description', 'Description')->hideOnIndex(),
            MoneyField::new('prix', 'Prix')
                ->setCurrency('EUR')
                ->setStoredAsCents(false),
            ImageField::new('image', 'Image')
                ->setBasePath('uploads/produits')
                ->setUploadDir('public/uploads/produits')
                ->setUploadedFileNamePattern('[slug]-[randomhash].[extension]')
                ->setRequired(false),
            BooleanField::new('enVedette', 'En vedette'),
            DateTimeField::new('dateCreation', 'Créé le')->hideOnForm(),
        ];
    }
}
