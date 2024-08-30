<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Form\UserProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Il faut être connecté pour accéder à son profil');
        }
    
        $profile = $user->getProfile();
        if (!$profile) {
            $profile = new UserProfile();
            $profile->setUser($user);
            $entityManager->persist($profile);
            $entityManager->flush();
        }
    
        $form = $this->createForm(UserProfileFormType::class, $profile);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->get('avatar')->getData();
            if ($uploadedFile instanceof UploadedFile) {
                $newFilename = uniqid().'.'.$uploadedFile->guessExtension();
                $uploadedFile->move(
                    $this->getParameter('kernel.project_dir').'/public/uploads/profile_images',
                    $newFilename
                );
                $profile->setAvatar($newFilename);
            }
    
            
            if (!$profile->getAvatar()) {
                $profile->setAvatar('default-avatar.png');
            }
    
            $selectedDepartment = $profile->getDepartment();
            if ($selectedDepartment) {
                $profile->setAddress($selectedDepartment->getName());
            }
    
            $profile->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();
    
            $this->addFlash('success', 'Le profil est mis à jour');
            return $this->redirectToRoute('app_home');
        }
    
        $avatarPath = $profile->getAvatar() 
            ? '/uploads/profile_images/'.$profile->getAvatar() 
            : '/uploads/profile_images/default-avatar.png';
    
        return $this->render('pages/profile/profile.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'avatarPath' => $avatarPath,
        ]);
    }
    
    
    #[Route('/profile/{id}', name: 'app_public_profile')]
    #[IsGranted('ROLE_USER')]
    public function publicProfile(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);
    
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }
    
        $profile = $user->getProfile();
    
        return $this->render('pages/profile/public_profile.html.twig', [
            'user' => $user,
            'profile' => $profile,
        ]);
    }

    #[Route('/profiles', name: 'app_all_profiles')]
    #[IsGranted('ROLE_USER')]
    public function allProfiles(EntityManagerInterface $entityManager, PaginatorInterface $paginator, Request $request): Response
    {
        $queryBuilder = $entityManager->getRepository(User::class)->createQueryBuilder('u')
            ->orderBy('u.username', 'ASC');
    
        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            8
        );
    
        return $this->render('pages/profile/all_profiles.html.twig', [
            'pagination' => $pagination,
        ]);
    }
    
}