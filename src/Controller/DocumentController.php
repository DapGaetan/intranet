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
    
        // Vérifie le poids du dossier de l'utilisateur
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
                // Vérifie la taille du fichier
                if ($pdfFile->getSize() > 5 * 1024 * 1024 * 1024) { // 5 Go
                    $this->addFlash('error', 'Le document ne doit pas dépasser 5 Go.');
                    return $this->redirectToRoute('app_document_new');
                }
    
                $safeFilename = $slugger->slug(pathinfo($pdfFile->getClientOriginalName(), PATHINFO_FILENAME));
                $newFilename = $safeFilename.'-'.$user->getLastName().'-'.$user->getFirstName().'.'.$pdfFile->guessExtension();
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/documents/' . $user->getLastName() . '-' . $user->getFirstName();
    
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
    
                try {
                    $pdfFile->move($uploadDir, $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement du document.');
                    return $this->redirectToRoute('app_document_new');
                }
    
                $document->setFilePath($uploadDir . '/' . $newFilename);
            }
    
            $em->persist($document);
            $em->flush();
    
            return $this->redirectToRoute('app_user_documents');
        }
    
        return $this->render('pages/document/newDocument.html.twig', [
            'form' => $form->createView(),
        ]);
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
}
