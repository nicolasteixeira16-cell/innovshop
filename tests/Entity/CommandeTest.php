<?php

namespace App\Tests\Entity;

use App\Entity\Commande;
use PHPUnit\Framework\TestCase;

class CommandeTest extends TestCase
{
    public function testLibelleDuStatutExpediee(): void
    {
        $commande = (new Commande())->setStatut(Commande::STATUT_EXPEDIEE);

        $this->assertSame('Expédiée', $commande->getStatutLibelle());
    }

    public function testStatutInconnuRetourneLaValeurBrute(): void
    {
        $commande = (new Commande())->setStatut('inconnu');

        $this->assertSame('inconnu', $commande->getStatutLibelle());
    }
}
