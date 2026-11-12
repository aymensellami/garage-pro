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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Vehicle>
 */
class VehicleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('brand', TextType::class, ['label' => 'Marque', 'trim' => true])
            ->add('model', TextType::class, ['label' => 'Modèle', 'trim' => true])
            ->add('registration', TextType::class, [
                'label' => 'Immatriculation',
                'trim' => true,
                'attr' => ['placeholder' => '123 TUN 4567'],
            ])
            ->add('vin', TextType::class, [
                'label' => 'N° de série (VIN)',
                'required' => false,
                'trim' => true,
                'empty_data' => null,
            ])
            ->add('year', IntegerType::class, ['label' => 'Année'])
            ->add('mileage', IntegerType::class, [
                'label' => 'Kilométrage',
                'attr' => ['min' => 0],
            ])
            ->add('fuelType', ChoiceType::class, [
                'label' => 'Carburant',
                'choices' => [
                    'Essence' => 'Essence',
                    'Diesel' => 'Diesel',
                    'Hybride' => 'Hybride',
                    'Électrique' => 'Électrique',
                    'GPL' => 'GPL',
                ],
            ])
            ->add('engineCode', TextType::class, [
                'label' => 'Code moteur',
                'required' => false,
                'trim' => true,
                'empty_data' => null,
            ])
            ->add('color', TextType::class, [
                'label' => 'Couleur',
                'required' => false,
                'trim' => true,
                'empty_data' => null,
            ])
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

        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event): void {
            $data = $event->getData();
            if (isset($data['registration']) && \is_string($data['registration'])) {
                $data['registration'] = \mb_strtoupper(
                    (string) \preg_replace('/\s+/', ' ', \trim($data['registration']))
                );
                $event->setData($data);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Vehicle::class]);
    }
}
