<?php

namespace App\Tests\Controller;

use App\Entity\Produit;
use App\Tests\FonctionnelTestCase;

class PrototypeTest extends FonctionnelTestCase
{
    /** Récupère un jeton CSRF du panier sur la fiche d'un produit ordinaire. */
    private function recupererJeton(Produit $produitOrdinaire): string
    {
        $crawler = $this->client->request('GET', '/produits/'.$produitOrdinaire->getId());
        $champ = $crawler->filter('input[name="_token"]');
        $this->assertGreaterThan(0, $champ->count(), 'Aucun jeton CSRF trouvé sur la fiche produit.');

        return (string) $champ->first()->attr('value');
    }

    public function testUnClientSansDroitNePeutPasAjouterUnPrototype(): void
    {
        $ordinaire = $this->creerProduit('Produit ordinaire');
        $prototype = $this->creerPrototype('Prototype secret');
        $client = $this->creerUtilisateur('client@test.fr');

        $this->client->loginUser($client);
        $jeton = $this->recupererJeton($ordinaire);

        $this->client->request('POST', '/panier/ajouter/'.$prototype->getId(), ['_token' => $jeton]);
        $this->assertResponseStatusCodeSame(403);
    }

    public function testUnVisiteurNePeutPasAjouterUnPrototype(): void
    {
        $ordinaire = $this->creerProduit('Produit ordinaire');
        $prototype = $this->creerPrototype('Prototype secret');

        $jeton = $this->recupererJeton($ordinaire);

        $this->client->request('POST', '/panier/ajouter/'.$prototype->getId(), ['_token' => $jeton]);
        $this->assertResponseRedirects();
        $this->assertStringContainsString('/login', (string) $this->client->getResponse()->headers->get('Location'));
    }

    public function testUnTesteurPeutAjouterUnPrototype(): void
    {
        $ordinaire = $this->creerProduit('Produit ordinaire');
        $prototype = $this->creerPrototype('Prototype test');
        $testeur = $this->creerUtilisateur('testeur@test.fr', ['ROLE_TESTEUR']);

        $this->client->loginUser($testeur);
        $jeton = $this->recupererJeton($ordinaire);

        $this->client->request('POST', '/panier/ajouter/'.$prototype->getId(), ['_token' => $jeton]);
        $this->assertResponseRedirects();
    }

    public function testUnAdministrateurPeutAjouterUnPrototype(): void
    {
        $ordinaire = $this->creerProduit('Produit ordinaire');
        $prototype = $this->creerPrototype('Prototype admin');
        $admin = $this->creerUtilisateur('admin@test.fr', ['ROLE_ADMIN']);

        $this->client->loginUser($admin);
        $jeton = $this->recupererJeton($ordinaire);

        $this->client->request('POST', '/panier/ajouter/'.$prototype->getId(), ['_token' => $jeton]);
        $this->assertResponseRedirects();
    }

    public function testUnProduitOrdinaireResteAjoutablePourUnClientSimple(): void
    {
        $ordinaire = $this->creerProduit('Produit ordinaire');
        $client = $this->creerUtilisateur('client@test.fr');

        $this->client->loginUser($client);
        $jeton = $this->recupererJeton($ordinaire);

        $this->client->request('POST', '/panier/ajouter/'.$ordinaire->getId(), ['_token' => $jeton]);
        $this->assertResponseRedirects();
    }

    public function testLaFicheDUnPrototypeEstReserveeAuxTesteurs(): void
    {
        $prototype = $this->creerPrototype('Prototype fiche');
        $client = $this->creerUtilisateur('client@test.fr');

        $this->client->loginUser($client);
        $this->client->request('GET', '/produits/'.$prototype->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'réservé aux testeurs validés');
        $this->assertSelectorNotExists('form[data-cart-form]');
    }

    public function testLaFicheDUnPrototypePropoSeLeBoutonAuTesteur(): void
    {
        $prototype = $this->creerPrototype('Prototype fiche');
        $testeur = $this->creerUtilisateur('testeur@test.fr', ['ROLE_TESTEUR']);

        $this->client->loginUser($testeur);
        $this->client->request('GET', '/produits/'.$prototype->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[data-cart-form]');
    }
}
