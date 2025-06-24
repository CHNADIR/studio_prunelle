<?php

namespace App\Form;

use App\Entity\PochetteFratrie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

/**
 * FormType unifié pour PochetteFratrie
 * Utilisé pour création rapide ET édition
 * Pattern DRY : Un seul FormType par entité
 */
class PochetteFratrieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class, [
                'label' => 'Nom de la pochette',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Le nom de la pochette est obligatoire']),
                    new Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Pochette fratrie A4',
                    'maxlength' => 200,
                    'autofocus' => $options['autofocus']
                ],
                'help' => 'Nom unique pour identifier cette pochette fratrie'
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
                    'placeholder' => 'Décrivez brièvement cette pochette...'
                ],
                'help' => 'Description détaillée de la pochette fratrie'
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'Pochette active',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'help' => 'Les pochettes actives sont disponibles pour les prises de vue'
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
            'data_class' => PochetteFratrie::class,
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