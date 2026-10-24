<?php

namespace App\Form;

use App\Entity\Adresse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdresseFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rue', TextType::class, ['label' => 'Adresse'])
            ->add('codePostal', TextType::class, ['label' => 'Code postal'])
            ->add('ville', TextType::class, ['label' => 'Ville'])
            ->add('pays', TextType::class, ['label' => 'Pays'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Adresse::class]);
    }
}
