<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
    #[Route('/', name: 'app_homepage', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('base.html.twig', [
            'controller_name' => 'HomepageController',
            'recents' => $articleRepository->findAll(),
        ]);
    }
}
