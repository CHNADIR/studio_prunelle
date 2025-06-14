<?php

namespace App\Form;

use App\Entity\Ecole;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EcoleForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Code UAI/RNE',
                'attr' => ['placeholder' => 'Ex: 0751234A']
            ])
            ->add('genre', TextType::class, [
                'label' => 'Type d\'établissement',
                'attr' => ['placeholder' => 'Ex: École élémentaire publique']
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom de l\'établissement',
                'attr' => ['placeholder' => 'Ex: École du Centre']
            ])
            ->add('rue', TextType::class, [
                'label' => 'Adresse (rue)',
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
            ])
            ->add('codePostal', TextType::class, [
                'label' => 'Code Postal',
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email de contact',
                'required' => false,
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'École active',
                'required' => false, // CheckboxType is false by default if not checked
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ecole::class,
        ]);
    }
}
