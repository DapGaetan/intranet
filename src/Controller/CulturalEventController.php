<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CulturalEventController extends AbstractController
{
    #[Route('/cultural/event', name: 'app_cultural_event')]
    public function index(): Response
    {
        return $this->render('pages\cultural_event\showCulturalEvents.html.twig', [
            'controller_name' => 'CulturalEventController',
        ]);
    }
}
