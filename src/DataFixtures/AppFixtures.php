<?php

namespace App\DataFixtures;

use App\Entity\Admin;
use App\Entity\Article;
use App\Entity\Category;
use App\Repository\AdminRepository;
use App\Repository\CategoryRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
	public function __construct(
		private readonly AdminRepository $userRepository,
		private readonly CategoryRepository $categoryRepository,
	){}

	public function load(ObjectManager $manager): void
	{
		$this->loadUsers($manager);
		$this->loadCategory($manager);
		$this->loadArticles($manager);

	}

	private function loadUsers(ObjectManager $manager)
	{
		$admin = new Admin();
		// Password = admin
		$admin->setUsername("admin")->setPassword('$2y$13$LXGYRqV/oH0.Mr5LPy4rDOiKEdwUtaaFBtaIZnCcSZatgkpQE3AH.')->setRoles(['ROLE_AUTHOR', 'ROLE_PREMIUM', 'ROLE_MODO', 'ROLE_ADMIN']);
		$manager->persist($admin);

		$manager->flush();
		$manager->clear();
	}

	private function loadCategory(ObjectManager $manager)
	{
		$category = new Category();
		$category->setTitle('Questions');
		$manager->persist($category);
		
		$category = new Category();
		$category->setTitle('Annonces');
		$manager->persist($category);
		
		$category = new Category();
		$category->setTitle('Nouveautés');
		$manager->persist($category);

		$manager->flush();
		$manager->clear();
	}

	private function loadArticles(ObjectManager $manager)
	{
		$article = new Article();
		$article->setTitle('Sortie du site')
			->setText("Ce site est en preparation mais devrait etre fini d'ici mercredi 11/10/2023. Merci de patienter encore un peu.")
			->setAuthor($this->userRepository->findOneBy(["username" => "admin"]))
			->setCategory($this->categoryRepository->findOneBy(["title" => "Annonces"]))
			->setImage('default.png')
			->setIsPremium(false);
	}
	
}