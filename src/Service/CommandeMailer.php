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
        $utilisateur = $commande->getUtilisateur();

        $email = (new TemplatedEmail())
            ->from(new Address($this->mailFrom, 'InnovShop'))
            ->to(new Address(
                $utilisateur->getEmail(),
                trim($utilisateur->getPrenom() . ' ' . $utilisateur->getNom())
            ))
            ->subject(sprintf('InnovShop - Confirmation de votre commande n°%d', $commande->getId()))
            ->htmlTemplate('emails/commande_confirmation.html.twig')
            ->textTemplate('emails/commande_confirmation.txt.twig')
            ->context(['commande' => $commande]);

        try {
            $this->mailer->send($email);

            return true;
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error("Échec de l'envoi de l'email de confirmation", [
                'commande' => $commande->getId(),
                'erreur' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}