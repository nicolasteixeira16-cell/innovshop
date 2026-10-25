<?php

namespace App\Controller\Admin;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use App\Repository\UtilisateurRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private ProduitRepository $produitRepository,
        private CommandeRepository $commandeRepository,
        private UtilisateurRepository $utilisateurRepository,
    ) {
    }

    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'nbProduits' => $this->produitRepository->count([]),
            'nbCommandes' => $this->commandeRepository->count([]),
            'nbCommandesEnAttente' => $this->commandeRepository->count(['statut' => Commande::STATUT_EN_ATTENTE]),
            'nbUtilisateurs' => $this->utilisateurRepository->count([]),
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('InnovShop - Administration');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');
        yield MenuItem::linkTo(CategorieCrudController::class, 'Catégories', 'fas fa-tags');
        yield MenuItem::linkTo(ProduitCrudController::class, 'Produits', 'fas fa-box');
        yield MenuItem::linkTo(CommandeCrudController::class, 'Commandes', 'fas fa-receipt');
        yield MenuItem::linkTo(UtilisateurCrudController::class, 'Utilisateurs', 'fas fa-users');
        yield MenuItem::linkTo(CandidatureTesteurCrudController::class, 'Candidatures testeurs', 'fas fa-user-check');
        // yield MenuItem::linkTo(SomeCrudController::class, 'The Label', 'fas fa-list');
    }
}
