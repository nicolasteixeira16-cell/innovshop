<?php

namespace App\Tests\Controller;

use App\Tests\FonctionnelTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AccesTest extends FonctionnelTestCase
{
    #[DataProvider('pagesPubliques')]
    public function testPagePubliqueAccessible(string $url): void
    {
        $this->client->request('GET', $url);

        $this->assertResponseIsSuccessful();
    }

    public static function pagesPubliques(): iterable
    {
        yield 'accueil' => ['/'];
        yield 'catalogue' => ['/produits'];
        yield 'connexion' => ['/login'];
        yield 'inscription' => ['/register'];
        yield 'panier' => ['/panier'];
    }

    public function testPageInconnueRenvoie404(): void
    {
        $this->client->request('GET', '/cette-page-n-existe-pas');

        $this->assertResponseStatusCodeSame(404);
    }

    #[DataProvider('pagesProtegees')]
    public function testVisiteurRedirigeVersLaConnexion(string $url): void
    {
        $this->client->request('GET', $url);

        $this->assertResponseRedirects('/login');
    }

    public static function pagesProtegees(): iterable
    {
        yield 'compte' => ['/mon-compte'];
        yield 'commandes' => ['/mon-compte/commandes'];
        yield 'profil' => ['/mon-compte/profil'];
        yield 'admin' => ['/admin'];
    }

    public function testClientSimpleNAPasAccesALAdmin(): void
    {
        $this->client->loginUser($this->creerUtilisateur('client@test.local'));

        $this->client->request('GET', '/admin');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdministrateurAccedeALAdmin(): void
    {
        $this->client->loginUser($this->creerUtilisateur('admin@test.local', ['ROLE_ADMIN']));

        $this->client->request('GET', '/admin');

        $this->assertResponseIsSuccessful();
    }

    public function testMotDePasseEstHasheEnBcrypt(): void
    {
        $utilisateur = $this->creerUtilisateur('hash@test.local');
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $hash = $hasher->hashPassword($utilisateur, 'MotDePasse-Solide-123');

        $this->assertStringStartsWith('$2y$', $hash);
        $this->assertNotSame('MotDePasse-Solide-123', $hash);
    }

    public function testActionDuPanierSansJetonCsrfEstRefusee(): void
    {
        $this->client->loginUser($this->creerUtilisateur('csrf@test.local'));

        $this->client->request('POST', '/panier/vider');

        $this->assertResponseStatusCodeSame(403);
    }
}