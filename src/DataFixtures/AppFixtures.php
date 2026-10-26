<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use App\Entity\Produit;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Données de démonstration (environnement de développement uniquement :
 * le bundle des fixtures n'est pas installé en production).
 */
class AppFixtures extends Fixture
{
    public const MOT_DE_PASSE_DEMO = 'Demo-InnovShop-2026!';

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Catégories
        $categories = [];
        foreach ([
            'prototypes' => 'Prototypes',
            'ordinateurs' => 'Ordinateurs',
            'vetements' => 'Vêtements',
            'accessoires' => 'Accessoires',
        ] as $slug => $nom) {
            $categorie = (new Categorie())->setNom($nom)->setSlug($slug);
            $manager->persist($categorie);
            $categories[$slug] = $categorie;
        }

        // Comptes de démonstration
        $admin = $this->creerUtilisateur('admin@innovshop.local', 'Alice', 'Admin', ['ROLE_ADMIN']);
        $client = $this->creerUtilisateur('client@innovshop.local', 'Camille', 'Client', []);
        $vendeur = $this->creerUtilisateur('vendeur@innovshop.local', 'Victor', 'Vendeur', ['ROLE_VENDEUR']);
        $vendeur->setNomBoutique('Atelier Proto');
        $testeur = $this->creerUtilisateur('testeur@innovshop.local', 'Théo', 'Testeur', ['ROLE_TESTEUR']);
        foreach ([$admin, $client, $vendeur, $testeur] as $utilisateur) {
            $manager->persist($utilisateur);
        }

        // Produits publiés (images copiées dans public/uploads/produits)
        $produits = [
            ['Full IA Navigator', '210.00', 'prototypes', 'navigator.jpg', false,
                "GPS avec IA satellitaire intégrée permettant l'optimisation des trajets."],
            ['Immersive Computer', '598.00', 'ordinateurs', 'ordinateur-immersif.jpg', true,
                'Ordinateur à écran 3D en mobilité sur lunettes immersives.'],
            ['Combinaison Aérodrone', '2499.00', 'vetements', 'combinaison-aerodrone.jpg', true,
                'Combinaison wingsuit avec drones intégrés permettant le premier vol humain en toute sécurité.'],
            ['Lunettes 3D Transform', '399.00', 'accessoires', 'lunettes-3d.jpg', false,
                "Transforme toute sélection d'objet 2D en 3D."],
        ];

        $filesystem = new Filesystem();
        $dossierUploads = $this->projectDir.'/public/uploads/produits';
        $filesystem->mkdir($dossierUploads);

        foreach ($produits as [$nom, $prix, $slugCategorie, $image, $enVedette, $description]) {
            $filesystem->copy(
                $this->projectDir.'/fixtures/images/'.$image,
                $dossierUploads.'/'.$image,
                true,
            );

            $produit = (new Produit())
                ->setNom($nom)
                ->setPrix($prix)
                ->setDescription('<p>'.$description.'</p>')
                ->setImage($image)
                ->setEnVedette($enVedette)
                ->setCategorie($categories[$slugCategorie])
                ->setStatut(Produit::STATUT_PUBLIE);
            $manager->persist($produit);
        }

        // Produit proposé par le vendeur : en attente de validation (démo de la modération)
        $enAttente = (new Produit())
            ->setNom('Drone de poche Pocket Fly')
            ->setPrix('149.00')
            ->setDescription('<p>Mini drone pliable, prototype soumis par un vendeur et en attente de validation.</p>')
            ->setEnVedette(false)
            ->setCategorie($categories['prototypes'])
            ->setVendeur($vendeur)
            ->setStatut(Produit::STATUT_EN_ATTENTE);
        $manager->persist($enAttente);

        $manager->flush();
    }

    /** @param string[] $roles */
    private function creerUtilisateur(string $email, string $prenom, string $nom, array $roles): Utilisateur
    {
        $utilisateur = new Utilisateur();
        $utilisateur->setEmail($email);
        $utilisateur->setPrenom($prenom);
        $utilisateur->setNom($nom);
        $utilisateur->setRoles($roles);
        $utilisateur->setPassword($this->passwordHasher->hashPassword($utilisateur, self::MOT_DE_PASSE_DEMO));

        return $utilisateur;
    }
}
