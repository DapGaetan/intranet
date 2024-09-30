<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Routing\Annotation\Route;

class LdapTestController extends AbstractController
{
    #[Route('/ldap', name: 'ldap_test_login')]
    public function login(): Response
    {
        return $this->render('pages/test/ldap_login.html.twig');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        // Redirige vers la page d'accueil ou une autre page après une connexion réussie
        return $this->redirectToRoute('app_home'); // Change 'app_home' par le nom de ta route d'accueil
    }
}
