<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Intervention;
use App\Entity\User;
use App\Entity\Vehicle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Intervention>
 */
class InterventionType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder
            ->add('vehicle', EntityType::class, [
                'label' => 'Véhicule',
                'class' => Vehicle::class,
                'choices' => $options['vehicles'],
                'choice_label' => static fn (Vehicle $v) => $v->getBrand().' '.
                    $v->getModel().' ('.
                    $v->getRegistration().')',
                'placeholder' => 'Sélectionnez un véhicule',
            ])

           ->add('mechanic', EntityType::class, [
               'label' => 'Mécanicien',
               'class' => User::class,
               'choice_label' => 'fullName',
               'required' => false,
               'placeholder' => 'Sélectionnez un mécanicien',
               'query_builder' => static function ($er) {
                   return $er->createQueryBuilder('u')
                       ->where('u.roles LIKE :role')
                       ->setParameter('role', '%ROLE_MECHANIC%')
                       ->orderBy('u.lastName', 'ASC');
               },
           ])

            ->add('description', TextareaType::class, [
                'label' => 'Description',
            ])

            ->add('operations', ChoiceType::class, [
                'label' => 'Opérations',
                'choices' => [
                    'Vidange' => 'Vidange',
                    'Freins' => 'Freins',
                    'Pneus' => 'Pneus',
                    'Courroie distribution' => 'Courroie distribution',
                    'Diagnostic' => 'Diagnostic',
                    'Climatisation' => 'Climatisation',
                    'Batterie' => 'Batterie',
                    'Contrôle technique' => 'Contrôle technique',
                ],
                'multiple' => true,
                'expanded' => true,
            ])

            ->add('estimatedCost', MoneyType::class, [
                'label' => 'Coût estimé',
                'currency' => 'EUR',
            ])

            ->add('scheduledAt', DateTimeType::class, [
                'label' => 'Date prévue',
                'widget' => 'single_text',
                'html5' => true,
            ])

            ->add('notes', TextareaType::class, [
                'label' => 'Notes internes',
                'required' => false,
            ]);
    }

    public function configureOptions(
        OptionsResolver $resolver,
    ): void {
        $resolver->setDefaults([
            'data_class' => Intervention::class,
            'vehicles' => [],
        ]);

        $resolver->setAllowedTypes(
            'vehicles',
            'array'
        );
    }
}