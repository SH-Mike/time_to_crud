<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Form\BrandType;
use App\Form\BrandSearchType;
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
     * Shows a list of Brands, with or without searching
     * 
     * @param BrandRepository $brandRepository
     * @param Request $request
     * @return Response
     * @Route("/brand", name="brand_index")
     */
    public function index(BrandRepository $brandRepository, Request $request): Response
    {
        // Form creation
        $searchForm = $this->createForm(BrandSearchType::class);

        // Request handling to check if the form has been submitted
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search_category = $request->request->get('brand_search')['search_category'];
            $search_text = $request->request->get('brand_search')['search_text'];
            // Using the parameters from the request, we search specific Brands
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
     * Shows a detailled view of the Brand chosen
     * 
     * @param Brand $brand
     * @return Response
     * 
     * @Route("/brand/view/{brand}", name="brand_view")
     */
    public function getBrand(Brand $brand = null){
        // If Brand not found, then we redirect the user to the Brands index page
        if ($brand == null) {
            $this->addFlash('danger', "La marque que vous essayez de visionner n'existe pas");
            return $this->redirectToRoute("brand_index");
        }

        return $this->render('brand/view.html.twig', [
            'brand' => $brand
        ]);
    }


    /**
     * Adds a Brand to the database form the form submitted
     * 
     * @param Request $request
     * @return Response
     * @Route("/brand/add", name="brand_add")
     */
    public function addBrand(Request $request)
    {
        $brand = new Brand();
        // Slugify initialisation to create the slug data using the name
        $slugify = new Slugify();
        // Form creation
        $addForm = $this->createForm(BrandType::class, $brand);
        // Request handling to check if the form has been submitted
        $addForm->handleRequest($request);
        if ($addForm->isSubmitted() && $addForm->isValid()) {
            $brand->setSlug($slugify->slugify($brand->getName()));
            // Persistence in database 
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($brand);
            $manager->flush();
            // Adding a flash message to inform the user
            $this->addFlash('success', "Votre marque {$brand->getName()} a bien été ajoutée");
            return $this->redirectToRoute('brand_index');
        }
        return $this->render('brand/add.html.twig', [
            'addForm' => $addForm->createView(),
        ]);
    }

    /**
     * Modify a given Brand from the submitted form
     * 
     * @param Request $request
     * @param Brand $brand
     * @return Response
     * @Route("/brand/edit/{brand}", name="brand_edit")
     */
    public function editBrand(Request $request, Brand $brand = null)
    {
        // If Brand not found, then we redirect the user to the Brands index page
        if ($brand == null) {
            $this->addFlash('danger', "La marque que vous essayez de modifier n'existe pas");
            return $this->redirectToRoute("brand_index");
        }
        // Slugify initialisation to create the slug data using the name
        $slugify = new Slugify();
        // Form creation
        $editForm = $this->createForm(BrandType::class, $brand);
        // Request handling to check if the form has been submitted
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $brand->setSlug($slugify->slugify($brand->getName()));
            // Persistence in database 
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($brand);
            $manager->flush();
            // Adding a flash message to inform the user
            $this->addFlash('success', "Votre marque {$brand->getName()} a bien été modifiée");
            return $this->redirectToRoute("brand_index");
        }
        return $this->render('brand/edit.html.twig', [
            'editForm' => $editForm->createView(),
            'brand' => $brand,
        ]);
    }

    /**
     * Delete a given Brand according to parameters
     * 
     * @param Brand $brand
     * @return Response
     * @Route("/brand/delete/{brand}/{confirm}", name="brand_delete")
     */
    public function deleteBrand(Brand $brand = null, $confirm = false)
    {
        // If Brand not found, then we redirect the user to the Brands index page
        if ($brand == null) {
            $this->addFlash('danger', "La marque que vous essayez de supprimer n'existe pas");
            return $this->redirectToRoute("brand_index");
        }

        if($confirm == true){
            // Saving the Brand's name to use it in the flash message
            $formerBrandName = $brand->getName();
    
            // Removing the Brand from database
            $manager = $this->getDoctrine()->getManager();
            $manager->remove($brand);
            $manager->flush();
    
            // Adding a flash message to inform the user
            $this->addFlash('success', "Votre marque $formerBrandName a bien été supprimée");
            return $this->redirectToRoute("brand_index");
        }

        return $this->render('brand/delete.html.twig', [
            'brand' => $brand,
        ]);
    }
}
