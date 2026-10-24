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
use App\Service\CommandeMailer;
use Doctrine\ORM\EntityManagerInterface;

class CommandeCrudController extends AbstractCrudController
{
    public function __construct(private CommandeMailer $commandeMailer)
    {
    }

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

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Statut tel qu'il est en base, avant l'enregistrement.
        $ancienStatut = null;
        if ($entityInstance instanceof Commande) {
            $donneesOriginales = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);
            $ancienStatut = $donneesOriginales['statut'] ?? null;
        }

        parent::updateEntity($entityManager, $entityInstance);

        // Email au client seulement si le statut a réellement changé.
        if ($entityInstance instanceof Commande
            && $ancienStatut !== null
            && $ancienStatut !== $entityInstance->getStatut()
        ) {
            if ($this->commandeMailer->envoyerChangementStatut($entityInstance)) {
                $this->addFlash('success', 'Un email a été envoyé au client pour l\'informer du changement de statut.');
            } else {
                $this->addFlash('warning', 'Le statut a été modifié, mais l\'email au client n\'a pas pu être envoyé.');
            }
        }
    }
}
