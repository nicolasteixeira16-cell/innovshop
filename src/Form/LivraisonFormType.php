<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class LivraisonFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rue', TextType::class, [
                'label' => 'Adresse',
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir votre adresse.'),
                    new Length(max: 255),
                    new Regex(pattern: '/[<>]/', match: false, message: 'Les caractères < et > ne sont pas autorisés.'),
                ],
            ])
            ->add('codePostal', TextType::class, [
                'label' => 'Code postal',
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir votre code postal.'),
                    new Regex(pattern: '/^[0-9A-Za-z \-]{3,10}$/', message: 'Code postal invalide.'),
                ],
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir votre ville.'),
                    new Length(max: 100),
                    new Regex(pattern: '/[<>]/', match: false, message: 'Les caractères < et > ne sont pas autorisés.'),
                ],
            ])
            ->add('pays', TextType::class, [
                'label' => 'Pays',
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir votre pays.'),
                    new Length(max: 100),
                    new Regex(pattern: '/[<>]/', match: false, message: 'Les caractères < et > ne sont pas autorisés.'),
                ],
            ])
        ;
    }
}
