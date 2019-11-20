<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('oldPassword', PasswordType::class, array(
            'mapped' => false
        ))
        ->add('plainPassword', RepeatedType::class, array(
            'type' => PasswordType::class,
            'first_options'  => ['label' => 'mot de passe'],
            'second_options' => ['label' => 'Confirmation du mot de passe'],
            'invalid_message' => 'Les deux mots de passe doivent être identiques',
            'required' => true,
        ))
        ->add('submit', SubmitType::class, array(
            'attr' => array(
                'class' => 'btn btn-primary btn-block'
            )
        ))
    ;
    }
}
