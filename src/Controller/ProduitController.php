<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProduitController extends AbstractController
{
    private const TRIS = ['recent', 'prix_asc', 'prix_desc', 'nom'];

    #[Route('/produits', name: 'app_produit_index')]
    public function index(
        Request $request,
        ProduitRepository $produitRepository,
        CategorieRepository $categorieRepository,
    ): Response {
        $q = trim((string) $request->query->get('q', ''));
        $categorie = (string) $request->query->get('categorie', '');
        $tri = (string) $request->query->get('tri', 'recent');
        if (!in_array($tri, self::TRIS, true)) {
            $tri = 'recent';
        }

        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->rechercher($q, $categorie, $tri),
            'categories' => $categorieRepository->findBy([], ['nom' => 'ASC']),
            'q' => $q,
            'categorieActive' => $categorie,
            'tri' => $tri,
        ]);
    }

    #[Route('/produits/{id}', name: 'app_produit_show', requirements: ['id' => '\d+'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }
}