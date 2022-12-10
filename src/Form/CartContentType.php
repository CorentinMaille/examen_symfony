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
                'label' => 'label.quantity',
                'attr' => [
                    'class' => 'text-center w-25 mx-auto',
                    'min' => 0,
                    'value' => 1
                ],
            ])
            ->add('product', HiddenType::class)
            ->add('submit', SubmitType::class, [
                'label' => 'cart.button.addTo',
                'attr' => [
                    'class' => 'text-center mt-3 btn btn-success w-25'
                ],
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
