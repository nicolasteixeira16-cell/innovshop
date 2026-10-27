<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Paiement par carte (simulation) : les données saisies sont seulement contrôlées,
 * elles ne sont ni enregistrées ni transmises.
 */
class PaiementFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titulaire', TextType::class, [
                'label' => 'Titulaire de la carte',
                'attr' => ['autocomplete' => 'cc-name'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir le nom du titulaire.'),
                    new Length(max: 100),
                    new Regex(pattern: '/[<>]/', match: false, message: 'Les caractères < et > ne sont pas autorisés.'),
                ],
            ])
            ->add('numero', TextType::class, [
                'label' => 'Numéro de carte',
                'attr' => ['autocomplete' => 'cc-number', 'inputmode' => 'numeric', 'placeholder' => '4242 4242 4242 4242'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir le numéro de carte.'),
                    new Regex(pattern: '/^[0-9 ]{13,23}$/', message: 'Le numéro de carte doit contenir uniquement des chiffres.'),
                ],
            ])
            ->add('expiration', TextType::class, [
                'label' => "Date d'expiration (MM/AA)",
                'attr' => ['autocomplete' => 'cc-exp', 'inputmode' => 'numeric', 'placeholder' => '12/29'],
                'constraints' => [
                    new NotBlank(message: "Veuillez saisir la date d'expiration."),
                    new Regex(pattern: '/^(0[1-9]|1[0-2])\/[0-9]{2}$/', message: 'Format attendu : MM/AA.'),
                ],
            ])
            ->add('cryptogramme', TextType::class, [
                'label' => 'Cryptogramme',
                'attr' => ['autocomplete' => 'cc-csc', 'inputmode' => 'numeric', 'maxlength' => 4, 'placeholder' => '123'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir le cryptogramme.'),
                    new Regex(pattern: '/^[0-9]{3,4}$/', message: 'Le cryptogramme comporte 3 ou 4 chiffres.'),
                ],
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, $this->controlerCarte(...))
        ;
    }

    /** Contrôles qui portent sur plusieurs champs : clé de Luhn et carte non expirée. */
    private function controlerCarte(FormEvent $event): void
    {
        $form = $event->getForm();
        $donnees = $event->getData();

        $chiffres = preg_replace('/\s+/', '', (string) ($donnees['numero'] ?? ''));
        if ($form->get('numero')->isValid() && '' !== $chiffres) {
            if (!ctype_digit($chiffres) || strlen($chiffres) < 13 || strlen($chiffres) > 19 || !$this->luhnValide($chiffres)) {
                $form->get('numero')->addError(new FormError('Numéro de carte invalide.'));
            }
        }

        $expiration = (string) ($donnees['expiration'] ?? '');
        if ($form->get('expiration')->isValid() && 1 === preg_match('/^(\d{2})\/(\d{2})$/', $expiration, $m)) {
            $finDeMois = new \DateTimeImmutable(sprintf('20%s-%s-01 23:59:59', $m[2], $m[1]));
            $finDeMois = $finDeMois->modify('last day of this month');
            if ($finDeMois < new \DateTimeImmutable()) {
                $form->get('expiration')->addError(new FormError('Cette carte est expirée.'));
            }
        }
    }

    private function luhnValide(string $chiffres): bool
    {
        $somme = 0;
        $double = false;
        for ($i = strlen($chiffres) - 1; $i >= 0; --$i) {
            $n = (int) $chiffres[$i];
            if ($double) {
                $n *= 2;
                if ($n > 9) {
                    $n -= 9;
                }
            }
            $somme += $n;
            $double = !$double;
        }

        return 0 === $somme % 10;
    }
}
