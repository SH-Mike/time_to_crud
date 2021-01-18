<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Brand;
use App\Entity\Product;
use Cocur\Slugify\Slugify;
use App\Repository\BrandRepository;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    private $brandRepository;
    private $productRepository;
    private $manager;
    private $slugify;
    private $faker;

    public function __construct(BrandRepository $brandRepository, ProductRepository $productRepository, EntityManagerInterface $em)
    {
        $this->brandRepository = $brandRepository;
        $this->productRepository = $productRepository;
        $this->manager = $em;
        $this->slugify = new Slugify();
        $this->faker = Factory::create();
    }

    /**
     * We first load 10 random Brands, and then load 25 random Products using the Brands
     * 
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->loadBrands($manager);
        
        $this->loadProducts($manager);
    }

    /**
     * Creates 25 random Products and persist them into database
     * 
     * @param ObjectManager $manager 
     */
    private function loadProducts($manager)
    {
        $brands = $this->brandRepository->findAll();

        for ($i = 0; $i < 25; $i++) {
            $product = new Product();
            $product->setName($this->faker->sentence(2,true))
                ->setCreationDate($this->faker->dateTime('now', null))
                ->setPrice($this->faker->randomFloat(2, 0, 999999))
                ->setDescription($this->faker->sentence(10, true))
                ->setSlug($this->slugify->slugify($product->getName()))
                ->setBrand($brands[rand(0, sizeof($brands) - 1)]);

            $manager->persist($product);
        }
        
        $manager->flush();
    }

    /**
     * Creates 10 random Brands and persist them into database
     * 
     * @param ObjectManager $manager
     */
    private function loadBrands($manager)
    {
        for ($i = 0; $i < 10; $i++) {
            $brand = new Brand();
            $brand->setName($this->faker->sentence(2,true))
                ->setLogo("https://placehold.it/100x100")
                ->setCreationDate($this->faker->dateTime('now', null))
                ->setNationality($this->faker->country)
                ->setSlogan($this->faker->sentence(5, true))
                ->setWebsite("https://theuselessweb.com/")
                ->setSlug($this->slugify->slugify($brand->getName()));

            $manager->persist($brand);
        }

        $manager->flush();
    }
}