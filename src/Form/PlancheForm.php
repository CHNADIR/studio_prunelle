<?php

namespace App\Form;

use App\Entity\Planche;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType; // Ajoutez cette ligne
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image; // Pour la validation d'image

class PlancheForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la planche',
                'attr' => ['placeholder' => 'Ex: Planche A - 1 portrait 18x24 + 4 identités']
            ])
            ->add('categorie', TextType::class, [
                'label' => 'Catégorie',
                'attr' => ['placeholder' => 'Ex: individuel, fratrie, groupe classe']
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image de la planche (JPG, PNG)',
                'mapped' => false, // Important: non mappé directement à l'entité
                'required' => false, // L'image n'est pas obligatoire
                'constraints' => [
                    new Image([
                        'maxSize' => '2M', // Taille maximale de 2Mo
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image JPG ou PNG valide.',
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Planche::class,
        ]);
    }
}
