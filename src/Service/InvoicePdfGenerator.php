<?php

namespace App\Service;

use App\Entity\Commande;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

class InvoicePdfGenerator
{
    public function __construct(private Environment $twig)
    {
    }

    public function numero(Commande $commande): string
    {
        return sprintf('FA-%s-%05d', $commande->getDateCreation()->format('Y'), $commande->getId());
    }

    /** Retourne le contenu binaire du PDF. */
    public function generer(Commande $commande): string
    {
        $html = $this->twig->render('account/invoice.html.twig', [
            'commande' => $commande,
            'numero' => $this->numero($commande),
        ]);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans'); // gère les accents et le symbole €
        $options->set('isRemoteEnabled', false);     // aucune ressource externe chargée

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}