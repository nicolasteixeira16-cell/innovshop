<?php

namespace App\Form;

use App\Entity\CandidatureTesteur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CandidatureTesteurFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Votre nom',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control', 'maxlength' => 100],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Votre adresse e-mail',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control', 'maxlength' => 180],
            ])
            ->add('motivation', TextareaType::class, [
                'label' => 'Pourquoi souhaitez-vous tester InnovShop ?',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control', 'rows' => 5, 'maxlength' => 2000],
            ])
            ->add('envoyer', SubmitType::class, [
                'label' => 'Envoyer ma candidature',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CandidatureTesteur::class,
        ]);
    }
}