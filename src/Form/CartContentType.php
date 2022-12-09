<?php

namespace App\Form;

use App\Entity\CartContent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CartContentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity', null, [
                'attr' => [
                    'class' => 'text-center',
                    'min' => 0,
                    'value' => 1
                ]
            ])
            ->add('product', HiddenType::class)
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'text-center mt-3 btn btn-success w-25'
                ],
                'label' => 'add to cart'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CartContent::class,
        ]);
    }
}
