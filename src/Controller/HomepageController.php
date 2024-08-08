<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomepageController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        if (!$this->isGranted('ROLE_USER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Access Denied');
        }

        return $this->render('pages/home.html.twig', [
            'controller_name' => 'HomepageController',
        ]);
    }

    #[Route('/mentions-legales', name: 'app_mentions')]
    public function mentions(): Response
    {
        return $this->render('pages/mentions.html.twig', [
        ]);
    }
}
