<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Form\BrandType;
use App\Form\SearchType;
use Cocur\Slugify\Slugify;
use App\Repository\BrandRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BrandController extends AbstractController
{
    /**
     * Shows the list of brands, with or without searching
     * 
     * @param BrandRepository $brandRepository
     * @param Request $request
     * @return Response
     * @Route("/brand", name="brand_index")
     */
    public function index(BrandRepository $brandRepository, Request $request): Response
    {
        // Form creation
        $searchForm = $this->createForm(SearchType::class);
        
        // Request handling to check if the form has been submitted
        $searchForm->handleRequest($request);
        if($searchForm->isSubmitted() && $searchForm->isValid()){
            $search_category = $request->request->get('search')['search_category'];
            $search_text = $request->request->get('search')['search_text'];
            // Using the parameters from the request, we search specific brands
            $brands = $brandRepository->searchBrands($search_category, $search_text);
        } else {
            $brands = $brandRepository->findAll();
        }
        return $this->render('brand/index.html.twig', [
            'brands' => $brands,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    /**
     * Adds a brand to the database form the form submitted
     * 
     * @param Request $request
     * @Route("/brand/add", name="brand_add")
     */
    public function addBrand(Request $request){
        $brand = new Brand();
        $slugify = new Slugify();
        $addForm = $this->createForm(BrandType::class, $brand);
        $addForm->handleRequest($request);
        if($addForm->isSubmitted() && $addForm->isValid()){
            $brand->setSlug($slugify->slugify($brand->getName()));
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($brand);
            $manager->flush();
            $this->addFlash('success', "Votre marque {$brand->getName()} a bien été ajoutée");
        }
        return $this->render('brand/add.html.twig', [
            'addForm' => $addForm->createView(),
        ]);
    }
}
