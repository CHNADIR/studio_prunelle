<?php

namespace App\Form;

use App\Entity\Ecole;
use App\Entity\Planche;
use App\Entity\PriseDeVue;
use App\Entity\Theme;
use App\Entity\TypePrise;
use App\Entity\TypeVente;
use App\Repository\EcoleRepository;
use App\Repository\PlancheRepository; // Make sure this repository exists
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PriseDeVueForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date de la séance',
                'html5' => true, // Use HTML5 date picker if browser supports
            ])
            ->add('photographe', TextType::class, [
                'label' => 'Photographe assigné',
            ])
            ->add('nombreEleves', IntegerType::class, [
                'label' => 'Nombre d\'élèves prévus',
            ])
            ->add('nombreClasses', IntegerType::class, [
                'label' => 'Nombre de classes concernées',
            ])
            ->add('ecole', EntityType::class, [
                'class' => Ecole::class,
                'choice_label' => 'nom',
                'label' => 'École',
                'placeholder' => 'Sélectionnez une école',
                'query_builder' => function (EcoleRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->where('e.active = :active')
                        ->setParameter('active', true)
                        ->orderBy('e.nom', 'ASC');
                },
            ])
            ->add('typePrise', EntityType::class, [
                'class' => TypePrise::class,
                'choice_label' => 'nom',
                'label' => 'Type de prise de vue',
                'placeholder' => 'Sélectionnez un type',
            ])
            ->add('theme', EntityType::class, [
                'class' => Theme::class,
                'choice_label' => 'nom', // Changed from 'id'
                'label' => 'Thème',
                'placeholder' => 'Sélectionnez un thème',
            ])
            ->add('typeVente', EntityType::class, [
                'class' => TypeVente::class,
                'choice_label' => 'nom', // Changed from 'id'
                'label' => 'Type de vente',
                'placeholder' => 'Sélectionnez un type de vente',
            ])
            ->add('planchesIndividuel', EntityType::class, [
                'class' => Planche::class,
                'choice_label' => 'nom', // Changed from 'id'
                'label' => 'Planches individuelles proposées',
                'multiple' => true,
                'expanded' => true, // Renders as checkboxes
                'query_builder' => function (PlancheRepository $pr) { // Ensure PlancheRepository exists
                    return $pr->createQueryBuilder('p')
                        ->where('p.categorie LIKE :cat')
                        ->setParameter('cat', '%individuel%')
                        ->orderBy('p.nom', 'ASC');
                },
            ])
            ->add('planchesFratrie', EntityType::class, [
                'class' => Planche::class,
                'choice_label' => 'nom', // Changed from 'id'
                'label' => 'Planches fratrie proposées',
                'multiple' => true,
                'expanded' => true, // Renders as checkboxes
                'query_builder' => function (PlancheRepository $pr) { // Ensure PlancheRepository exists
                    return $pr->createQueryBuilder('p')
                        ->where('p.categorie LIKE :cat')
                        ->setParameter('cat', '%fratrie%')
                        ->orderBy('p.nom', 'ASC');
                },
            ])
            ->add('prixEcole', MoneyType::class, [
                'label' => 'Prix facturé à l\'école',
                'currency' => 'EUR',
            ])
            ->add('prixParent', MoneyType::class, [
                'label' => 'Prix de vente conseillé aux parents',
                'currency' => 'EUR',
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Commentaires / Notes internes',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PriseDeVue::class,
        ]);
    }
}
