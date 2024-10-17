<?php

namespace App\Controller;

use App\Entity\CulturalEventTicket;
use App\Form\CulturalEventTicketFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CulturalEventController extends AbstractController
{
    #[Route('/cultural', name: 'app_cultural_event_list')]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $culturalEvents = $entityManager->getRepository(CulturalEventTicket::class)->findAll();

        return $this->render('pages/cultural_event/showCulturalEvents.html.twig', [
            'culturalEvents' => $culturalEvents,
        ]);
    }

    #[Route('/cultural/event/{id}', name: 'app_cultural_event_show', requirements: ['id' => '\d+'])]
    public function show(CulturalEventTicket $event): Response
    {
        return $this->render('pages/cultural_event/showCulturalEvent.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/cultural/event/new', name: 'app_cultural_event_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new CulturalEventTicket();
        $form = $this->createForm(CulturalEventTicketFormType::class, $event);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $event->setCreatedBy($this->getUser());
            $event->setCreatedAt(new \DateTimeImmutable());
            $event->setUpdatedAt(new \DateTimeImmutable());

            $seasonsDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/culturalEvent/seasons/';
            $logoDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/culturalEvent/logo/';
            $backgroundDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/culturalEvent/background/';
    
            foreach ([$seasonsDirectory, $logoDirectory, $backgroundDirectory] as $dir) {
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
            }
    
            $logoFile = $form->get('logo')->getData();
            if ($logoFile) {
                $logoFilename = sprintf('%s-logo.%s', $event->getTitle(), $logoFile->guessExtension());
                $logoFile->move($logoDirectory, $logoFilename);
                $event->setLogo($logoFilename);
            }
    
            $seasonFile = $form->get('season')->getData();
            if ($seasonFile) {
                $seasonFilename = sprintf('%s-season.%s', $event->getTitle(), $seasonFile->guessExtension());
                $seasonFile->move($seasonsDirectory, $seasonFilename);
                $event->setSeason($seasonFilename);
            }
    
            $backgroundFile = $form->get('background')->getData();
            if ($backgroundFile) {
                $backgroundFilename = sprintf('%s-background.%s', $event->getTitle(), $backgroundFile->guessExtension());
                $backgroundFile->move($backgroundDirectory, $backgroundFilename);
                $event->setBackground($backgroundFilename);
            }
    
            $entityManager->persist($event);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_cultural_event_list');
        }
    
        return $this->render('pages/cultural_event/newCulturalEvents.html.twig', [
            'form' => $form->createView(),
        ]);
    }    

    #[Route('/cultural/event/{id}/edit', name: 'app_cultural_event_edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, CulturalEventTicket $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CulturalEventTicketFormType::class, $event);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_cultural_event_show', ['id' => $event->getId()]);
        }

        return $this->render('pages/cultural_event/editCulturalEvent.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    #[Route('/cultural/event/{id}/delete', name: 'app_cultural_event_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, CulturalEventTicket $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_cultural_event_list');
    }
}
