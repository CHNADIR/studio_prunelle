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
        $canEditFull = $options['can_edit_full'];

        $builder
            ->add('date', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date de la séance',
                'html5' => true,
                'disabled' => !$canEditFull,
            ])
            ->add('photographe', TextType::class, [
                'label' => 'Photographe assigné',
                'disabled' => !$canEditFull,
            ])
            ->add('nombreEleves', IntegerType::class, [
                'label' => 'Nombre d\'élèves prévus',
                'disabled' => !$canEditFull,
            ])
            ->add('nombreClasses', IntegerType::class, [
                'label' => 'Nombre de classes concernées',
                'disabled' => !$canEditFull,
            ])
            ->add('ecole', EntityType::class, [
                'class' => Ecole::class,
                'choice_label' => 'nom',
                'label' => 'École',
                'placeholder' => 'Sélectionnez une école',
                'query_builder' => function (EcoleRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->orderBy('e.nom', 'ASC');
                },
                'disabled' => !$canEditFull,
            ])
            ->add('typePrise', EntityType::class, [
                'class' => TypePrise::class,
                'choice_label' => 'nom',
                'label' => 'Type de prise de vue',
                'placeholder' => 'Sélectionnez un type',
                'disabled' => !$canEditFull,
            ])
            ->add('theme', EntityType::class, [
                'class' => Theme::class,
                'choice_label' => 'nom',
                'label' => 'Thème',
                'placeholder' => 'Sélectionnez un thème',
                'disabled' => !$canEditFull,
            ])
            ->add('typeVente', EntityType::class, [
                'class' => TypeVente::class,
                'choice_label' => 'nom',
                'label' => 'Type de vente',
                'placeholder' => 'Sélectionnez un type de vente',
                'disabled' => !$canEditFull,
            ])
            ->add('planchesIndividuel', EntityType::class, [
                'class' => Planche::class,
                'choice_label' => 'nom',
                'label' => 'Planches individuelles proposées',
                'multiple' => true,
                'expanded' => true,
                'query_builder' => function (PlancheRepository $pr) {
                    return $pr->createQueryBuilder('p')
                        ->where('p.categorie = :categorie')
                        ->setParameter('categorie', Planche::CATEGORIE_INDIVIDUEL)
                        ->orderBy('p.nom', 'ASC');
                },
                'disabled' => !$canEditFull,
            ])
            ->add('planchesFratrie', EntityType::class, [
                'class' => Planche::class,
                'choice_label' => 'nom',
                'label' => 'Planches fratrie proposées',
                'multiple' => true,
                'expanded' => true,
                'query_builder' => function (PlancheRepository $pr) {
                    return $pr->createQueryBuilder('p')
                        ->where('p.categorie = :categorie')
                        ->setParameter('categorie', Planche::CATEGORIE_FRATRIE)
                        ->orderBy('p.nom', 'ASC');
                },
                'disabled' => !$canEditFull,
            ])
            ->add('prixEcole', MoneyType::class, [
                'label' => 'Prix facturé à l\'école',
                'currency' => 'EUR',
                'disabled' => !$canEditFull,
            ])
            ->add('prixParent', MoneyType::class, [
                'label' => 'Prix de vente conseillé aux parents',
                'currency' => 'EUR',
                'disabled' => !$canEditFull,
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Commentaires / Notes internes',
                'required' => false,
                'attr' => ['rows' => 4],
                // Le commentaire est toujours éditable
            ])
            ->add('frequence', TextType::class, [
                'label' => 'Fréquence',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: Annuelle, Ponctuelle'],
                'disabled' => !$canEditFull,
            ])
            ->add('baseDeDonneeUtilisee', TextType::class, [
                'label' => 'Base de données utilisée',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: Oui, Non, Partielle'],
                'disabled' => !$canEditFull,
            ])
            ->add('jourDecharge', TextType::class, [
                'label' => 'Jour de décharge',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: Lundi Matin'],
                'disabled' => !$canEditFull,
            ])
            ->add('endroitInstallation', TextType::class, [
                'label' => 'Endroit de l\'installation',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: Préau, Salle polyvalente'],
                'disabled' => !$canEditFull,
            ])
        ;

        // La logique pour désactiver les champs si can_edit_full est false est maintenant
        // directement appliquée avec l'option 'disabled' sur chaque champ.
        // La section if ($options['can_edit_full']) { ... } n'est plus nécessaire
        // si tous les champs sauf 'commentaire' doivent être désactivés.
        // Si certains champs devaient rester éditables même pour un photographe (en plus du commentaire),
        // il faudrait ajuster la logique 'disabled' champ par champ.
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PriseDeVue::class,
            'can_edit_full' => false,
        ]);
        $resolver->setAllowedTypes('can_edit_full', 'bool');
    }
}
