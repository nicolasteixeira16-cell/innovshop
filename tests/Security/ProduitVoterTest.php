<?php

namespace App\Tests\Security;

use App\Entity\Categorie;
use App\Entity\Produit;
use App\Entity\Utilisateur;
use App\Security\Voter\ProduitVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ProduitVoterTest extends TestCase
{
    private function produit(string $slugCategorie): Produit
    {
        return (new Produit())->setCategorie((new Categorie())->setNom('Test')->setSlug($slugCategorie));
    }

    /** @param list<string> $roles */
    private function jeton(array $roles): UsernamePasswordToken
    {
        $utilisateur = (new Utilisateur())->setEmail('test@test.fr')->setRoles($roles);

        return new UsernamePasswordToken($utilisateur, 'main', $utilisateur->getRoles());
    }

    public function testUnProduitOrdinaireEstOuvertATous(): void
    {
        $voter = new ProduitVoter();
        $produit = $this->produit('vetements');

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote(new NullToken(), $produit, [ProduitVoter::COMMANDER]));
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($this->jeton([]), $produit, [ProduitVoter::COMMANDER]));
    }

    public function testUnPrototypeEstRefuseAuVisiteur(): void
    {
        $voter = new ProduitVoter();

        $this->assertSame(VoterInterface::ACCESS_DENIED, $voter->vote(new NullToken(), $this->produit('prototypes'), [ProduitVoter::COMMANDER]));
    }

    public function testUnPrototypeEstRefuseAUnClientSansRoleTesteur(): void
    {
        $voter = new ProduitVoter();

        $this->assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($this->jeton([]), $this->produit('prototypes'), [ProduitVoter::COMMANDER]));
    }

    public function testUnPrototypeEstAccordeAUnTesteur(): void
    {
        $voter = new ProduitVoter();

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($this->jeton(['ROLE_TESTEUR']), $this->produit('prototypes'), [ProduitVoter::COMMANDER]));
    }

    public function testUnPrototypeEstAccordeAUnAdministrateur(): void
    {
        $voter = new ProduitVoter();

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($this->jeton(['ROLE_ADMIN']), $this->produit('prototypes'), [ProduitVoter::COMMANDER]));
    }
}
