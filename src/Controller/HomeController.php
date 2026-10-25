<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProduitRepository $produitRepository): Response
    {
        $enVedette = $produitRepository->findEnVedette(4);
        $idsEnVedette = array_map(static fn (Produit $produit): ?int => $produit->getId(), $enVedette);

        return $this->render('home/index.html.twig', [
            'produitsEnVedette' => $enVedette,
            'prototypes' => $produitRepository->findPrototypes(4),
            'nouveautes' => $produitRepository->findNouveautes(4, $idsEnVedette),
        ]);
    }
}
