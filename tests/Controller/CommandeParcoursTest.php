<?php

namespace App\Tests\Controller;

use App\Tests\FonctionnelTestCase;

class CommandeParcoursTest extends FonctionnelTestCase
{
    private function remplirPanier(): void
    {
        $produit = $this->creerProduit('Produit du parcours');
        $this->client->loginUser($this->creerUtilisateur('acheteur@test.fr'));

        $crawler = $this->client->request('GET', '/produits/'.$produit->getId());
        $jeton = (string) $crawler->filter('input[name="_token"]')->first()->attr('value');
        $this->client->request('POST', '/panier/ajouter/'.$produit->getId(), ['_token' => $jeton]);
    }

    private function renseignerLivraison(): void
    {
        $this->client->request('GET', '/commande/livraison');
        $this->client->submitForm('Continuer vers le paiement', [
            'livraison_form[rue]' => '1 rue des Tests',
            'livraison_form[codePostal]' => '75001',
            'livraison_form[ville]' => 'Paris',
            'livraison_form[pays]' => 'France',
        ]);
    }

    /** @param array<string, string> $surcharge */
    private function payer(array $surcharge = []): void
    {
        $this->client->request('GET', '/commande/paiement');
        $this->client->submitForm('Continuer vers la confirmation', $surcharge + [
            'paiement_form[titulaire]' => 'Jean Dupont',
            'paiement_form[numero]' => '4242 4242 4242 4242',
            'paiement_form[expiration]' => '12/99',
            'paiement_form[cryptogramme]' => '123',
        ]);
    }

    public function testLaLivraisonMeneAuPaiement(): void
    {
        $this->remplirPanier();
        $this->renseignerLivraison();

        $this->assertResponseRedirects('/commande/paiement');
    }

    public function testLePaiementExigeUneLivraison(): void
    {
        $this->remplirPanier();

        $this->client->request('GET', '/commande/paiement');

        $this->assertResponseRedirects('/commande/livraison');
    }

    public function testLaConfirmationExigeUnPaiement(): void
    {
        $this->remplirPanier();
        $this->renseignerLivraison();

        $this->client->request('GET', '/commande/confirmation');

        $this->assertResponseRedirects('/commande/paiement');
    }

    public function testUnPaiementValideMeneALaConfirmation(): void
    {
        $this->remplirPanier();
        $this->renseignerLivraison();
        $this->payer();

        $this->assertResponseRedirects('/commande/confirmation');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'se terminant par 4242');
        $this->assertSelectorTextNotContains('body', '4242 4242 4242 4242');
    }

    public function testUnNumeroDeCarteInvalideEstRefuse(): void
    {
        $this->remplirPanier();
        $this->renseignerLivraison();
        $this->payer(['paiement_form[numero]' => '4242 4242 4242 4241']);

        $this->assertResponseIsUnprocessable();
        $this->assertSelectorTextContains('body', 'Numéro de carte invalide');
    }

    public function testUneCarteExpireeEstRefusee(): void
    {
        $this->remplirPanier();
        $this->renseignerLivraison();
        $this->payer(['paiement_form[expiration]' => '01/20']);

        $this->assertResponseIsUnprocessable();
        $this->assertSelectorTextContains('body', 'Cette carte est expirée');
    }

    public function testLaCommandeEstEnregistreeApresPaiement(): void
    {
        $this->remplirPanier();
        $this->renseignerLivraison();
        $this->payer();

        $crawler = $this->client->request('GET', '/commande/confirmation');
        $jeton = (string) $crawler->filter('input[name="_token"]')->first()->attr('value');
        $this->client->request('POST', '/commande/valider', ['_token' => $jeton]);

        $this->assertResponseRedirects();
        $this->assertStringContainsString('/commande/merci/', (string) $this->client->getResponse()->headers->get('Location'));
    }
}
