<?php

namespace App\Controller;

use App\Form\EditProfileType;
use App\Form\ResetPasswordType;
use App\Service\UploaderHelper;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class AccountController extends AbstractController
{
    /**
     * @Route("/account", name="account")
     */
    public function index()
    {
        $user=$this->getUser();

        return $this->render('account/index.html.twig', [
            'controller_name' => 'AccountController',
        ]);
    }

    

    /**
     * @Route("/account/change", name="passwordChange")
     */

    public function editAction(Request $request,UserPasswordEncoderInterface $passwordEncoder)
    {
    	$em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $form = $this->createForm(ResetPasswordType::class);

    	$form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() ) {
            $passwordEncoder = $passwordEncoder;
            $oldPassword = $request->request->get('reset_password')["oldPassword"];

            // Si l'ancien mot de passe est bon
            if ($passwordEncoder->isPasswordValid($user, $oldPassword)) {
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
            
                
                $em->persist($user);
                $em->flush();

                $this->addFlash('notice', 'Votre mot de passe à bien été changé !');

                return $this->redirectToRoute('account');
            } else {
                $form->addError(new FormError('Ancien mot de passe incorrect'));
            }
        }
    	
    	return $this->render('account/edit.html.twig', array(
    		'form' => $form->createView(),
    	));
    }


    /**
     * @Route("/account/addImage", name="addImage")
     */
    public function edit( Request $request, ObjectManager $em, UploaderHelper $uploaderHelper)
    {
        
        $user=$this->getUser();
        $form = $this->createForm(EditProfileType::class,$user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $brochureFile */
            $image = $form['ProfileImage']->getData();
            if ($image) {
                $imageName = $uploaderHelper->upload($image);
                $user->setProfileImage($imageName);
            }
            $user->setEmail($form['email']->getData());
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'user Updated! Inaccuracies squashed!');
            return $this->redirectToRoute('homepage');
        }
        return $this->render('account/image.html.twig', [
            'form' => $form->createView()
        ]);
    }

}



    