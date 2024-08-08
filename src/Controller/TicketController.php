<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Form\TicketFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Ticket;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

class TicketController extends AbstractController 
{
    #[Route('/tickets', name: 'app_show_tickets')]
    public function show(EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $security->getUser();

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        $tickets = $entityManager->getRepository(Ticket::class)->findBy(['user' => $user]);

        return $this->render('pages/tickets/showTickets.html.twig', [
            'controller_name' => 'TicketController',
            'tickets' => $tickets,
        ]);
    }

    #[Route('/ticket-creation', name: 'app_create_ticket')]
    public function create(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $ticket = new Ticket();
        $form = $this->createForm(TicketFormType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $security->getUser();
            if ($user) {
                $ticket->setUser($user);
            }
            $ticket->setCreatedAt(new \DateTimeImmutable());
            $ticket->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($ticket);
            $entityManager->flush();

            return $this->redirectToRoute('app_show_tickets');
        }

        return $this->render('pages/tickets/createTicket.html.twig', [
            'controller_name' => 'TicketController',
            'TicketForm' => $form->createView(),
        ]);
    }

    // #[Route('/ticket-modification/{id}', 'app_ticket_modifier', methods:['GET', 'POST'])]
    // #[IsGranted('ROLE_ADMIN')]
    // {

    // }
}
