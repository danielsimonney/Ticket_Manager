<?php

namespace App\DataFixtures;

use App\Entity\Message;
use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    /**
     * Encodeur de mot de passe
     *
     * @param UserPasswordEncoderInterface $encoder
     */


    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }


    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
        $faker = Factory::create("fr_FR");

        $userList = [];
        $ticketList = [];

        for ($u = 0; $u < 8; $u++) {
            $user=new User;
            if($u==1 || $u==2){
                $role=["ROLE_ADMIN"];
            }else{
                $role=[""];
            }

            $hash=$this->encoder->encodePassword($user, "password");
            $user->setEmail($faker->email)
            ->setFirstname($faker->firstName())
                    ->setLastname($faker->lastName)
                ->setPassword($hash)
                ->setRoles($role);
                
            $userList[] = $user;
            $manager->persist($user);

            for ($c = 0; $c < mt_rand(1, 3); $c++) {
                $ticket=new Ticket;
                $ticket->setAuthor($user)
                        ->setCreatedAt($faker->dateTime)
                        ->setDescription($faker->text(70))
                        ->setTitle($faker->text(15));
                        $manager->persist($ticket);

                $ticketList[] = $ticket;
                for ($m=0; $m < mt_rand(3,5) ; $m++) { 
                    $message=new Message;
                    $message->setCreatedAt($faker->dateTime)
                            ->setContent($faker->text(300))
                            ->setUser($user)
                            ->setTicket($ticket);
                            $manager->persist($message);

                }

            }

        }


        foreach ($ticketList as $singleTicket){
            $firstUserToAssign = $userList[mt_rand(0,3)];
            $secondUserToAssign = $userList[mt_rand(3,7)];

            $singleTicket->addTicketsAssignment($firstUserToAssign);
            $singleTicket->addTicketsAssignment($secondUserToAssign);
        }
        $manager->flush();
    }
}
