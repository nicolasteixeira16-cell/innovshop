<?php

namespace App\Controller\Vendeur;

use App\Entity\Produit;
use App\Form\ProduitVendeurFormType;
use App\Repository\ProduitRepository;
use App\Security\Voter\ProduitVoter;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/vendeur/produits')]
class ProduitVendeurController extends AbstractController
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')] private string $projectDir,
        private SluggerInterface $slugger,
    ) {
    }

    #[Route('', name: 'app_vendeur_produit_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('vendeur/produit/index.html.twig', [
            'produits' => $produitRepository->findByVendeur($this->getUser()),
        ]);
    }

    #[Route('/nouveau', name: 'app_vendeur_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        return $this->traiter($request, new Produit(), true, $em);
    }

    #[Route('/{id}/modifier', name: 'app_vendeur_produit_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Produit $produit, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted(ProduitVoter::MODIFIER, $produit);

        return $this->traiter($request, $produit, false, $em);
    }

    #[Route('/{id}/supprimer', name: 'app_vendeur_produit_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Produit $produit, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted(ProduitVoter::MODIFIER, $produit);

        if (!$this->isCsrfTokenValid('supprimer_produit_' . $produit->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $image = $produit->getImage();

        try {
            $em->remove($produit);
            $em->flush();
        } catch (ForeignKeyConstraintViolationException) {
            $this->addFlash('danger', 'Ce produit figure dans des commandes : il ne peut pas être supprimé.');

            return $this->redirectToRoute('app_vendeur_produit_index');
        }

        $this->supprimerImage($image);
        $this->addFlash('success', 'Le produit a été supprimé.');

        return $this->redirectToRoute('app_vendeur_produit_index');
    }

    private function traiter(Request $request, Produit $produit, bool $creation, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProduitVendeurFormType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $fichier */
            $fichier = $form->get('imageFichier')->getData();

            if ($fichier !== null) {
                $ancienne = $produit->getImage();
                $produit->setImage($this->enregistrerImage($fichier));
                $this->supprimerImage($ancienne);
            }

            if ($creation) {
                $produit->setVendeur($this->getUser());
                $produit->setEnVedette(false);
                $em->persist($produit);
            }

            // Toute création ou modification doit être validée par un administrateur.
            $produit->setStatut(Produit::STATUT_EN_ATTENTE);
            $em->flush();

            $this->addFlash('success', 'Produit enregistré. Il sera visible dans le catalogue après validation par un administrateur.');

            return $this->redirectToRoute('app_vendeur_produit_index');
        }

        return $this->render('vendeur/produit/form.html.twig', [
            'form' => $form,
            'produit' => $produit,
            'creation' => $creation,
        ]);
    }

    private function enregistrerImage(UploadedFile $fichier): string
    {
        $base = $this->slugger->slug(pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME))->lower();
        $nom = $base . '-' . bin2hex(random_bytes(6)) . '.' . ($fichier->guessExtension() ?? 'jpg');
        $fichier->move($this->projectDir . '/public/uploads/produits', $nom);

        return $nom;
    }

    private function supprimerImage(?string $nom): void
    {
        if ($nom === null || $nom === '') {
            return;
        }

        $chemin = $this->projectDir . '/public/uploads/produits/' . basename($nom);
        if (is_file($chemin)) {
            unlink($chemin);
        }
    }
}