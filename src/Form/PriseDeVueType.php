<?php

namespace App\Form;

use App\Entity\Ecole;
use App\Entity\Planche;
use App\Entity\PriseDeVue;
use App\Entity\Theme;
use App\Entity\TypePrise;
use App\Entity\TypeVente;
use App\Entity\User;
use App\Repository\EcoleRepository;
use App\Repository\PlancheRepository;
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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use App\Enum\PlancheUsage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PriseDeVueType extends AbstractType
{
    private Security $security;
    private PlancheRepository $plancheRepository;
    private TypePriseRepository $typePriseRepository;
    private TypeVenteRepository $typeVenteRepository;
    private ThemeRepository $themeRepository;
    private EcoleRepository $ecoleRepository;
    private UserRepository $userRepository;
    private UrlGeneratorInterface $urlGenerator;
    
    public function __construct(
        Security $security,
        PlancheRepository $plancheRepository,
        TypePriseRepository $typePriseRepository,
        TypeVenteRepository $typeVenteRepository,
        ThemeRepository $themeRepository,
        EcoleRepository $ecoleRepository,
        UserRepository $userRepository,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->security            = $security;
        $this->plancheRepository   = $plancheRepository;
        $this->typePriseRepository = $typePriseRepository;
        $this->typeVenteRepository = $typeVenteRepository;
        $this->themeRepository     = $themeRepository;
        $this->ecoleRepository     = $ecoleRepository;
        $this->userRepository      = $userRepository;
        $this->urlGenerator        = $urlGenerator;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Informations générales
        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de la prise de vue',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'form-group'],
            ])
            ->add('ecole', EntityType::class, [
                'class' => Ecole::class,
                'choice_label' => 'nom',
                'query_builder' => function () {
                    return $this->ecoleRepository->createQueryBuilder('e')
                        ->where('e.active = :active')
                        ->setParameter('active', true)
                        ->orderBy('e.nom', 'ASC');
                },
                'label' => 'École',
                'placeholder' => 'Sélectionnez une école',
                'required' => true,
                'attr' => ['class' => 'form-select'],
                'row_attr' => ['class' => 'form-group'],
            ]);
            
        // Afficher le champ photographe uniquement pour les admins
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $builder->add('photographe', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'nom',
                'query_builder' => function () {
                    return $this->userRepository->createQueryBuilder('u')
                        ->where('u.roles LIKE :role')
                        ->setParameter('role', '%ROLE_PHOTOGRAPHE%')
                        ->orderBy('u.nom', 'ASC');
                },
                'label' => 'Photographe',
                'placeholder' => 'Sélectionnez un photographe',
                'required' => true,
                'attr' => ['class' => 'form-select'],
                'row_attr' => ['class' => 'form-group'],
            ]);
        }
        
        // Nombre d'élèves et classes
        $builder
            ->add('nbEleves', IntegerType::class, [
                'label' => 'Nombre d\'élèves',
                'required' => true,
                'attr' => [
                    'min' => 1,
                    'class' => 'form-control',
                ],
                'row_attr' => ['class' => 'form-group'],
            ])
            ->add('nbClasses', IntegerType::class, [
                'label' => 'Nombre de classes',
                'required' => false,
                'attr' => [
                    'min' => 0,
                    'class' => 'form-control',
                ],
                'row_attr' => ['class' => 'form-group'],
            ])
            ->add('classes', TextType::class, [
                'label' => 'Description des classes',
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'form-group'],
            ]);
            
        // Type de prise et options
        $builder
            ->add('typePrise', EntityType::class, [
                'class' => TypePrise::class,
                'choice_label' => 'nom',
                'query_builder' => function () {
                    return $this->typePriseRepository->createQueryBuilder('tp')
                        ->where('tp.active = :active')
                        ->setParameter('active', true)
                        ->orderBy('tp.nom', 'ASC');
                },
                'label' => 'Type de prise',
                'placeholder' => 'Sélectionnez un type de prise',
                'required' => false,
                'attr' => [
                    'class' => 'form-select select-with-add',
                    'data-add-url' => '/admin/type-prise/modal-new',
                ],
                'row_attr' => ['class' => 'form-group'],
            ])
            ->add('typeVente', EntityType::class, [
                'class' => TypeVente::class,
                'choice_label' => 'nom',
                'query_builder' => function () {
                    return $this->typeVenteRepository->createQueryBuilder('tv')
                        ->where('tv.active = :active')
                        ->setParameter('active', true)
                        ->orderBy('tv.nom', 'ASC');
                },
                'label' => 'Type de vente',
                'placeholder' => 'Sélectionnez un type de vente',
                'required' => false,
                'attr' => [
                    'class' => 'form-select select-with-add',
                    'data-add-url' => '/admin/type-vente/modal-new',
                ],
                'row_attr' => ['class' => 'form-group'],
            ])
            ->add('theme', EntityType::class, [
                'class' => Theme::class,
                'choice_label' => 'nom',
                'query_builder' => function () {
                    return $this->themeRepository->createQueryBuilder('t')
                        ->where('t.active = :active')
                        ->setParameter('active', true)
                        ->orderBy('t.nom', 'ASC');
                },
                'label' => 'Thème',
                'placeholder' => 'Sélectionnez un thème',
                'required' => false,
                'attr' => [
                    'class' => 'form-select select-with-add',
                    'data-add-url' => '/admin/theme/modal-new',
                ],
                'row_attr' => ['class' => 'form-group'],
            ]);
            
        // Planches
        $builder
            ->add('planchesIndividuelles', EntityType::class, [
                'class'         => Planche::class,
                'choice_label'  => 'nom',
                'query_builder' => fn (PlancheRepository $r) =>
                    $r->createActivesByTypeQueryBuilder(PlancheUsage::INDIVIDUELLE),
                'multiple'      => true,          // ManyToMany
                'expanded'      => true,          // checkboxes (false pour <select multiple>)
                'by_reference'  => false,         // appelle add/remove (Doctrine)
                'label'         => 'Planches individuelles',
                'attr' => [
                    'class'        => 'planches-list select-with-add',
                    'data-add-url' => $this->urlGenerator->generate('admin_planche_modal_new'),
                ],
                'row_attr' => ['class' => 'form-group planches-group'],
            ])
            ->add('planchesFratries', EntityType::class, [
                'class'         => Planche::class,
                'choice_label'  => 'nom',
                'query_builder' => fn (PlancheRepository $r) =>
                    $r->createActivesByTypeQueryBuilder(PlancheUsage::FRATRIE),
                'multiple'      => true,          // ManyToMany
                'expanded'      => true,          // checkboxes (false pour <select multiple>)
                'by_reference'  => false,         // appelle add/remove (Doctrine)
                'label'         => 'Planches fratries',
                'attr' => [
                    'class'        => 'planches-list select-with-add',
                    'data-add-url' => $this->urlGenerator->generate('admin_planche_modal_new'),
                ],
                'row_attr' => ['class' => 'form-group planches-group'],
            ]);
            
        // Tarification
        $builder
            ->add('prixEcole', MoneyType::class, [
                'label' => 'Prix école (€)',
                'required' => false,
                'currency' => 'EUR',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'form-group'],
            ])
            ->add('prixParents', MoneyType::class, [
                'label' => 'Prix parents (€)',
                'required' => false,
                'currency' => 'EUR',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'form-group'],
            ]);
            
        // Commentaires
        $builder
            ->add('commentaire', TextareaType::class, [
                'label' => 'Commentaires',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'class' => 'form-control'
                ],
                'row_attr' => ['class' => 'form-group'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PriseDeVue::class,
            'validation_groups' => ['Default', 'create'],
        ]);
    }
}