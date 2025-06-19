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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlancheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la planche',
            ])
            ->add('type', ChoiceType::class, [
                'choices' => array_combine(
                    array_map(fn($c) => ucfirst($c->value), PlancheUsage::cases()),
                    PlancheUsage::cases()
                ),
                'choice_value' => 'value',
            ])
            ->add('usage', ChoiceType::class, [
                'choices'  => ['Seule' => 'SEULE', 'Incluse dans un pack' => 'INCLUSE'],
                'label'    => 'Usage',
            ])
            ->add('formats', TextareaType::class, [
                'help' => 'JSON valide (ex : ["10x15","portrait"]) ou liste séparée par des virgules',
            ])
            ->add('prixEcole', MoneyType::class, ['currency' => 'EUR', 'label' => 'Prix école'])
            ->add('prixParents', MoneyType::class, ['currency' => 'EUR', 'label' => 'Prix parents'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Planche::class,
        ]);
    }
}
