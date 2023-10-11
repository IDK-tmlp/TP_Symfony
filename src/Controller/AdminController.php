<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Form\AdminType;
use App\Repository\AdminRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_index', methods: ['GET', 'POST'])]
    public function index(AdminRepository $adminRepository, Request $request): Response
    {
        $role_search = '';
        $name_search = '';
       
        
        if ( $request->request->get('role_search') !== null || $request->request->get('name_search')!== null ) {
            $role_search = $request->request->get('role_search');
            $name_search = $request->request->get('name_search');
        }
        
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $adminRepository->getAdminPaginator($role_search, $name_search, $offset);
        return $this->render('admin/index.html.twig', [
            'roles' => $adminRepository->getListRole(),
            'names' => $adminRepository->getListName(),
            'admins' => $paginator,
            'role_search'=> $role_search,
            'name-search'=>$name_search,
        ]);
    }

    #[Route('/new', name: 'app_admin_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $admin = new Admin();
        $form = $this->createForm(AdminType::class, $admin);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($admin);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/new.html.twig', [
            'admin' => $admin,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_show', methods: ['GET'])]
    public function show(Admin $admin): Response
    {
        return $this->render('admin/show.html.twig', [
            'admin' => $admin,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Admin $admin, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AdminType::class, $admin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/edit.html.twig', [
            'admin' => $admin,
            'form' => $form,
        ]);
    }
    
    #[Route('/premium/{id}', name: 'app_admin_premium', methods: ['GET'])]
    public function setPremium(Admin $admin, EntityManagerInterface $entityManager): Response
    {
        $admin->addRole('ROLE_PREMIUM');
        $entityManager->persist($admin);
        $entityManager->flush();

        return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_admin_delete', methods: ['POST'])]
    public function delete(Request $request, Admin $admin, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$admin->getId(), $request->request->get('_token'))) {
            $entityManager->remove($admin);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_index', [], Response::HTTP_SEE_OTHER);
    }
}
