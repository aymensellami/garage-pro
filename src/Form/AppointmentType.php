<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Appointment;
use App\Entity\Vehicle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Appointment>
 */
class AppointmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('vehicle', EntityType::class, [
                'label' => 'Véhicule',
                'class' => Vehicle::class,
                'choice_label' => static fn (Vehicle $v) => $v->getBrand().' '.$v->getModel().' ('.$v->getRegistration().')',
            ])
            ->add('scheduledAt', DateTimeType::class, [
                'label' => 'Date et heure',
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('duration', IntegerType::class, ['label' => 'Durée estimée (min)'])
            ->add('reason', TextType::class, ['label' => 'Motif'])
            ->add('notes', TextareaType::class, ['label' => 'Notes', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Appointment::class]);
    }
}
