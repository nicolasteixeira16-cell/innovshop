<?php

namespace App\Tests\Controller;

use App\Entity\Commande;
use App\Tests\FonctionnelTestCase;

class CommandeAccesTest extends FonctionnelTestCase
{
    public function testProprietaireVoitSaCommande(): void
    {
        $proprietaire = $this->creerUtilisateur('proprio@test.local');
        $commande = $this->creerCommande($proprietaire);

        $this->client->loginUser($proprietaire);
        $this->client->request('GET', '/mon-compte/commandes/'.$commande->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Casque Test');
        $this->assertSelectorTextContains('body', 'En attente');
    }

    public function testProprietaireTelechargeSaFacturePdf(): void
    {
        $proprietaire = $this->creerUtilisateur('proprio@test.local');
        $commande = $this->creerCommande($proprietaire);

        $this->client->loginUser($proprietaire);
        $this->client->request('GET', '/mon-compte/commandes/'.$commande->getId().'/facture');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/pdf');
    }

    public function testUnAutreClientNeVoitPasLaCommande(): void
    {
        $proprietaire = $this->creerUtilisateur('proprio@test.local');
        $intrus = $this->creerUtilisateur('intrus@test.local');
        $commande = $this->creerCommande($proprietaire);

        $this->client->loginUser($intrus);
        $this->client->request('GET', '/mon-compte/commandes/'.$commande->getId());

        $this->assertResponseStatusCodeSame(403);
    }

    public function testUnAutreClientNeTelechargePasLaFacture(): void
    {
        $proprietaire = $this->creerUtilisateur('proprio@test.local');
        $intrus = $this->creerUtilisateur('intrus@test.local');
        $commande = $this->creerCommande($proprietaire);

        $this->client->loginUser($intrus);
        $this->client->request('GET', '/mon-compte/commandes/'.$commande->getId().'/facture');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCommandeInexistanteRenvoie404(): void
    {
        $this->client->loginUser($this->creerUtilisateur('client@test.local'));

        $this->client->request('GET', '/mon-compte/commandes/999999');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testPasDeFacturePourUneCommandeAnnulee(): void
    {
        $proprietaire = $this->creerUtilisateur('proprio@test.local');
        $commande = $this->creerCommande($proprietaire, Commande::STATUT_ANNULEE);

        $this->client->loginUser($proprietaire);
        $this->client->request('GET', '/mon-compte/commandes/'.$commande->getId().'/facture');

        $this->assertResponseStatusCodeSame(404);
    }
}
