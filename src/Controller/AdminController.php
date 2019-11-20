<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\Message;
use App\Entity\User;
use App\Form\AssignType;
use App\Form\MessageType;
use App\Form\TicketType;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    /**
     * @Route("admin", name="admin_index")
     */
    public function index(UserRepository $user){
        $users=$user->findAll();
        return $this->render('admin/index.html.twig', [
            'users' => $users,
        ]);
    }


    /**
     * @Route("admin/user/{id}", name="user_profile")
     */
    public function UserAdmin(Request $request,User $user): Response
    {
        return $this->render('admin/user/index.html.twig', [
            'user' => $user,
        ]);
         
    }



    /**
     * @Route("admin/ticket/edit/{id}", name="ticket_edit")
     */
    public function editTicket(Ticket $ticket,Request $request,UserRepository $userRepo): Response
    {
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('homepage');
        }

        $listUsers=$userRepo->findAll();

        foreach ($listUsers as $key => $user) {
            foreach ($ticket->getTicketsAssignment() as $mykey => $ticketAssign) {
              if($user==$ticketAssign){
                //   dump($user->getLastname());
                unset($listUsers[$key]);
              }
            }
            if($ticket->getAuthor()==$user){
                unset($listUsers[$key]);
            }
            
        }
        
        

        return $this->render('admin/ticket/edit.html.twig', [
            'ticket' => $ticket, 
            'form' => $form->createView(),
            'users'=>$listUsers
        ]);
    }

    /**
     * @Route("admin/ticket/supress/{id}", name="ticket_supress",methods={"DELETE"})
     */
    public function supressTicket(Request $request,Ticket $ticket): Response
    {
        
        if ($this->isCsrfTokenValid('delete'.$ticket->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($ticket);
            $entityManager->flush();
        }
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("admin/message/edit/{id}", name="message_edit")
     */
    public function editMessage(Request $request, Message $message): Response
    {
        $form = $this->createForm(MessageType::class, $message);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('homepage');
        }

        return $this->render('admin/message/edit.html.twig', [
            'message' => $message,
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("admin/message/supress/{id}", name="message_supress",methods={"DELETE","GET"})
     */
    public function suppress_message(Request $request, Message $message): Response
    {
        $ticketId=$message->getTicket()->getId();
        if ($this->isCsrfTokenValid('delete'.$message->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($message);
            $entityManager->flush();
        }else{
            die("bouhou");
        }

        return $this->redirectToRoute('ticket_show',['id'=>$ticketId]);
    }

    /**
     * @Route("admin/assign/{id}", name="assign_user")
     */
    public function AssignView(Request $request,User $user,TicketRepository $ticketsrepo): Response
    {
        $listTickets=$ticketsrepo->findAll();
        foreach ($listTickets as $key => $ticket) {
            foreach ($user->getTicketsAssignments() as $key => $ticketAssign) {
              if($ticket==$ticketAssign){
                unset($listTickets[$key]);
              }
            }
            foreach ($user->getTickets() as $key => $authorTicket) {
                if($ticket==$authorTicket){
                    unset($listTickets[$key]);
                  }
            }
        }
        
        return $this->render('admin/user/assign.html.twig', [
            'user' => $user,
            'tickets'=>$listTickets
        ]);
         
    }

    /**
    * @Route("admin/user/add/{id_ticket}/{id_user}", name="add_assignment",methods={"DELETE","GET"})
    */
    public function add_assignment(Request $request,TicketRepository $tr,UserRepository $ur, $id_user,$id_ticket): Response
    {
     $myTicket=$tr->findOneBy(array("id"=>$id_ticket));
     $myUser=$ur->findOneBy(array("id"=>$id_user));
     
     if ($this->isCsrfTokenValid('add'.$myTicket->getId(), $request->request->get('_token'))) {
         $entityManager = $this->getDoctrine()->getManager();
         
      $myTicket->addTicketsAssignment($myUser);
      $this->addFlash(
        'success',
        'You have assign'.$myUser->getFirstname().' '.$myUser->getLastname().' to the ticketv' .$myTicket->getTitle().'!'
    );
         $entityManager->flush();
     }
     return $this->redirectToRoute('user_profile',['id'=>$id_user]);
    }


     /**
    * @Route("admin/ticket/removeAssign/{id_ticket}/{id_user}", name="remove_assignment",methods={"DELETE","GET"})
    */
   public function remove_assign(Request $request,TicketRepository $tr,UserRepository $ur, $id_user,$id_ticket): Response
   {
    $myTicket=$tr->findOneBy(array("id"=>$id_ticket));
    $myUser=$ur->findOneBy(array("id"=>$id_user));
    
    if ($this->isCsrfTokenValid('delete'.$myTicket->getId(), $request->request->get('_token'))) {
        $entityManager = $this->getDoctrine()->getManager();
        
     $myTicket->removeTicketsAssignment($myUser);
        $entityManager->flush();
    }
    $users=$ur->findAll();


    return $this->render('admin/index.html.twig', [
        'users' => $users,
    ]);
   }

    /**
     * @Route("admin/message/done/{id}", name="ticket_resolve")
     */

     public function resolveTicket(Request $request, Ticket $ticket,ObjectManager $em): Response
        {
            $ticket->setStatus("done");
            $em->persist($ticket);
            $em->flush();
            return $this->redirectToRoute('ticket_show',['id'=>$ticket->getId()]);
     }

     /**
     * @Route("admin/message/open/{id}", name="ticket_reopen")
     */

     public function reopenTicket(Request $request, Ticket $ticket,ObjectManager $em): Response
        {
            $ticket->setStatus("en cours");
            $em->persist($ticket);
            $em->flush();
            return $this->redirectToRoute('ticket_show',['id'=>$ticket->getId()]);
     }

    //  /**
    //  * @Route("admin/message/open/{id}", name="ticket_reopen")
    //  */
    // public function grantedAdmin(): Response
    // {
        
    // }

}