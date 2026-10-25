<?php

namespace App\Controller\Admin;

use App\Entity\Produit;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;

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


    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('statut', 'Statut')->setChoices(array_flip(Produit::STATUTS)));
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
                        ChoiceField::new('statut', 'Statut')
                ->setChoices(array_flip(Produit::STATUTS))
                ->renderAsBadges([
                    Produit::STATUT_EN_ATTENTE => 'warning',
                    Produit::STATUT_PUBLIE => 'success',
                    Produit::STATUT_REFUSE => 'danger',
                ]),
            AssociationField::new('vendeur', 'Vendeur')->setRequired(false),
            DateTimeField::new('dateCreation', 'Créé le')->hideOnForm(),
        ];
    }
}
