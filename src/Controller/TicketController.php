<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Ticket;
use App\Entity\User;
use App\Form\MessageType;
use App\Form\TicketType;
use App\Repository\MessageRepository;
use App\Repository\TicketRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class TicketController extends AbstractController
{
    /**
     * @Route("/", name="homepage",methods={"GET"})
     */
    public function index(TicketRepository $ticketRepository): Response
    {
        $user=$this->getUser();
        $userId=$user->getId();
        if(in_array("ROLE_ADMIN", $user->getRoles())){
            $ticket=$ticketRepository->findBy(array(),['created_at' => 'DESC']);
        }else{
            $ticket=$ticketRepository->findBy(
            ["author"=>$userId],
            ['created_at' => 'DESC']
        );
        }
        
        dump($ticket);
         $assign=$user->getTicketsAssignments()->getValues();
         dump($assign);
        return $this->render('ticket/index.html.twig', [
            'controller_name' => 'TicketController',
            'tickets'=>$ticket,
            'ticketAssigns'=>$assign
        ]);
    }

    /**
     * @Route("/ticket/new", name="new_ticket", methods={"GET","POST"})
     */

     public function new(Request $request,ObjectManager $manager){
        $ticket=new Ticket;
        $ticket->setAuthor($this->getUser());
        $user=$this->getUser();
        if(in_array("ROLE_ADMIN", $user->getRoles())){
            $form=$this->createForm(TicketType::class,$ticket,array(
                'AutorizeAssign' => true
            ));
        }else{
            $form=$this->createForm(TicketType::class,$ticket);

        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($ticket);
            $manager->flush();
            $id=$ticket->getId();
            return $this->redirectToRoute('ticket_show',['id'=>$id]);
        }
        return $this->render('ticket/new.html.twig', [
            'form' => $form->createView()

        ]);


     }

    /**
     * @Route("/ticket/{id}", name="ticket_show", methods={"GET","POST"})
     */

     public function show(Ticket $ticket,MessageRepository $MessageRepo,$id,ObjectManager $manager,Request $request): Response
     {
        if($this->hasPermission($id)){

        
        $newMessage=new Message();
        $newMessage->setUser($this->getUser());
        $newMessage->setTicket($ticket);
        $form=$this->createForm(MessageType::class,$newMessage);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($newMessage);
            $manager->flush();
            return $this->redirectToRoute('ticket_show',['id'=>$id]);
        }

         $messages=$MessageRepo->findBy(
             ["ticket"=>$id],
             ['created_at' => 'ASC']
         );
         dump($messages);
         dump($ticket->getTicketsAssignment());


        return $this->render('ticket/show.html.twig', [
            'ticket'=>$ticket,
            'messages'=>$messages,
            'form' => $form->createView()

        ]);

        }else{
            $this->addFlash('warning', 'You dont have the rigths for this ticket');
        return $this->redirectToRoute('homepage');
        }
    }
        
     

     /**
      * function qui va me servir à vérifier si l'utilisateur courant a le droit d'avoir accès au ticket ou non.
      * @return boolean
      */

     private function hasPermission($id){
         $user=$this->getUser();
        if(in_array("ROLE_ADMIN", $user->getRoles())){
            return true;
        }
        foreach ($this->getUser()->getTicketsAssignments() as $key => $value) {
           if($value->getId()==$id){
               return true;
           }
        }

        foreach ($this->getUser()->getTickets() as $key => $value) {
            if($value->getId()==$id){
                return true;
            }
        }
        return false;
     }
}


