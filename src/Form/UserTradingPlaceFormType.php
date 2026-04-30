<?php

namespace App\Form;

use App\Entity\UserTradingPlace;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserTradingPlaceFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startingCash', NumberType::class, [
                'label' => 'Starting cash',
                'scale' => 2,
                'help' => 'The initial cash balance at this trading place. Chaning the re-bases the entire history.'
            ])
            ->add('taxRate', NumberType::class, [
                'label' => 'Tax rate (decimal, e.g. 0.25 for 25%)',
                'scale' => 4,
                'help'  => 'German Abgeltungsteuer ≈ 0.25. Austrian KESt ≈ 0.275.',
            ])
            ->add('taxFreePot', NumberType::class, [
                'label' => 'Annual tax-free amount',
                'scale' => 2,
                'help'  => 'Sparerpauschbetrag (DE) ≈ 1000. KESt has no general exemption (set 0).',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserTradingPlace::class,
        ]);
    }
}
