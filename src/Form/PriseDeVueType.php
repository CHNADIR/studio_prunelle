<?php

namespace App\Form;

use App\Entity\Ecole;
use App\Entity\PriseDeVue;
use App\Entity\Theme;
use App\Entity\TypePrise;
use App\Entity\TypeVente;
use App\Entity\User;
use App\Repository\ThemeRepository;
use App\Repository\TypePriseRepository;
use App\Repository\TypeVenteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class PriseDeVueType extends AbstractType
{
    private $security;
    private $typePriseRepository;
    private $typeVenteRepository;
    private $themeRepository;

    public function __construct(
        Security $security,
        TypePriseRepository $typePriseRepository,
        TypeVenteRepository $typeVenteRepository,
        ThemeRepository $themeRepository
    ) {
        $this->security = $security;
        $this->typePriseRepository = $typePriseRepository;
        $this->typeVenteRepository = $typeVenteRepository;
        $this->themeRepository = $themeRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de la prise de vue',
                'required' => true,
            ])
            ->add('nbEleves', IntegerType::class, [
                'label' => 'Nombre d\'élèves',
                'required' => true,
                'attr' => [
                    'min' => 1,
                ]
            ])
            ->add('classes', TextType::class, [
                'label' => 'Classes concernées',
                'required' => false,
            ])
            ->add('typePrise', EntityType::class, [
                'class' => TypePrise::class,
                'choice_label' => 'nom',
                'query_builder' => function() {
                    return $this->typePriseRepository->createQueryBuilder('tp')
                        ->where('tp.active = :active')
                        ->setParameter('active', true)
                        ->orderBy('tp.nom', 'ASC');
                },
                'placeholder' => 'Sélectionnez un type de prise',
                'required' => false,
                'attr' => [
                    'class' => 'form-control select-with-add',
                    'data-add-url' => '/admin/type-prise/modal-new',
                ]
            ])
            ->add('typeVente', EntityType::class, [
                'class' => TypeVente::class,
                'choice_label' => 'nom',
                'query_builder' => function() {
                    return $this->typeVenteRepository->createQueryBuilder('tv')
                        ->where('tv.active = :active')
                        ->setParameter('active', true)
                        ->orderBy('tv.nom', 'ASC');
                },
                'placeholder' => 'Sélectionnez un type de vente',
                'required' => false,
                'attr' => [
                    'class' => 'form-control select-with-add',
                    'data-add-url' => '/admin/type-vente/modal-new',
                ]
            ])
            ->add('theme', EntityType::class, [
                'class' => Theme::class,
                'choice_label' => 'nom',
                'query_builder' => function() {
                    return $this->themeRepository->createQueryBuilder('t')
                        ->where('t.active = :active')
                        ->setParameter('active', true)
                        ->orderBy('t.nom', 'ASC');
                },
                'placeholder' => 'Sélectionnez un thème',
                'required' => false,
                'attr' => [
                    'class' => 'form-control select-with-add',
                    'data-add-url' => '/admin/theme/modal-new',
                ]
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Commentaires',
                'required' => false,
            ])
            ->add('ecole', EntityType::class, [
                'class' => Ecole::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionnez une école',
                'required' => true,
                'query_builder' => function ($er) {
                    return $er->createQueryBuilder('e')
                        ->where('e.active = :active')
                        ->setParameter('active', true)
                        ->orderBy('e.nom', 'ASC');
                },
            ]);

        // Si c'est un administrateur, il peut choisir le photographe
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $builder->add('photographe', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionnez un photographe',
                'required' => true,
                'query_builder' => function ($er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles LIKE :role')
                        ->setParameter('role', '%ROLE_PHOTOGRAPHE%')
                        ->orderBy('u.nom', 'ASC');
                },
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PriseDeVue::class,
        ]);
    }
}