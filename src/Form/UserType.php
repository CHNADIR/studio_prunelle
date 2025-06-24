<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom complet',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Jean Dupont'
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le nom est obligatoire'),
                    new Assert\Length(
                        min: 2,
                        max: 50,
                        minMessage: 'Le nom doit faire au moins {{ limit }} caractères',
                        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
                    )
                ]
            ])
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: jdupont'
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le nom d\'utilisateur est obligatoire'),
                    new Assert\Length(
                        min: 3,
                        max: 50,
                        minMessage: 'Le nom d\'utilisateur doit faire au moins {{ limit }} caractères',
                        maxMessage: 'Le nom d\'utilisateur ne peut pas dépasser {{ limit }} caractères'
                    ),
                    new Assert\Regex(
                        pattern: '/^[a-zA-Z0-9_.-]+$/',
                        message: 'Le nom d\'utilisateur ne peut contenir que des lettres, chiffres, points, tirets et underscores'
                    )
                ],
                'help' => 'Identifiant unique pour la connexion (lettres, chiffres, points, tirets et underscores uniquement)'
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'email@example.com'
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'L\'email est obligatoire'),
                    new Assert\Email(message: 'L\'adresse email n\'est pas valide')
                ]
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Rôle utilisateur',
                'choices' => [
                    'Photographe - Consultation de ses prises de vue' => 'ROLE_PHOTOGRAPHE',
                    'Administrateur - Gestion photos et écoles' => 'ROLE_ADMIN',
                    'Super Administrateur - Accès complet' => 'ROLE_SUPERADMIN',
                ],
                'mapped' => false,
                'expanded' => false,
                'multiple' => false,
                'attr' => [
                    'class' => 'form-select'
                ],
                'placeholder' => 'Sélectionnez un rôle...',
                'constraints' => [
                    new Assert\NotBlank(message: 'Un rôle doit être sélectionné')
                ],
                'help' => 'Chaque utilisateur a un seul rôle principal'
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => $options['is_edit'] ? 'Nouveau mot de passe (optionnel)' : 'Mot de passe',
                'mapped' => false,
                'required' => $options['is_edit'] === false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => $options['is_edit'] ? 'Laissez vide pour conserver l\'actuel' : 'Minimum 8 caractères',
                    'autocomplete' => 'new-password'
                ],
                'help' => $options['is_edit'] 
                    ? 'Laissez ce champ vide pour conserver le mot de passe actuel'
                    : 'Le mot de passe doit contenir au moins 8 caractères',
                'constraints' => $options['is_edit'] ? [] : [
                    new Assert\NotBlank(message: 'Le mot de passe est obligatoire'),
                    new Assert\Length(
                        min: 8,
                        minMessage: 'Le mot de passe doit faire au moins {{ limit }} caractères'
                    )
                ]
            ]);

        // Afficher la dernière connexion seulement en édition
        if ($options['is_edit']) {
            $builder->add('lastLogin', DateTimeType::class, [
                'label' => 'Dernière connexion',
                'widget' => 'single_text',
                'disabled' => true,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => true
                ],
                'help' => 'Information en lecture seule'
            ]);
        }

        // Gestionnaire d'événements pour mapper le rôle unique
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $user = $event->getData();
            $form = $event->getForm();

            if ($user && !empty($user->getRoles())) {
                // Récupérer le rôle principal (le plus élevé)
                $roles = $user->getRoles();
                $mainRole = $this->getMainRole($roles);
                
                $form->get('role')->setData($mainRole);
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $user = $event->getData();
            $form = $event->getForm();

            if ($user && $form->has('role')) {
                $selectedRole = $form->get('role')->getData();
                
                if ($selectedRole) {
                    // Définir le rôle unique (+ ROLE_USER automatique)
                    $user->setRoles([$selectedRole]);
                }
            }
        });
    }

    /**
     * Détermine le rôle principal d'un utilisateur
     */
    private function getMainRole(array $roles): string
    {
        // Ordre de priorité (du plus élevé au plus bas)
        $roleHierarchy = [
            'ROLE_SUPERADMIN',
            'ROLE_ADMIN', 
            'ROLE_PHOTOGRAPHE',
        ];

        foreach ($roleHierarchy as $role) {
            if (in_array($role, $roles)) {
                return $role;
            }
        }

        return 'ROLE_PHOTOGRAPHE'; // Par défaut
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
        ]);
    }
}
