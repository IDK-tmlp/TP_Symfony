<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Form\AdminRegisterType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class HomepageController extends AbstractController
{
    #[Route('/', name: 'app_homepage', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('base.html.twig', [
            'controller_name' => 'HomepageController',
            'recents' => $articleRepository->sortByRecent(),
        ]);
    }

    #[Route('/register', name: 'app_admin_register', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $admin = new Admin();
        $form = $this->createForm(AdminRegisterType::class, $admin);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($admin);
            $entityManager->flush();

            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/new.html.twig', [
            'admin' => $admin,
            'form' => $form,
        ]);
    }

    public function sendEmail(MailerInterface $mailer)
    {
        $email = (new Email())
            ->from('your@email.com')
            ->to('recipient@email.com')
            ->subject('Sujet de l\'e-mail')
            ->text('Contenu du message.');

        $mailer->send($email);

    }
}
