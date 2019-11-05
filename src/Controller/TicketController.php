<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\User;
use App\Repository\MessageRepository;
use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class TicketController extends AbstractController
{
    /**
     * @Route("/ticket", name="homepage")
     */
    public function index(TicketRepository $ticketRepository)
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
     * @Route("/ticket/{id}", name="ticket_show")
     */

     public function show(Ticket $ticket,MessageRepository $MessageRepo,$id){
         
         $messages=$MessageRepo->findBy(
             ["ticket"=>$id],
             ['created_at' => 'DESC']
         );
         dump($messages);
         dump($ticket);


        return $this->render('ticket/show.html.twig', [
            'ticket'=>$ticket,
            'messages'=>$messages

        ]);
        
     }
}


