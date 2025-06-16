<?php

namespace App\Form\Admin;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isNew = $options['is_new'];

        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse Email',
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôles',
                'choices' => [
                    'Administrateur' => 'ROLE_ADMIN',
                    'Responsable Administratif' => 'ROLE_RESPONSABLE_ADMINISTRATIF',
                    'Photographe' => 'ROLE_PHOTOGRAPHE',
                    'Utilisateur' => 'ROLE_USER'
                ],
                'expanded' => true,
                'multiple' => true,
                'required' => true,
            ]);
        
        // Configuration conditionnelle des contraintes de mot de passe
        $passwordConstraints = [];
        if ($isNew) {
            $passwordConstraints = [
                new NotBlank([
                    'message' => 'Veuillez entrer un mot de passe',
                    'groups' => ['registration', 'password_update']
                ]),
                new Length([
                    'min' => 8,
                    'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                    'max' => 4096, // Max length allowed by Symfony for security reasons
                    'groups' => ['registration', 'password_update']
                ]),
            ];
        } else {
            // En mode édition, on applique uniquement la longueur minimale si un mot de passe est fourni
            $passwordConstraints = [
                new Length([
                    'min' => 8,
                    'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                    'max' => 4096,
                    'groups' => ['password_update']
                ]),
            ];
        }

        $builder->add('plainPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'label' => 'Mot de passe',
            'mapped' => false,
            'required' => $isNew,
            'first_options' => [
                'label' => 'Mot de passe',
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => $passwordConstraints
            ],
            'second_options' => [
                'label' => 'Confirmer le mot de passe',
                'attr' => ['autocomplete' => 'new-password'],
            ],
            'invalid_message' => 'Les champs du mot de passe doivent correspondre.',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_new' => false,
            'validation_groups' => function ($form) {
                $data = $form->getData();
                $groups = ['Default']; // 'Default' group is always used
                if ($data instanceof User && $data->getId() === null) { // New user
                    $groups[] = 'registration';
                }
                // If plainPassword is set during edit, add password_update group
                $plainPassword = $form->get('plainPassword')->getData();
                if ($data instanceof User && $data->getId() !== null && !empty($plainPassword)) {
                    $groups[] = 'password_update';
                }
                return $groups;
            },
        ]);
        $resolver->setAllowedTypes('is_new', 'bool');
    }
}