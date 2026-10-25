<?php

namespace App\Command;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:creer-admin',
    description: 'Crée un compte administrateur (e-mail et mot de passe demandés au clavier).',
)]
class CreerAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = trim((string) $io->ask('Adresse e-mail de l\'administrateur'));
        if (false === filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            $io->error('Adresse e-mail invalide.');

            return Command::FAILURE;
        }

        $existant = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);
        if (null !== $existant) {
            $io->error('Un compte existe déjà avec cette adresse e-mail.');

            return Command::FAILURE;
        }

        $prenom = trim((string) $io->ask('Prénom', 'Admin'));
        $nom = trim((string) $io->ask('Nom', 'InnovShop'));
        $motDePasse = (string) $io->askHidden('Mot de passe (12 caractères minimum, non affiché)');

        if (mb_strlen($motDePasse) < 12) {
            $io->error('Le mot de passe doit contenir au moins 12 caractères.');

            return Command::FAILURE;
        }

        $admin = new Utilisateur();
        $admin->setEmail($email);
        $admin->setPrenom($prenom);
        $admin->setNom($nom);
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, $motDePasse));

        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $io->success(sprintf('Administrateur %s créé.', $email));

        return Command::SUCCESS;
    }
}
