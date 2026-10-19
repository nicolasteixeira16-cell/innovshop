<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Controller\Admin\ProduitCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
{
    $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

    return $this->redirect(
        $adminUrlGenerator->setController(ProduitCrudController::class)->generateUrl()
    );
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
        // yield MenuItem::linkTo(SomeCrudController::class, 'The Label', 'fas fa-list');
    }
}
