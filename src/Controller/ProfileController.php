<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Form\UserAndProfileFormType;
use App\Form\UserProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
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
                $currentAvatar = $profile->getAvatar();
                if ($currentAvatar && $currentAvatar !== 'default-avatar.png') {
                    $currentAvatarPath = $this->getParameter('kernel.project_dir').'/public/uploads/profile_images/'.$currentAvatar;
                    if (file_exists($currentAvatarPath)) {
                        unlink($currentAvatarPath);
                    }
                }
        
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

    #[Route('/profile/{id}', name: 'app_public_profile', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function publicProfile(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);
    
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }
    
        $profile = $user->getProfile();
    
        return $this->render('pages/profile/publicProfile.html.twig', [
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
            10
        );
    
        return $this->render('pages/profile/allProfiles.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/profile/edit/{id}', name: 'app_edit_profile')]
    #[IsGranted('ROLE_ADMIN')]
    public function editProfile(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        Security $security
    ): Response {
        $user = $entityManager->getRepository(User::class)->find($id);
    
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }
    
        $form = $this->createForm(UserAndProfileFormType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $adminPassword = $form->get('adminPassword')->getData();
    
            /** @var User $admin */
            $admin = $security->getUser();
    
            if (!$passwordHasher->isPasswordValid($admin, $adminPassword)) {
                throw new AuthenticationException('Mot de passe incorrect. Vous ne pouvez pas modifier le profil sans entrer votre propre mot de passe.');
            }
    
            $uploadedFile = $form->get('profile')->get('avatar')->getData();
            if ($uploadedFile instanceof UploadedFile) {
                $newFilename = uniqid().'.'.$uploadedFile->guessExtension();
                $uploadedFile->move(
                    $this->getParameter('kernel.project_dir').'/public/uploads/profile_images',
                    $newFilename
                );
                $user->getProfile()->setAvatar($newFilename);
            }
    
            $entityManager->flush();
    
            $this->addFlash('success', 'Le profil et les informations de l\'utilisateur ont été mis à jour');
            return $this->redirectToRoute('app_public_profile', ['id' => $user->getId()]);
        }
    
        return $this->render('pages/profile/adminEditProfile.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
    
    #[Route('/profile/delete/{id}', name: 'app_delete_user', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteUser(int $id, EntityManagerInterface $entityManager, Request $request, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $csrfToken = $request->request->get('_token');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('delete-user-' . $id, $csrfToken))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide');
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'L\'utilisateur a été supprimé avec succès.');

        return $this->redirectToRoute('app_all_profiles');
    }

    #[Route('/profile/delete-avatar', name: 'app_delete_avatar', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function deleteAvatar(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Il faut être connecté pour supprimer la photo de profil');
        }

        $profile = $user->getProfile();

        if ($profile->getAvatar() && $profile->getAvatar() !== 'default-avatar.png') {
            $currentAvatarPath = $this->getParameter('kernel.project_dir').'/public/uploads/profile_images/'.$profile->getAvatar();
            if (file_exists($currentAvatarPath)) {
                unlink($currentAvatarPath);
            }
        }

        $profile->setAvatar('default-avatar.png');
        $entityManager->flush();

        $this->addFlash('success', 'La photo de profil a été supprimée avec succès.');

        return $this->redirectToRoute('app_profile');
    }

}