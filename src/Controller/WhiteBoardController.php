<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WhiteBoardController extends AbstractController
{
    #[Route('/white/board', name: 'app_white_board')]
    public function index(): Response
    {
        return $this->render('pages/whiteBoard/board.html.twig', [
            'controller_name' => 'WhiteBoardController',
        ]);
    }
}
