<?php

namespace App\Tests\Controller;

use App\Entity\Utilisateur;
use App\Tests\FonctionnelTestCase;

class ProfilTest extends FonctionnelTestCase
{
    public function testModificationDuProfil(): void
    {
        $utilisateur = $this->creerUtilisateur('profil@test.local');
        $this->client->loginUser($utilisateur);

        $crawler = $this->client->request('GET', '/mon-compte/profil');
        $formulaire = $crawler->filter('form[name="profil_form"]')->form([
            'profil_form[prenom]' => 'Marie',
            'profil_form[nom]' => 'Curie',
            'profil_form[telephone]' => '0601020304',
        ]);
        $this->client->submit($formulaire);

        $this->assertResponseRedirects();

        $this->em->clear();
        $misAJour = $this->em->getRepository(Utilisateur::class)->find($utilisateur->getId());
        $this->assertSame('Marie', $misAJour->getPrenom());
        $this->assertSame('Curie', $misAJour->getNom());
        $this->assertSame('0601020304', $misAJour->getTelephone());
    }

    public function testPrenomInvalideEstRefuse(): void
    {
        $utilisateur = $this->creerUtilisateur('profil@test.local');
        $this->client->loginUser($utilisateur);

        $crawler = $this->client->request('GET', '/mon-compte/profil');
        $formulaire = $crawler->filter('form[name="profil_form"]')->form([
            'profil_form[prenom]' => '<b>Test</b>',
        ]);
        $this->client->submit($formulaire);

        $this->assertFalse($this->client->getResponse()->isRedirection());
        $this->assertSelectorTextContains('body', 'lettres');

        $this->em->clear();
        $inchange = $this->em->getRepository(Utilisateur::class)->find($utilisateur->getId());
        $this->assertSame('Jean', $inchange->getPrenom());
    }
}
