<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Part;
use App\Entity\Supplier;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Part>
 */
class PartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference', TextType::class, ['label' => 'Référence'])
            ->add('name', TextType::class, ['label' => 'Nom'])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false])
            ->add('stockQuantity', IntegerType::class, ['label' => 'Quantité en stock'])
            ->add('minStockAlert', IntegerType::class, ['label' => 'Seuil d\'alerte'])
            ->add('unitPrice', MoneyType::class, ['label' => 'Prix unitaire HT', 'currency' => 'EUR'])
            ->add('supplier', EntityType::class, [
                'label' => 'Fournisseur',
                'class' => Supplier::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Aucun',
            ])
            ->add('location', TextType::class, ['label' => 'Emplacement stock', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Part::class]);
    }
}
