<?php

namespace App\Form;

use App\Entity\TypePrise;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

/**
 * FormType unifié pour TypePrise
 * Utilisé pour création rapide ET édition
 * Pattern DRY : Un seul FormType par entité
 */
class TypePriseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class, [
                'label' => 'Type de prise',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Le type de prise est obligatoire']),
                    new Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Le type doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le type ne peut pas dépasser {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Portrait innovant, Groupe créatif...',
                    'autofocus' => $options['autofocus']
                ],
                'help' => 'Décrivez le type de prise de vue'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description (optionnelle)',
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 500,
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Décrivez les spécificités de ce type de prise...'
                ],
                'help' => 'Détails techniques ou artistiques du type de prise'
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'Type actif',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'help' => 'Un type actif peut être utilisé dans les nouvelles prises de vue'
            ])
        ;
        
        // Bouton submit conditionnel
        if ($options['show_submit']) {
            $builder->add('submit', SubmitType::class, [
                'label' => $options['submit_label'],
                'attr' => [
                    'class' => 'btn btn-info fw-bold'
                ]
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TypePrise::class,
            'show_submit' => true,
            'submit_label' => 'Enregistrer',
            'autofocus' => true,
            'attr' => [
                'novalidate' => 'novalidate',
                'class' => 'needs-validation'
            ]
        ]);
    }
} 