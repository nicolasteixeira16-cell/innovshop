<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProduitVendeurFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du produit',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control', 'maxlength' => 150],
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisir une catégorie',
                'label' => 'Catégorie',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-select'],
                'constraints' => [new NotBlank(message: 'Choisissez une catégorie.')],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control', 'rows' => 6],
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix (€)',
                'label_attr' => ['class' => 'form-label'],
                'scale' => 2,
                'html5' => true,
                'input' => 'string',
                'attr' => ['class' => 'form-control', 'step' => '0.01', 'min' => '0.01'],
            ])
            ->add('imageFichier', FileType::class, [
                'label' => 'Image du produit (JPEG, PNG ou WebP, 2 Mo maximum)',
                'label_attr' => ['class' => 'form-label'],
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control', 'accept' => 'image/jpeg,image/png,image/webp'],
                'constraints' => [
                    new File(
                        maxSize: '2M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        mimeTypesMessage: 'Formats acceptés : JPEG, PNG ou WebP.',
                    ),
                ],
            ])
            ->add('enregistrer', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn btn-dark'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}