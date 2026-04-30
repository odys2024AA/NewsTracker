<?php

namespace App\Form;

use App\Entity\Asset;
use App\Entity\Transaction;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TransactionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('symbol', TextType::class, [
                'mapped' => false,
                'label' => 'Symbol',
            ])
            ->add('asset_type', ChoiceType::class, [
                'mapped' => false,
                'choices' => [
                    'Stock' => 'STOCK',
                    'Crypto' => 'CRYPTO',
                    'ETF' => 'ETF',
                    'Fund' => 'FUND',
                ],
                'label' => 'Asset Type',
            ])
            ->add('transaction_type', ChoiceType::class, [
                'choices' => [
                    'Buy' => 'BUY',
                    'Sell' => 'SELL',
                ],
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('price', NumberType::class)
            ->add('quantity', NumberType::class)
            ->add('fee', NumberType::class, [
                'required' => false,
            ])
            ->add('trading_place', TextType::class, [
                'mapped' => false,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
        ]);
    }
}
