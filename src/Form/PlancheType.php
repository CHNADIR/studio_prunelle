<?php
// src/Form/PlancheType.php
namespace App\Form;

use App\Entity\Planche;
use App\Enum\PlancheUsage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlancheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la planche',
                'attr' => [
                    'placeholder' => 'Ex: Portrait A4, Photo 10x15...',
                    'class' => 'form-control'
                ]
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'Individuelle' => PlancheUsage::INDIVIDUELLE,
                    'Fratrie' => PlancheUsage::FRATRIE,
                    'Seule' => PlancheUsage::SEULE,
                ],
                'choice_value' => 'value',
                'attr' => ['class' => 'form-select']
            ])
            ->add('usage', ChoiceType::class, [
                'label' => 'Usage',
                'choices' => [
                    'Seule (à l\'unité)' => 'SEULE', 
                    'Incluse (dans un pack)' => 'INCLUSE'
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('formats', TextareaType::class, [
                'label' => 'Formats disponibles',
                'help' => 'Saisir un JSON valide ex : ["10x15","portrait"] ou une liste séparée par virgules',
                'attr' => [
                    'rows' => 3,
                    'placeholder' => '["10x15", "portrait", "13x18"]',
                    'class' => 'form-control'
                ]
            ])
            ->add('prixEcole', MoneyType::class, [
                'label' => 'Prix école',
                'currency' => 'EUR',
                'attr' => [
                    'step' => '0.01',
                    'min' => '0',
                    'class' => 'form-control'
                ]
            ])
            ->add('prixParents', MoneyType::class, [
                'label' => 'Prix parents',
                'currency' => 'EUR',
                'attr' => [
                    'step' => '0.01',
                    'min' => '0.01',
                    'class' => 'form-control'
                ]
            ])
            ->add('actif', CheckboxType::class, [
                'label' => 'Planche active',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
                'label_attr' => ['class' => 'form-check-label']
            ]);

        // Ajouter un event listener pour traiter les formats
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            
            if (isset($data['formats']) && is_string($data['formats'])) {
                // Tenter de décoder le JSON
                $formats = json_decode($data['formats'], true);
                
                // Si ce n'est pas du JSON valide, traiter comme une liste séparée par virgules
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $formats = array_map('trim', explode(',', $data['formats']));
                    $formats = array_filter($formats); // Supprimer les éléments vides
                }
                
                $data['formats'] = $formats;
                $event->setData($data);
            }
        });

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $planche = $event->getData();
            $form = $event->getForm();
            
            if ($planche && $planche->getFormats()) {
                // Convertir le tableau en JSON pour l'affichage
                $formatsJson = json_encode($planche->getFormats(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                $form->get('formats')->setData($formatsJson);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Planche::class,
        ]);
    }
}
