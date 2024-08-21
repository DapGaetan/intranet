<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Form\TicketFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Ticket;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
    public function show(EntityManagerInterface $entityManager, PaginatorInterface $paginator, Request $request): Response
    {
        $user = $this->getUser();
    
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }
    
        $queryBuilder = $entityManager->getRepository(Ticket::class)->createQueryBuilder('t')
            ->where('t.user = :user')
            ->andWhere('t.isDeleted = :isDeleted')
            ->setParameter('user', $user)
            ->setParameter('isDeleted', false)
            ->orderBy('t.status', 'DESC') // "Open" avant "Closed"
            ->addOrderBy('t.created_at', 'DESC'); // Trier par date de création, du plus récent au plus ancien
    
        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            15
        );
    
        return $this->render('pages/tickets/showTickets.html.twig', [
            'pagination' => $pagination,
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
    
        if ($ticket->getStatus() === 'Closed') {
            throw $this->createAccessDeniedException('Ce ticket est fermé et ne peut pas être modifié.');
        }
    
        $form = $this->createForm(TicketFormType::class, $ticket, ['is_edit' => true]);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $ticket->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();
    
            return $this->redirectToRoute('app_ticket_show', ['id' => $ticket->getId()]);
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
        $isAdmin = $this->isGranted('ROLE_ADMIN');
    
        if (!$isAdmin && $request->request->get('status') === 'Closed') {
            throw $this->createAccessDeniedException('Vous ne pouvez pas fermer un ticket.');
        }
    
        $status = $request->request->get('status');
    
        if (in_array($status, ['Open', 'Closed'])) {
            $ticket->setStatus($status);
            $ticket->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();
        }
    
        return $this->redirectToRoute($isAdmin ? 'app_admin_tickets' : 'app_show_tickets');
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
            $ticket->setIsDeleted(true);
            $ticket->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();
        }
    
        return $this->redirectToRoute('app_show_tickets');
    }
    
    
    #[Route('/admin/tickets', name: 'app_admin_tickets')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminShow(EntityManagerInterface $entityManager, PaginatorInterface $paginator, Request $request): Response
    {
        $ticketsQueryBuilder = $entityManager->getRepository(Ticket::class)
            ->createQueryBuilder('t')
            ->orderBy("CASE WHEN t.status = 'Closed' THEN 1 ELSE 0 END", 'ASC') // Trie pour mettre "Closed" en bas
            ->addOrderBy('t.created_at', 'DESC'); // Ensuite, trie par date de création
    
        $pagination = $paginator->paginate(
            $ticketsQueryBuilder,
            $request->query->getInt('page', 1),
            15
        );
    
        return $this->render('pages/tickets/adminTickets.html.twig', [
            'pagination' => $pagination,
        ]);
    }
    
    

    #[Route('/admin/ticket/{id}', name: 'app_ticket_show_admin', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function admShowTicket(Ticket $ticket): Response
    {
        return $this->render('pages/tickets/adminShowTicket.html.twig', [
            'ticket' => $ticket,
        ]);
    }
    

    #[Route('/admin/ticket-status/{id}', name: 'app_ticket_status_admin', methods:['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateStatusAdmin(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $ticket = $em->getRepository(Ticket::class)->find($id);
    
        if (!$ticket) {
            throw $this->createNotFoundException('Ticket non trouvé');
        }
    
        $status = $request->request->get('status');
    
        if (in_array($status, ['Open', 'Closed'])) {
            $ticket->setStatus($status);
            $ticket->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();
        }
    
        return $this->redirectToRoute('app_admin_tickets');
    }    


    #[Route('/admin/ticket-suppression/{id}', name: 'app_ticket_delete_admin', methods:['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteAdmin(Request $request, Ticket $ticket, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $ticket->getId(), $request->request->get('_token'))) {
            $em->remove($ticket);
            $em->flush();
        }

        return $this->redirectToRoute('app_admin_tickets');
    }

    #[Route('/admin/ticket-modification/{id}', name: 'app_admin_ticket_modify', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function modifyForAdmin(Request $request, Ticket $ticket, EntityManagerInterface $em): Response
    {
        $form = $this->createFormBuilder($ticket)
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Ouvert' => 'Open',
                    'Fermé' => 'Closed',
                ],
                'label' => 'Statut du ticket :',
                'label_attr' => [
                    'class' => ''
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ticket->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            return $this->redirectToRoute('app_admin_tickets');
        }

        return $this->render('pages/tickets/adminEditTicket.html.twig', [
            'form' => $form->createView(),
            'ticket' => $ticket,
        ]);
    }


}
