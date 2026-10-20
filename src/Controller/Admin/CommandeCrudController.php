<?php

namespace App\Controller\Admin;

use App\Entity\Commande;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class CommandeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Commande::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Commande')
            ->setEntityLabelInPlural('Commandes')
            ->setDefaultSort(['dateCreation' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // Les commandes sont créées par les clients, pas par l'admin,
            // et on les conserve pour l'historique : pas de création ni de suppression.
            ->disable(Action::NEW, Action::DELETE, Action::BATCH_DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'N°')->hideOnForm(),
            AssociationField::new('utilisateur', 'Client')->hideOnForm(),
            MoneyField::new('total', 'Total')
                ->setCurrency('EUR')
                ->setStoredAsCents(false)
                ->hideOnForm(),
            DateTimeField::new('dateCreation', 'Date')->hideOnForm(),
            ChoiceField::new('statut', 'Statut')
                ->setChoices([
                    'En attente' => Commande::STATUT_EN_ATTENTE,
                    'Validée' => Commande::STATUT_VALIDEE,
                    'Expédiée' => Commande::STATUT_EXPEDIEE,
                    'Annulée' => Commande::STATUT_ANNULEE,
                ])
                ->renderAsBadges([
                    Commande::STATUT_EN_ATTENTE => 'warning',
                    Commande::STATUT_VALIDEE => 'info',
                    Commande::STATUT_EXPEDIEE => 'success',
                    Commande::STATUT_ANNULEE => 'danger',
                ]),
                TextareaField::new('adresseLivraison', 'Adresse de livraison')->onlyOnDetail(),
        ];
    }
}