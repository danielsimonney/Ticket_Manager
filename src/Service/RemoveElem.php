<?php

namespace App\Service;

class RemoveElem
{
    public function removeLinkedTickets($listTickets,$user){

        foreach ($listTickets as $key => $ticket) {
            foreach ($user->getTicketsAssignments() as $ticketAssign) {
              if($ticket==$ticketAssign){
            
                unset($listTickets[$key]);
              }
            }
            foreach ($user->getTickets() as $authorTicket) {
                if($ticket==$authorTicket){
                  
                    unset($listTickets[$key]);
                  }
            }
        }
        return $listTickets;
    }
    
    public function removeLinkedUsers($listUsers,$ticket){
        foreach ($listUsers as $key => $user) {
            foreach ($ticket->getTicketsAssignment() as $mykey => $ticketAssign) {
              if($user==$ticketAssign){
                
                unset($listUsers[$key]);
              }
            }
            if($ticket->getAuthor()==$user){
                unset($listUsers[$key]);
            }
            
        }
        return $listUsers;
    }


}
