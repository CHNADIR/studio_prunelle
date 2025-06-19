<?php

namespace App\Form;

use App\Entity\Ecole;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EcoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Code (5 caractères)',
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom de l\'école',
            ])
            ->add('genre', TextType::class, [
                'label' => 'Genre (ex: primaire, collège...)',
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
            ])
            ->add('codePostal', TextType::class, [
                'label' => 'Code Postal',
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone',
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'label' => 'E-mail',
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ecole::class,
        ]);
    }
}