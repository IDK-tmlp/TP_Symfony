<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Form\ArticleType;
use App\Form\ArticleTypeFromCategorie;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/article')]
class ArticleController extends AbstractController
{
    #[Route('/', name: 'app_article_index', methods: ['GET', 'POST'])]
    public function index(ArticleRepository $articleRepository, CategoryRepository $categoryRepository, Request $request): Response
    {
        $titles = $categoryRepository->getListTitle();
        $title_search = '';
        $offset = max(0, $request->query->getInt('offset', 0));
        if ( $request->request->get('title_search') !== null ) {
            $title_search = $request->request->get('title_search');
            $title_search = $categoryRepository->findOneBy(['title' => $title_search]);
            $paginator = $articleRepository->getArticlePaginator($title_search, $offset);
        }else if($request->request->get('title') !== null) {
            $paginator = $articleRepository->findByTitle($request->request->get('title'));
        }else {
            $paginator = $articleRepository->findAll();
        }
        $free=false;
        $premium=false;
        foreach ($paginator as $article) {
            if ($article->isIsPremium()) {
                $premium[] = $article;
            }else{
                $free[] = $article;
            }
        }

        return $this->render('article/index.html.twig', [
            'free' => $free,
            'premium' => $premium,
            'title_search' => $title_search,
            'titles' => $titles,
        ]);
    }

    #[Route('/author/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, #[Autowire('%photo_dir%')] string $photodir, Security $security): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $security->getUser();
            $user->addArticle($article);
            $entityManager->persist($article);
            $entityManager->flush();
            if ($image = $form['image']->getData()) {
                $filename=$article->getId().'.'.$image->guessExtension();
                $image->move($photodir, $filename);
                $article->setImage($filename);
            }else {
                $article->setImage('default.png');
            }
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }
    
    #[Route('/author/new/{id}', name: 'app_article_new_with_id', methods: ['GET', 'POST'])]
    public function newFromCategory(Request $request, EntityManagerInterface $entityManager, Category $category, #[Autowire('%photo_dir%')] string $photodir, Security $security): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleTypeFromCategorie::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $security->getUser();
            $user->addArticle($article);
            $category->addArticle($article);
            $entityManager->persist($article);
            $entityManager->flush();
            if ($image = $form['image']->getData()) {
                $filename=$article->getId().'.'.$image->guessExtension();
                $image->move($photodir, $filename);
                $article->setImage($filename);
                $entityManager->persist($article);
                $entityManager->flush();
            }else {
                $article->setImage('default.png');
            }
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('app_category_show',['id' => $category->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/show/{id}', name: 'app_article_show', methods: ['GET', 'POST'])]
    public function show(Article $article, Request $request, CommentRepository $commentRepository): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $commentRepository->getCommentPaginator( $article, $offset);
        return $this->render('article/show.html.twig', [
            'article' => $article,
            'comments' => $paginator,
            'previous' => $offset - CommentRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
        ]);
    }

    #[Route('/author/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager, #[Autowire('%photo_dir%')] string $photodir): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $filename = $article->getImage();

            $entityManager->flush();
            if ($image = $form['image']->getData()) {
                $filename=$article->getId().'.'.$image->guessExtension();
                $image->move($photodir, $filename);
                $article->setImage($filename);
            }else if ($filename !== 'default.png'){
                    $filesystem = new Filesystem();
                    $filesystem->remove($photodir.'/'.$filename);
                    $article->setImage('default.png');
            }
            $entityManager->persist($article);
            $entityManager->flush();
            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/author/{id}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/author/delete/picture/{id}', name: 'app_article_delete_picture', methods: ['GET'])]
    public function deletePicture(Request $request, Article $article, EntityManagerInterface $entityManager, #[Autowire('%photo_dir%')] string $photodir): Response
    {
        // handle delete
        
        $filename = $article->getImage();
        if ($filename !== 'default.png'){
            $filesystem = new Filesystem();
            $filesystem->remove($photodir.'/'.$filename);
            $article->setImage('default.png');
            $entityManager->persist($article);
            $entityManager->flush();
        }


        return $this->redirectToRoute('app_article_show',['id' => $article->getId()], Response::HTTP_SEE_OTHER);
    }
}
