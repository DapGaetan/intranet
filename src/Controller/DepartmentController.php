<?php

namespace App\Controller;

use App\Entity\Department;
use App\Form\DepartmentFormType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DepartmentController extends AbstractController
{
    #[Route('/department/new', name: 'app_department_create')]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $department = new Department();
        $form = $this->createForm(DepartmentFormType::class, $department);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $department->setName();
            $em->persist($department);
            $em->flush();

            return $this->redirectToRoute('app_departments_show');
        }

        return $this->render('pages/department/createDepartment.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/departments', name: 'app_departments_show')]
    #[IsGranted('ROLE_ADMIN')]
    public function list(EntityManagerInterface $entityManager, PaginatorInterface $paginator, Request $request): Response
    {
        $queryBuilder = $entityManager->getRepository(Department::class)->createQueryBuilder('d')
            ->orderBy('d.updated_at', 'DESC');
    
        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            9
        );
    
        return $this->render('pages/department/showDepartments.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/department/{id}/delete', name: 'app_department_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Department $department, EntityManagerInterface $em): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete' . $department->getId(), $request->request->get('_token'))) {
            $em->remove($department);
            $em->flush();
        }

        return $this->redirectToRoute('app_departments_show');
    }

    #[Route('/department/{id}', name: 'app_department_show')]
    #[IsGranted('ROLE_ADMIN')]
    public function show(Department $department): Response
    {
        return $this->render('pages/department/showDepartment.html.twig', [
            'department' => $department,
        ]);
    }

    #[Route('/department/{id}/edit', name: 'app_department_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Department $department, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DepartmentFormType::class, $department);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $department->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();
            return $this->redirectToRoute('app_department_show', ['id' => $department->getId()]);
        }

        return $this->render('pages/department/editDepartment.html.twig', [
            'department' => $department,
            'form' => $form->createView(),
        ]);
    }
}
