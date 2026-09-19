<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Customer;
use App\Entity\Vehicle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VehicleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('brand', TextType::class, ['label' => 'Marque'])
            ->add('model', TextType::class, ['label' => 'Modèle'])
            ->add('registration', TextType::class, ['label' => 'Immatriculation'])
            ->add('vin', TextType::class, ['label' => 'N° de série (VIN)', 'required' => false])
            ->add('year', IntegerType::class, ['label' => 'Année'])
            ->add('mileage', IntegerType::class, ['label' => 'Kilométrage'])
            ->add('fuelType', ChoiceType::class, [
                'label' => 'Carburant',
                'choices' => [
                    'Essence' => 'essence',
                    'Diesel' => 'diesel',
                    'Hybride' => 'hybride',
                    'Électrique' => 'electrique',
                    'GPL' => 'gpl',
                ],
            ])
            ->add('engineCode', TextType::class, ['label' => 'Code moteur', 'required' => false])
            ->add('color', TextType::class, ['label' => 'Couleur', 'required' => false])
            ->add('technicalControlDate', DateType::class, [
                'label' => 'Date du dernier CT',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('owner', EntityType::class, [
                'label' => 'Propriétaire',
                'class' => Customer::class,
                'choice_label' => 'fullName',
                'placeholder' => 'Choisir un client',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Vehicle::class]);
    }
}
