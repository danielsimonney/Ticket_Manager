<?php

namespace App\Form;

use App\Entity\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        if($options["upload"]==false){
            $builder
            ->add('content',TextareaType::class,[
                "label"=>"new message","attr"=>[
                    'placeholder'=>"Type your message here !"
                ]
            ])
        ;
                }else{
                    $builder 
                    ->add('content',TextareaType::class,[
                        "label"=>"new message","attr"=>[
                            'placeholder'=>"Type your message here !"
                        ]
                    ])
                    ->add('ressource', FileType::class, [
                        'label' => 'Wanna upload some file to describe better where is your problem ??',
        
                        // unmapped means that this field is not associated to any entity property
                        'mapped' => false,
        
                        // make it optional so you don't have to re-upload the PDF file
                        // everytime you edit the Product details
                        'required' => false,
        
                        // unmapped fields can't define their validation using annotations
                        // in the associated entity, so you can use the PHP constraint classes
                        'constraints' => [
                            new File([
                                'maxSize' => '3000k',
                                'mimeTypes' => [
                                    'image/jpeg',
                                    'image/png',
                                    'application/pdf',
                                    'application/x-pdf'
                                ],
                                'mimeTypesMessage' => 'Please upload a valid image',
                            ])
                        ],
                    ])

                ;
                }     
        
                

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
            'upload'=>false
        ]);
    }
}
