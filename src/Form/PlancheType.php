<?php

namespace App\Form;

use App\Entity\Planche;
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
 * FormType unifié pour Planche
 * Utilisé pour création rapide ET édition
 * Pattern DRY : Un seul FormType par entité
 */
class PlancheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class, [
                'label' => 'Nom de la planche',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Le nom de la planche est obligatoire']),
                    new Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Album photo standard',
                    'maxlength' => 200,
                    'autofocus' => $options['autofocus']
                ],
                'help' => 'Nom unique pour identifier cette planche'
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
                    'placeholder' => 'Décrivez brièvement cette planche...'
                ],
                'help' => 'Description détaillée de la planche'
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'Planche active',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'help' => 'Les planches actives sont disponibles pour les prises de vue'
            ])
        ;
        
        // Bouton submit conditionnel
        if ($options['show_submit']) {
            $builder->add('submit', SubmitType::class, [
                'label' => $options['submit_label'],
                'attr' => [
                    'class' => 'btn btn-warning fw-bold'
                ]
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Planche::class,
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