<?php

namespace App\Service;

use App\Entity\Commande;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class CommandeMailer
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        #[Autowire('%env(MAIL_FROM)%')] private string $mailFrom,
    ) {
    }

    /** Retourne true si l'email est parti, false sinon (la commande n'est jamais bloquée). */
    public function envoyerConfirmation(Commande $commande): bool
    {
        return $this->envoyer(
            $commande,
            sprintf('InnovShop - Confirmation de votre commande n°%d', $commande->getId()),
            'emails/commande_confirmation'
        );
    }

    public function envoyerChangementStatut(Commande $commande): bool
    {
        return $this->envoyer(
            $commande,
            sprintf(
                'InnovShop - Commande n°%d : %s',
                $commande->getId(),
                mb_strtolower($commande->getStatutLibelle())
            ),
            'emails/commande_statut'
        );
    }

    private function envoyer(Commande $commande, string $sujet, string $gabarit): bool
    {
        $utilisateur = $commande->getUtilisateur();

        $email = (new TemplatedEmail())
            ->from(new Address($this->mailFrom, 'InnovShop'))
            ->to(new Address(
                $utilisateur->getEmail(),
                trim($utilisateur->getPrenom() . ' ' . $utilisateur->getNom())
            ))
            ->subject($sujet)
            ->htmlTemplate($gabarit . '.html.twig')
            ->textTemplate($gabarit . '.txt.twig')
            ->context(['commande' => $commande]);

        try {
            $this->mailer->send($email);

            return true;
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error("Échec de l'envoi d'un email de commande", [
                'commande' => $commande->getId(),
                'sujet' => $sujet,
                'erreur' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
