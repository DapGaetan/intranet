<?php

namespace App\Controller;

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Form\TicketFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Ticket;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TicketController extends AbstractController
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/tickets', name: 'app_show_tickets')]
    #[IsGranted('ROLE_USER')]
    public function show(EntityManagerInterface $entityManager): Response
    {
        $user = $this->security->getUser();

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        $tickets = $entityManager->getRepository(Ticket::class)->findBy(['user' => $user]);

        return $this->render('pages/tickets/showTickets.html.twig', [
            'controller_name' => 'TicketController',
            'tickets' => $tickets,
        ]);
    }

    #[Route('/ticket/{id}', name: 'app_ticket_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function showTicket(Ticket $ticket): Response
    {
        return $this->render('pages/tickets/showTicket.html.twig', [
            'ticket' => $ticket,
        ]);
    }


    #[Route('/ticket-creation', name: 'app_create_ticket')]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $em, UserRepository $userRepo): Response
    {
        $ticket = new Ticket();
        $form = $this->createForm(TicketFormType::class, $ticket, ['is_edit' => false]); // Passer false
        $form->handleRequest($request);

        $admins = $userRepo->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_ADMIN%')
            ->getQuery()
            ->getResult();

        $user = $this->security->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($admins as $admin) {
                $ticket->addAssignedTo($admin);
            }

            $ticket->setUser($user);

            $ticket->setCreatedAt(new \DateTimeImmutable());
            $ticket->setUpdatedAt(new \DateTimeImmutable());

            $em->persist($ticket);
            $em->flush();
    
            return $this->redirectToRoute('app_show_tickets');
        }

        return $this->render('pages/tickets/createTicket.html.twig', [
            'form' => $form->createView(),
            'admins' => $admins,
        ]);
    }

    #[Route('/ticket-modification/{id}', name: 'app_ticket_modify', methods:['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function modify(Request $request, Ticket $ticket, EntityManagerInterface $em): Response
    {
        $user = $this->security->getUser();
    
        if ($ticket->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres tickets.');
        }
    
        $form = $this->createForm(TicketFormType::class, $ticket, ['is_edit' => true]); // Passer true
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $ticket->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();
    
            return $this->redirectToRoute('app_show_tickets');
        }
    
        return $this->render('pages/tickets/editTicket.html.twig', [
            'form' => $form->createView(),
            'ticket' => $ticket,
        ]);
    }

    #[Route('/ticket-status/{id}', name: 'app_ticket_status', methods:['POST'])]
    #[IsGranted('ROLE_USER')]
    public function updateStatus(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $ticket = $em->getRepository(Ticket::class)->find($id);
    
        if (!$ticket) {
            throw $this->createNotFoundException('Ticket non trouvé');
        }
    
        $user = $this->getUser();
    
        // Vérifiez que l'utilisateur connecté est bien le propriétaire du ticket
        if ($ticket->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce ticket');
        }
    
        $status = $request->request->get('status');
    
        if (in_array($status, ['Open', 'Closed'])) {
            $ticket->setStatus($status);
            $ticket->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();
        }
    
        return $this->redirectToRoute('app_show_tickets');
    }

    #[Route('/ticket-suppression/{id}', name: 'app_ticket_delete', methods:['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, Ticket $ticket, EntityManagerInterface $em): Response
    {
        $user = $this->security->getUser();
    
        if ($ticket->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous ne pouvez supprimer que vos propres tickets.');
        }
    
        if ($this->isCsrfTokenValid('delete' . $ticket->getId(), $request->request->get('_token'))) {
            $em->remove($ticket);
            $em->flush();
        }
    
        return $this->redirectToRoute('app_show_tickets');
    }    
}
