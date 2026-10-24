<?php

namespace App\Tests\Entity;

use App\Entity\LigneCommande;
use PHPUnit\Framework\TestCase;

class LigneCommandeTest extends TestCase
{
    public function testSousTotalMultipliePrixParQuantite(): void
    {
        $ligne = (new LigneCommande())
            ->setNomProduit('Produit test')
            ->setPrixUnitaire('19.99')
            ->setQuantite(3);

        $this->assertSame('59.97', $ligne->getSousTotal());
    }

    public function testSousTotalEviteLesErreursDArrondi(): void
    {
        $ligne = (new LigneCommande())
            ->setNomProduit('Produit test')
            ->setPrixUnitaire('0.10')
            ->setQuantite(3);

        $this->assertSame('0.30', $ligne->getSousTotal());
    }
}