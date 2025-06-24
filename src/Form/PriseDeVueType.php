<?php

namespace App\Form;

use App\Entity\Ecole;
use App\Entity\Planche;
use App\Entity\PochetteIndiv;
use App\Entity\PochetteFratrie;
use App\Entity\PriseDeVue;
use App\Entity\Theme;
use App\Entity\TypePrise;
use App\Entity\TypeVente;
use App\Entity\User;
use App\Repository\EcoleRepository;
use App\Repository\PlancheRepository;
use App\Repository\PochetteIndivRepository;
use App\Repository\PochetteFratrieRepository;
use App\Repository\ThemeRepository;
use App\Repository\TypePriseRepository;
use App\Repository\TypeVenteRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * FormType pour PriseDeVue avec sélections uniques
 * Pattern appliqué: FormType Pattern (patterns.md)
 * Responsabilité: Formulaire optimisé pour sélection unique des entités référentielles
 */
class PriseDeVueType extends AbstractType
{
    public function __construct(
        private Security $security,
        private PlancheRepository $plancheRepository,
        private TypePriseRepository $typePriseRepository,
        private TypeVenteRepository $typeVenteRepository,
        private ThemeRepository $themeRepository,
        private EcoleRepository $ecoleRepository,
        private UserRepository $userRepository,
        private PochetteIndivRepository $pochetteIndivRepository,
        private PochetteFratrieRepository $pochetteFratrieRepository
    ) {}
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // === INFORMATIONS GÉNÉRALES ===
        $builder
            ->add('datePdv', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de la prise de vue',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'max' => (new \DateTime('+1 year'))->format('Y-m-d')
                ],
                'constraints' => [
                    new Assert\NotNull(message: 'La date de prise de vue est obligatoire')
                ],
                'help' => 'Date de réalisation de la séance photo'
            ])
            ->add('ecole', EntityType::class, [
                'class' => Ecole::class,
                'choice_label' => function (Ecole $ecole) {
                    return sprintf('%s - %s (%s)', $ecole->getCode(), $ecole->getNom(), $ecole->getVille());
                },
                'query_builder' => function () {
                    return $this->ecoleRepository->createQueryBuilder('e')
                        ->where('e.active = :active')
                        ->setParameter('active', true)
                        ->orderBy('e.nom', 'ASC');
                },
                'label' => 'École',
                'placeholder' => 'Sélectionnez une école',
                'required' => true,
                'attr' => [
                    'class' => 'form-select',
                    'data-controller' => 'select-autocomplete'
                ],
                'help' => 'École où a lieu la prise de vue'
            ]);
            
        // Photographe (seulement pour les admins)
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $builder->add('photographe', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return sprintf('%s (%s)', $user->getNom(), $user->getUserIdentifier());
                },
                'query_builder' => function () {
                    return $this->userRepository->createQueryBuilder('u')
                        ->where('u.actif = :actif')
                        ->andWhere('u.roles LIKE :role')
                        ->setParameter('actif', true)
                        ->setParameter('role', '%ROLE_PHOTOGRAPHE%')
                        ->orderBy('u.nom', 'ASC');
                },
                'label' => 'Photographe',
                'placeholder' => 'Sélectionnez un photographe',
                'required' => true,
                'attr' => ['class' => 'form-select'],
                'help' => 'Photographe responsable de cette prise de vue'
            ]);
        }

        // === ÉLÈVES ET CLASSES ===
        $builder
            ->add('nbEleves', IntegerType::class, [
                'label' => 'Nombre d\'élèves',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'max' => 9999,
                    'placeholder' => 'Ex: 120'
                ],
                'constraints' => [
                    new Assert\NotNull(message: 'Le nombre d\'élèves est obligatoire'),
                    new Assert\GreaterThan(value: 0, message: 'Le nombre d\'élèves doit être supérieur à 0')
                ],
                'help' => 'Nombre total d\'élèves photographiés'
            ])
            ->add('nbClasses', IntegerType::class, [
                'label' => 'Nombre de classes',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 99,
                    'placeholder' => 'Ex: 5'
                ],
                'help' => 'Nombre de classes concernées (optionnel)'
            ]);
            
        // === TARIFICATION ===
        $builder
            ->add('prixEcole', MoneyType::class, [
                'label' => 'Prix école',
                'currency' => 'EUR',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '0.00',
                    'step' => '0.01'
                ],
                'help' => 'Montant facturé à l\'école'
            ])
            ->add('prixParent', MoneyType::class, [
                'label' => 'Prix parents',
                'currency' => 'EUR',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '0.00',
                    'step' => '0.01'
                ],
                'help' => 'Montant facturé aux parents'
            ]);
            
        // === TYPES ET RÉFÉRENCES (SÉLECTIONS UNIQUES) ===
        $builder
            ->add('typePrise', EntityType::class, [
                'class' => TypePrise::class,
                'choice_label' => 'libelle',
                'query_builder' => function () {
                    return $this->typePriseRepository->createQueryBuilder('tp')
                        ->where('tp.active = :active')
                        ->setParameter('active', true)
                        ->orderBy('tp.libelle', 'ASC');
                },
                'label' => 'Type de prise',
                'placeholder' => 'Sélectionnez un type de prise',
                'required' => false,
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Type de prise de vue (individuel, groupe, etc.)'
            ])
            ->add('typeVente', EntityType::class, [
                'class' => TypeVente::class,
                'choice_label' => 'libelle',
                'query_builder' => function () {
                    return $this->typeVenteRepository->createQueryBuilder('tv')
                        ->where('tv.active = :active')
                        ->setParameter('active', true)
                        ->orderBy('tv.libelle', 'ASC');
                },
                'label' => 'Type de vente',
                'placeholder' => 'Sélectionnez un type de vente',
                'required' => false,
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Modalité de vente (individuel, collectif, etc.)'
            ])
            ->add('theme', EntityType::class, [
                'class' => Theme::class,
                'choice_label' => 'libelle',
                'query_builder' => function () {
                    return $this->themeRepository->createQueryBuilder('t')
                        ->where('t.active = :active')
                        ->setParameter('active', true)
                        ->orderBy('t.libelle', 'ASC');
                },
                'label' => 'Thème',
                'placeholder' => 'Sélectionnez un thème',
                'required' => false,
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Thème de la séance photo'
            ]);
            
        // === PLANCHES ET POCHETTES (SÉLECTIONS MULTIPLES) ===
        $builder
            ->add('pochettesIndiv', EntityType::class, [
                'class' => PochetteIndiv::class,
                'choice_label' => 'libelle',
                'query_builder' => function () {
                    return $this->pochetteIndivRepository->createQueryBuilder('p')
                        ->where('p.active = :active')
                        ->setParameter('active', true)
                        ->orderBy('p.libelle', 'ASC');
                },
                'label' => 'Pochettes individuelles',
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'attr' => [
                    'class' => 'form-select',
                    'multiple' => 'multiple'
                ],
                'help' => 'Types de pochettes pour les photos individuelles (sélection multiple)'
            ])
            ->add('pochettesFratrie', EntityType::class, [
                'class' => PochetteFratrie::class,
                'choice_label' => 'libelle',
                'query_builder' => function () {
                    return $this->pochetteFratrieRepository->createQueryBuilder('p')
                        ->where('p.active = :active')
                        ->setParameter('active', true)
                        ->orderBy('p.libelle', 'ASC');
                },
                'label' => 'Pochettes fratries',
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'attr' => [
                    'class' => 'form-select',
                    'multiple' => 'multiple'
                ],
                'help' => 'Types de pochettes pour les photos de fratries (sélection multiple)'
            ])
            ->add('planches', EntityType::class, [
                'class' => Planche::class,
                'choice_label' => 'libelle',
                'query_builder' => function () {
                    return $this->plancheRepository->createQueryBuilder('p')
                        ->where('p.active = :active')
                        ->setParameter('active', true)
                        ->orderBy('p.libelle', 'ASC');
                },
                'label' => 'Planches',
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'attr' => [
                    'class' => 'form-select',
                    'multiple' => 'multiple'
                ],
                'help' => 'Types de planches ou supports (sélection multiple)'
            ]);
            
        // === COMMENTAIRES ===
        $builder
            ->add('commentaire', TextareaType::class, [
                'label' => 'Commentaires',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'class' => 'form-control',
                    'placeholder' => 'Commentaires ou remarques sur cette prise de vue...',
                    'maxlength' => 2000
                ],
                'help' => 'Remarques, observations ou informations complémentaires'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PriseDeVue::class,
            'attr' => [
                'novalidate' => 'novalidate', // Désactiver validation HTML5 pour utiliser Symfony
                'class' => 'needs-validation'
            ]
        ]);
    }
}