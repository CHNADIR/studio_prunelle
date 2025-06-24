<?php

namespace App\Form;

use App\Entity\Ecole;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * FormType pour les écoles avec validation renforcée
 * Pattern appliqué: FormType Pattern (patterns.md)
 */
class EcoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Code école',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '25108 (5 chiffres)',
                    'maxlength' => 5,
                    'pattern' => '[0-9]{5}'
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le code école est obligatoire'),
                    new Assert\Length(
                        exactly: 5,
                        exactMessage: 'Le code école doit faire exactement {{ limit }} caractères'
                    ),
                    new Assert\Regex(
                        pattern: '/^\d{5}$/',
                        message: 'Le code école doit contenir exactement 5 chiffres'
                    )
                ],
                'help' => 'Code unique de 5 chiffres (format: 25108)'
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom de l\'école',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: École primaire Jean Jaurès'
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le nom de l\'école est obligatoire'),
                    new Assert\Length(
                        min: 3,
                        max: 100,
                        minMessage: 'Le nom doit faire au moins {{ limit }} caractères',
                        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
                    )
                ]
            ])
            ->add('genre', TextType::class, [
                'label' => 'Type d\'établissement',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Primaire, Collège, Lycée, Maternelle...',
                    'list' => 'genre-suggestions'
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le type d\'établissement est obligatoire'),
                    new Assert\Length(
                        max: 50,
                        maxMessage: 'Le type ne peut pas dépasser {{ limit }} caractères'
                    )
                ],
                'help' => 'Type d\'établissement (primaire, collège, lycée...)'
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 123 rue de la République'
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'L\'adresse est obligatoire'),
                    new Assert\Length(
                        min: 5,
                        max: 200,
                        minMessage: 'L\'adresse doit faire au moins {{ limit }} caractères',
                        maxMessage: 'L\'adresse ne peut pas dépasser {{ limit }} caractères'
                    )
                ]
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Paris'
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'La ville est obligatoire'),
                    new Assert\Length(
                        min: 2,
                        max: 50,
                        minMessage: 'La ville doit faire au moins {{ limit }} caractères',
                        maxMessage: 'La ville ne peut pas dépasser {{ limit }} caractères'
                    )
                ]
            ])
            ->add('codePostal', TextType::class, [
                'label' => 'Code postal',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '75001',
                    'pattern' => '[0-9]{5}'
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le code postal est obligatoire'),
                    new Assert\Regex(
                        pattern: '/^\d{5}$/',
                        message: 'Le code postal doit être composé de 5 chiffres'
                    )
                ]
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Téléphone',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '01 23 45 67 89'
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le téléphone est obligatoire'),
                    new Assert\Regex(
                        pattern: '/^(?:(?:\+|00)33[\s\.\-]{0,3}(?:\(0\)[\s\.\-]{0,3})?|0)[1-9](?:[\s\.\-]?\d{2}){4}$/',
                        message: 'Le numéro de téléphone n\'est pas valide (format français attendu)'
                    )
                ],
                'help' => 'Format: 01 23 45 67 89 ou +33 1 23 45 67 89'
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'contact@ecole.fr'
                ],
                'constraints' => [
                    new Assert\Email(message: 'L\'adresse email n\'est pas valide'),
                    new Assert\Length(
                        max: 100,
                        maxMessage: 'L\'email ne peut pas dépasser {{ limit }} caractères'
                    )
                ],
                'help' => 'Email de contact (optionnel)'
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'École active',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'help' => 'Décochez pour désactiver l\'école (elle n\'apparaîtra plus dans les listes)'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ecole::class,
            'attr' => [
                'novalidate' => 'novalidate' // Utiliser la validation côté serveur
            ]
        ]);
    }
}