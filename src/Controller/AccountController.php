<?php

namespace App\Controller;


use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\ResetPasswordType;
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


    // /**
    //  * @Route("/account/change", name="passwordChange")
    //  */
    // public function edit(){

    // }

}



