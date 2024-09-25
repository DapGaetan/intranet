<?php

namespace App\Controller;

use App\Entity\Link;
use App\Form\LinkFormType;
use App\Repository\LinkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

class LinkController extends AbstractController
{
    #[Route('/mes-liens', name: 'app_user_links')]
    public function index(LinkRepository $linkRepository): Response
    {
        $user = $this->getUser();
    
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à vos liens.');
        }
    
        $links = $linkRepository->findBy(['user' => $user]);
    
        return $this->render('pages/link/showLinks.html.twig', [
            'links' => $links,
        ]);
    }

    #[Route('/link/new', name: 'app_link_new')]
    public function create(Request $request, LinkRepository $linkRepository, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
    
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour créer un lien.');
        }
    
        // Vérifie si l'utilisateur a déjà 8 liens
        $links = $linkRepository->findBy(['user' => $user]);
        if (count($links) >= 8) {
            $this->addFlash('error', 'Vous ne pouvez pas avoir plus de 8 liens.');
            return $this->redirectToRoute('app_user_links');
        }
    
        $link = new Link();
        $form = $this->createForm(LinkFormType::class, $link);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $link->setUser($user);
            $link->setCreatedAt(new \DateTimeImmutable());
            $link->setUpdatedAt(new \DateTimeImmutable());
    
            
            $logoFile = $form->get('logo')->getData();
            if ($logoFile) {
                $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$logoFile->guessExtension();
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/logo_links';
    
                try {
                    $logoFile->move(
                        $uploadDir,
                        $newFilename
                    );
                } catch (FileException $e) {
                    
                }
                $link->setLogo($newFilename);
            }
    
            $em->persist($link);
            $em->flush();
    
            return $this->redirectToRoute('app_home');
        }
    
        return $this->render('pages/link/newLink.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    

    

    #[Route('/link/{id}/edit', name: 'app_link_edit')]
    public function edit(Request $request, Link $link, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(LinkFormType::class, $link);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $link->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            return $this->redirectToRoute('app_user_links');
        }

        return $this->render('pages/link/editLink.html.twig', [
            'form' => $form->createView(),
            'link' => $link,
        ]);
    }

    #[Route('/link/{id}/delete', name: 'app_link_delete')]
    public function delete(Link $link, EntityManagerInterface $em): Response
    {
        $logoPath = $this->getParameter('kernel.project_dir') . '/public/uploads/logo_links/' . $link->getLogo();
    
        if ($link->getLogo() && file_exists($logoPath) && !is_dir($logoPath)) {
            unlink($logoPath);
        }
    
        $em->remove($link);
        $em->flush();
    
        return $this->redirectToRoute('app_home');
    }
    
    
}
