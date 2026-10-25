<?php

namespace App\Tests\Controller;

use App\Entity\Produit;
use App\Entity\Utilisateur;
use App\Tests\FonctionnelTestCase;

class MarketplaceTest extends FonctionnelTestCase
{
    // --- Visibilité publique -------------------------------------------------

    public function testUnProduitEnAttenteNEstPasDansLeCatalogue(): void
    {
        $this->creerProduit('Produit Visible');
        $this->creerProduit('Produit Secret', Produit::STATUT_EN_ATTENTE);

        $this->client->request('GET', '/produits');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Produit Visible');
        $this->assertSelectorTextNotContains('body', 'Produit Secret');
    }

    public function testUnProduitRefuseNEstPasDansLeCatalogue(): void
    {
        $this->creerProduit('Produit Refuse', Produit::STATUT_REFUSE);

        $this->client->request('GET', '/produits');

        $this->assertSelectorTextNotContains('body', 'Produit Refuse');
    }

    public function testUnProduitEnAttenteNEstPasSurLAccueil(): void
    {
        $this->creerProduit('Produit Visible');
        $this->creerProduit('Produit Secret', Produit::STATUT_EN_ATTENTE);

        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Produit Visible');
        $this->assertSelectorTextNotContains('body', 'Produit Secret');
    }

    public function testLaFicheDUnProduitEnAttenteEstIntrouvablePourUnVisiteur(): void
    {
        // Produit de la boutique (sans vendeur) puis produit d'un vendeur.
        $sansVendeur = $this->creerProduit('Sans vendeur', Produit::STATUT_EN_ATTENTE);
        $vendeur = $this->creerUtilisateur('vendeur@test.fr', ['ROLE_VENDEUR']);
        $avecVendeur = $this->creerProduit('Avec vendeur', Produit::STATUT_EN_ATTENTE, $vendeur);

        $this->client->request('GET', '/produits/' . $sansVendeur->getId());
        $this->assertResponseStatusCodeSame(404);

        $this->client->request('GET', '/produits/' . $avecVendeur->getId());
        $this->assertResponseStatusCodeSame(404);
    }

    public function testLeVendeurVoitLaFicheDeSonProduitEnAttente(): void
    {
        $vendeur = $this->creerUtilisateur('vendeur@test.fr', ['ROLE_VENDEUR']);
        $produit = $this->creerProduit('Mon produit', Produit::STATUT_EN_ATTENTE, $vendeur);

        $this->client->loginUser($vendeur);
        $this->client->request('GET', '/produits/' . $produit->getId());

        $this->assertResponseIsSuccessful();
    }

    public function testUnAutreClientNeVoitPasLaFicheDUnProduitEnAttente(): void
    {
        $vendeur = $this->creerUtilisateur('vendeur@test.fr', ['ROLE_VENDEUR']);
        $produit = $this->creerProduit('Mon produit', Produit::STATUT_EN_ATTENTE, $vendeur);
        $client = $this->creerUtilisateur('client@test.fr');

        $this->client->loginUser($client);
        $this->client->request('GET', '/produits/' . $produit->getId());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUnProduitNonPublieNeSAjoutePasAuPanier(): void
    {
        $publie = $this->creerProduit('Produit Visible');
        $enAttente = $this->creerProduit('Produit Secret', Produit::STATUT_EN_ATTENTE);

        $crawler = $this->client->request('GET', '/produits/' . $publie->getId());
        $champ = $crawler->filter('input[name="_token"]');
        $this->assertGreaterThan(0, $champ->count(), 'Aucun jeton CSRF trouvé sur la fiche produit.');
        $jeton = (string) $champ->first()->attr('value');

        // Témoin : le produit publié s'ajoute bien (redirection vers le panier).
        $this->client->request('POST', '/panier/ajouter/' . $publie->getId(), ['_token' => $jeton]);
        $this->assertResponseRedirects();

        // Le produit en attente est refusé.
        $this->client->request('POST', '/panier/ajouter/' . $enAttente->getId(), ['_token' => $jeton]);
        $this->assertResponseStatusCodeSame(404);
    }

    // --- Accès à l'espace vendeur -------------------------------------------

    public function testUnVisiteurEstRedirigeVersLaConnexion(): void
    {
        $this->client->request('GET', '/vendeur/produits');

        $this->assertResponseRedirects();
        $this->assertStringContainsString('/login', (string) $this->client->getResponse()->headers->get('Location'));
    }

    public function testUnSimpleClientNAccedePasALEspaceVendeur(): void
    {
        $client = $this->creerUtilisateur('client@test.fr');

        $this->client->loginUser($client);
        $this->client->request('GET', '/vendeur/produits');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testUnVendeurAccedeASonEspace(): void
    {
        $vendeur = $this->creerUtilisateur('vendeur@test.fr', ['ROLE_VENDEUR']);

        $this->client->loginUser($vendeur);
        $this->client->request('GET', '/vendeur/produits');

        $this->assertResponseIsSuccessful();
    }

    public function testLaListeDuVendeurNeContientQueSesProduits(): void
    {
        $a = $this->creerUtilisateur('a@test.fr', ['ROLE_VENDEUR']);
        $b = $this->creerUtilisateur('b@test.fr', ['ROLE_VENDEUR']);
        $this->creerProduit('Produit de A', Produit::STATUT_PUBLIE, $a);
        $this->creerProduit('Produit de B', Produit::STATUT_PUBLIE, $b);

        $this->client->loginUser($a);
        $this->client->request('GET', '/vendeur/produits');

        $this->assertSelectorTextContains('body', 'Produit de A');
        $this->assertSelectorTextNotContains('body', 'Produit de B');
    }

    // --- Propriété des produits (Voter) -------------------------------------

    public function testUnVendeurModifieSonProduit(): void
    {
        $vendeur = $this->creerUtilisateur('vendeur@test.fr', ['ROLE_VENDEUR']);
        $produit = $this->creerProduit('Mon produit', Produit::STATUT_PUBLIE, $vendeur);

        $this->client->loginUser($vendeur);
        $this->client->request('GET', '/vendeur/produits/' . $produit->getId() . '/modifier');

        $this->assertResponseIsSuccessful();
    }

    public function testUnVendeurNeModifiePasLeProduitDUnAutre(): void
    {
        $proprietaire = $this->creerUtilisateur('proprietaire@test.fr', ['ROLE_VENDEUR']);
        $intrus = $this->creerUtilisateur('intrus@test.fr', ['ROLE_VENDEUR']);
        $produit = $this->creerProduit('Produit protégé', Produit::STATUT_PUBLIE, $proprietaire);
        $id = $produit->getId();

        $this->client->loginUser($intrus);

        $this->client->request('GET', '/vendeur/produits/' . $id . '/modifier');
        $this->assertResponseStatusCodeSame(403);

        $this->client->request('POST', '/vendeur/produits/' . $id . '/supprimer');
        $this->assertResponseStatusCodeSame(403);

        $this->em->clear();
        $this->assertNotNull($this->em->getRepository(Produit::class)->find($id));
    }

    // --- Demande vendeur -----------------------------------------------------

    public function testLaDemandeVendeurEstEnregistree(): void
    {
        $client = $this->creerUtilisateur('client@test.fr');

        $this->client->loginUser($client);
        $crawler = $this->client->request('GET', '/devenir-vendeur');
        $this->assertResponseIsSuccessful();

        $formulaire = $crawler->selectButton('Envoyer ma demande')->form();
        $formulaire['devenir_vendeur_form[nomBoutique]'] = 'Ma Boutique';
        $this->client->submit($formulaire);

        $this->assertResponseRedirects('/devenir-vendeur');

        $this->em->clear();
        $utilisateur = $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => 'client@test.fr']);
        $this->assertTrue($utilisateur->isDemandeVendeur());
        $this->assertSame('Ma Boutique', $utilisateur->getNomBoutique());
        $this->assertNotContains('ROLE_VENDEUR', $utilisateur->getRoles());
    }
}