<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class DevenirVendeurFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomBoutique', TextType::class, [
                'label' => 'Nom de votre boutique',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control', 'maxlength' => 100],
                'constraints' => [
                    new NotBlank(message: 'Le nom de la boutique est obligatoire.'),
                    new Length(min: 2, max: 100),
                    new Regex(pattern: '/[<>]/', match: false, message: 'Les caractères < et > ne sont pas autorisés.'),
                ],
            ])
            ->add('envoyer', SubmitType::class, [
                'label' => 'Envoyer ma demande',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }
}
