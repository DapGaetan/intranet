<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\LinkRepository;
use Symfony\Component\Security\Core\User\UserInterface;

class HomepageController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function home(LinkRepository $linkRepository, UserInterface $user)
    {
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Access Denied');
        }
        $userProfile = $user->getProfile();
    
        $links = $linkRepository->findBy(['user' => $userProfile]);
    
        return $this->render('pages/home.html.twig', [
            'links' => $links,
        ]);
    }


    #[Route('/mentions-legales', name: 'app_mentions')]
    public function mentions(): Response
    {
        return $this->render('pages/mentions.html.twig', [
        ]);
    }
}
