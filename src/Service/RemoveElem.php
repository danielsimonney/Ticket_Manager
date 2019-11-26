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
        }
        return $listTickets;
    }

}
