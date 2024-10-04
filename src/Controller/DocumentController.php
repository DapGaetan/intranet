<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Document;
use App\Form\DocumentFormType;
use App\Repository\DocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

class DocumentController extends AbstractController
{
    #[Route('/documents', name: 'app_user_documents')]
    public function index(DocumentRepository $documentRepository, Request $request): Response
    {
        $user = $this->getUser();
    
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à vos documents.');
        }
    
        $searchTerm = $request->query->get('search');
        $dateFilter = $request->query->get('date');

        $documents = $documentRepository->findBy(['created_by' => $user]);
    
        if ($searchTerm) {
            $documents = array_filter($documents, function($document) use ($searchTerm) {
                return stripos($document->getTitle(), $searchTerm) !== false || stripos($document->getDescription(), $searchTerm) !== false;
            });
        }
    
        if ($dateFilter) {
            $documents = array_filter($documents, function($document) use ($dateFilter) {
                return $document->getCreatedAt()->format('d-m-Y') === $dateFilter;
            });
        }
    
        return $this->render('pages/document/showDocuments.html.twig', [
            'documents' => $documents,
            'searchTerm' => $searchTerm,
        ]);
    }
    
    #[Route('/document/new', name: 'app_document_new')]
    public function create(Request $request, DocumentRepository $documentRepository, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
    
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour créer un document.');
        }
    
        $userDocuments = $documentRepository->findBy(['created_by' => $user]);
        $totalSize = array_reduce($userDocuments, function ($carry, $doc) {
            return $carry + filesize($doc->getFilePath());
        }, 0);
    
        if ($totalSize >= 20 * 1024 * 1024 * 1024) { // 20 Go
            $this->addFlash('error', 'Vous ne pouvez pas dépasser 20 Go de documents.');
            return $this->redirectToRoute('app_user_documents');
        }
    
        $document = new Document();
        $form = $this->createForm(DocumentFormType::class, $document);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $document->setCreatedBy($user);
            $document->setCreatedAt(new \DateTimeImmutable());
            $document->setUpdatedAt(new \DateTimeImmutable());
    
            $pdfFile = $form->get('file')->getData();
            if ($pdfFile) {
                if ($pdfFile->getSize() > 5 * 1024 * 1024 * 1024) { // 5 Go
                    $this->addFlash('error', 'Le document ne doit pas dépasser 5 Go.');
                    return $this->redirectToRoute('app_document_new');
                }
    
                $originalFilename = pathinfo($pdfFile->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $pdfFile->guessExtension();
    
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . $user->getLastName() . '-' . $user->getFirstName() . '.' . $extension;
    
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/documents/' . $user->getLastName() . '-' . $user->getFirstName();
    
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
    
                // Vérification si le fichier existe déjà
                if (file_exists($uploadDir . '/' . $newFilename)) {
                    $this->addFlash('error', 'Un fichier ayant le même nom existe déjà, modifiez son nom avant de réessayer.');
                    return $this->redirectToRoute('app_document_new');
                }
    
                try {
                    $pdfFile->move($uploadDir, $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement du document.');
                    return $this->redirectToRoute('app_document_new');
                }
    
                $document->setFilePath('uploads/documents/' . $user->getLastName() . '-' . $user->getFirstName() . '/' . $newFilename);
                $document->setTitle($safeFilename . '.' . $extension);
            }
    
            $em->persist($document);
            $em->flush();
    
            return $this->redirectToRoute('app_user_documents');
        }
    
        return $this->render('pages/document/newDocument.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/document/{id}', name: 'app_document_show')]
    public function show(Document $document): Response
    {
        $user = $document->getCreatedBy();
        
        if ($user === null) {
            throw $this->createNotFoundException('User not found');
        }
    
        $filePath = $document->getFilePath();
        $fullFilePath = $this->getParameter('kernel.project_dir') . '/public/' . $filePath;
    
        
        dump($fullFilePath);
    
        if (!file_exists($fullFilePath)) {
            throw $this->createNotFoundException('Le fichier n\'existe pas. Chemin : ' . $fullFilePath);
        }
    
        return $this->render('pages/document/showDocument.html.twig', [
            'document' => $document,
            'filePath' => $filePath,
        ]);
    }

    #[Route('/document/{id}/download', name: 'app_document_download')]
    public function download(Document $document): Response
    {

        $filePath = $document->getFilePath();
        $fullFilePath = $this->getParameter('kernel.project_dir') . '/public/' . $filePath;


        if (!file_exists($fullFilePath)) {
            throw $this->createNotFoundException('Le fichier n\'existe pas.');
        }

        return $this->file($fullFilePath);
    }

    #[Route('/document/{id}/edit', name: 'app_document_edit')]
    public function edit(Request $request, Document $document, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(DocumentFormType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $document->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            return $this->redirectToRoute('app_user_documents');
        }

        return $this->render('pages/document/editDocument.html.twig', [
            'form' => $form->createView(),
            'document' => $document,
        ]);
    }

    #[Route('/document/{id}/delete', name: 'app_document_delete')]
    public function delete(Document $document, EntityManagerInterface $em): Response
    {
        if ($document->getFilePath() && file_exists($document->getFilePath())) {
            unlink($document->getFilePath());
        }

        $em->remove($document);
        $em->flush();

        return $this->redirectToRoute('app_user_documents');
    }

    #[Route('/admin/documents', name: 'app_admin_documents')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminDocuments(DocumentRepository $documentRepository, Request $request): Response
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à vos documents.');
        }
    
        $searchTerm = $request->query->get('search');
        $dateFilter = $request->query->get('date');
        
        if ($this->isGranted('ROLE_ADMIN')) {
            $documents = $documentRepository->findAll();
        } else {
            $documents = $documentRepository->findBy(['created_by' => $user, 'is_admin_only' => false]);
        }

        if ($searchTerm) {
            $documents = array_filter($documents, function($document) use ($searchTerm) {
                return stripos($document->getTitle(), $searchTerm) !== false || stripos($document->getDescription(), $searchTerm) !== false;
            });
        }
    
        if ($dateFilter) {
            $documents = array_filter($documents, function($document) use ($dateFilter) {
                return $document->getCreatedAt()->format('d-m-Y') === $dateFilter;
            });
        }
    
        return $this->render('pages/document/showAdminDocuments.html.twig', [
            'documents' => $documents,
            'searchTerm' => $searchTerm,
        ]);
    }

    #[Route('/admin/document/new', name: 'app_admin_document_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function createAdminDocument(Request $request, DocumentRepository $documentRepository, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
    
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Accès réservé aux administrateurs.');
        }
    
        $document = new Document();
        $form = $this->createForm(DocumentFormType::class, $document);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $document->setCreatedBy($user);
            $document->setCreatedAt(new \DateTimeImmutable());
            $document->setUpdatedAt(new \DateTimeImmutable());
            $document->setIsAdminOnly(true);
    
            $pdfFile = $form->get('file')->getData();
            if ($pdfFile) {
                $originalFilename = pathinfo($pdfFile->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $pdfFile->guessExtension();
    
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-admin.' . $extension;
    
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/documents/adm-only';
    
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
    
                // Vérification si le fichier existe déjà
                if (file_exists($uploadDir . '/' . $newFilename)) {
                    $this->addFlash('error', 'Un fichier ayant le même nom existe déjà, modifiez son nom avant de réessayer.');
                    return $this->redirectToRoute('app_admin_document_new');
                }
    
                try {
                    $pdfFile->move($uploadDir, $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement du document.');
                    return $this->redirectToRoute('app_admin_document_new');
                }
    
                $document->setFilePath('uploads/documents/adm-only/' . $newFilename);
                $document->setTitle($safeFilename . '.' . $extension);
            }
    
            $em->persist($document);
            $em->flush();
    
            return $this->redirectToRoute('app_admin_documents');
        }
    
        return $this->render('pages/document/newAdminDocument.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/admin/document/{id}', name: 'app_admin_document_show')]
    #[IsGranted('ROLE_ADMIN')]
    public function showAdminDocument(Document $document): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') || !$document->getIsAdminOnly()) {
            throw $this->createAccessDeniedException('Accès réservé aux administrateurs.');
        }

        $filePath = $document->getFilePath();
        $fullFilePath = $this->getParameter('kernel.project_dir') . '/public/' . $filePath;

        if (!file_exists($fullFilePath)) {
            throw $this->createNotFoundException('Le fichier n\'existe pas. Chemin : ' . $fullFilePath);
        }

        return $this->render('pages/document/showAdminDocument.html.twig', [
            'document' => $document,
            'filePath' => $filePath,
        ]);
    }

    #[Route('/admin/document/{id}/download', name: 'app_admin_document_download')]
    #[IsGranted('ROLE_ADMIN')]
    public function downloadAdminDocument(Document $document): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') || !$document->getIsAdminOnly()) {
            throw $this->createAccessDeniedException('Accès réservé aux administrateurs.');
        }

        $filePath = $document->getFilePath();
        $fullFilePath = $this->getParameter('kernel.project_dir') . '/public/' . $filePath;

        if (!file_exists($fullFilePath)) {
            throw $this->createNotFoundException('Le fichier n\'existe pas.');
        }

        return $this->file($fullFilePath);
    }

    #[Route('/admin/document/{id}/edit', name: 'app_admin_document_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function editAdminDocument(Request $request, Document $document, EntityManagerInterface $em): Response
    {
        if (!$document->getIsAdminOnly()) {
            throw $this->createAccessDeniedException('Accès réservé aux administrateurs.');
        }

        $form = $this->createForm(DocumentFormType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $document->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            return $this->redirectToRoute('app_admin_documents');
        }

        return $this->render('pages/document/editAdminDocument.html.twig', [
            'form' => $form->createView(),
            'document' => $document,
        ]);
    }

    #[Route('/admin/document/{id}/delete', name: 'app_admin_document_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteAdminDocument(Document $document, EntityManagerInterface $em): Response
    {
        if (!$document->getIsAdminOnly()) {
            throw $this->createAccessDeniedException('Accès réservé aux administrateurs.');
        }

        $filePath = $document->getFilePath();
        $fullFilePath = $this->getParameter('kernel.project_dir') . '/public/' . $filePath;

        if ($filePath && file_exists($fullFilePath)) {
            unlink($fullFilePath);
        }

        $em->remove($document);
        $em->flush();

        return $this->redirectToRoute('app_admin_documents');
    }
}
