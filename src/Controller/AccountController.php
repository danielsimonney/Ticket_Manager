<?php

namespace App\Controller;

use App\Form\EditProfileType;
use App\Form\ResetPasswordType;
use App\Service\UploaderHelper;
use Symfony\Component\Form\FormError;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class AccountController extends AbstractController
{
    private $targetDirectory;

    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

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

        // dump($form['ProfileImage']->getData());
        

        if ($form->isSubmitted() && $form->isValid()) {

            
            /** @var UploadedFile $brochureFile */
            $image = $form['ProfileImage']->getData();
            if ($image) {
                if($user->getProfileImage()!=null){
                $fsObject = new Filesystem(); 
                $str="uploads/".$user->getProfileImage();
                $fsObject->remove($str);
                }
                

                $imageName = $uploaderHelper->upload($image);
                $user->setProfileImage($imageName);
            }
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'user Updated!');
            return $this->redirectToRoute('homepage');
        }
        return $this->render('account/image.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

}



    