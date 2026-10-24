<?php

namespace App\Controller\Admin;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class UtilisateurCrudController extends AbstractCrudController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Utilisateur::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setDefaultSort(['dateCreation' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        // Un utilisateur qui a des commandes ne peut pas être supprimé
        // (clé étrangère) : on désactive la suppression pour garder l'historique.
        return $actions->disable(Action::DELETE, Action::BATCH_DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('prenom', 'Prénom'),
            TextField::new('nom', 'Nom'),
            EmailField::new('email', 'Email'),
            ChoiceField::new('roles', 'Rôles')
                ->setChoices([
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                ])
                ->allowMultipleChoices()
                ->renderExpanded()
                ->renderAsBadges(),
            // Mot de passe : uniquement à la création d'un compte.
            TextField::new('password', 'Mot de passe')
                ->setFormType(PasswordType::class)
                ->setFormTypeOptions([
                    'constraints' => [
                        new NotBlank(message: 'Le mot de passe est obligatoire.'),
                        new Length(min: 8, minMessage: 'Au moins {{ limit }} caractères.'),
                        new PasswordStrength(minScore: PasswordStrength::STRENGTH_MEDIUM),
                    ],
                ])
                ->onlyWhenCreating(),
            DateTimeField::new('dateCreation', 'Inscrit le')->hideOnForm(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        // On hache le mot de passe saisi avant l'enregistrement.
        if ($entityInstance instanceof Utilisateur) {
            $entityInstance->setPassword(
                $this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPassword())
            );
        }

        parent::persistEntity($entityManager, $entityInstance);
    }
}
