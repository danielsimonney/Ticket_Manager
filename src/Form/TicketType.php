<?php

namespace App\Form;

use App\Entity\Ticket;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($options["AutorizeAssign"]==false){
        $builder
            ->add('title')
            ->add('description');
            }else{
                $builder
                ->add('title')
                ->add('description')
                ->add('TicketsAssignment', EntityType::class,  [
                    'class' => User::class,
                    // 'label' => 'Assigné l'utilisateur: ',
                    'choice_label' => 'username',
                    'placeholder' => '',
                    'empty_data' => null,
                    'required' => false,
                    'expanded' => true,
                    'multiple' => true,
                ])
            ;
            }        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
            'AutorizeAssign'=>false

        ]);
    }
}
