<?php

namespace App\Tests\Controller;

use App\Entity\Categorie;
use App\Entity\Produit;
use App\Tests\FonctionnelTestCase;

class CatalogueTest extends FonctionnelTestCase
{
    public function testLeCatalogueAfficheUnProduit(): void
    {
        $categorie = (new Categorie())->setNom('Catégorie Test')->setSlug('categorie-test');
        $this->em->persist($categorie);

        $produit = (new Produit())
            ->setNom('Produit Zorglub')
            ->setDescription('Description de test')
            ->setPrix('42.00')
            ->setEnVedette(false)
            ->setCategorie($categorie);
        $this->em->persist($produit);
        $this->em->flush();

        $this->client->request('GET', '/produits');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Produit Zorglub');
    }
}