<?php

namespace App\Form;

use App\Entity\User;
use PharIo\Manifest\Email;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, ['label' => 'user.firstname'])
            ->add('lastname', TextType::class, ['label' => 'user.lastname'])
            ->add('email', EmailType::class, ['label' => 'user.email'])
            ->add('password', PasswordType::class, [
                'label' => 'user.password',
                'attr' => [
                    'value' => '',
                ]
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'w-50 btn btn-success text-center',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
