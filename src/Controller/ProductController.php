<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use Cocur\Slugify\Slugify;
use App\Form\ProductSearchType;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    /**
     * Shows a list of Products, with or without searching
     * 
     * @param ProductRepository $productRepository
     * @param Request $request
     * @return Response
     * @Route("/product", name="product_index")
     */
    public function index(ProductRepository $productRepository, Request $request): Response
    {
        // Form creation
        $searchForm = $this->createForm(ProductSearchType::class);

        // Request handling to check if the form has been submitted
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $search_category = $request->request->get('product_search')['search_category'];
            $search_text = $request->request->get('product_search')['search_text'];
            // Using the parameters from the request, we search specific products
            $products = $productRepository->searchProducts($search_category, $search_text);
        } else {
            $products = $productRepository->findAll();
        }
        return $this->render('product/index.html.twig', [
            'products' => $products,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    /**
     * Shows a detailled view of the Product chosen
     * 
     * @param Product $product
     * @return Response
     * 
     * @Route("/product/view/{product}", name="product_view")
     */
    public function getBrand(Product $product = null){
        // If Product not found, then we redirect the user to the Products index page
        if ($product == null) {
            $this->addFlash('danger', "Le produit que vous essayez de visionner n'existe pas");
            return $this->redirectToRoute("product_index");
        }

        return $this->render('product/view.html.twig', [
            'product' => $product
        ]);
    }

    /**
     * Adds a Product to the database form the form submitted
     * 
     * @param Request $request
     * @return Response
     * @Route("/product/add", name="product_add")
     */
    public function addProduct(Request $request)
    {
        $product = new Product();
        // Slugify initialisation to create the slug data using the name
        $slugify = new Slugify();
        // Form creation
        $addForm = $this->createForm(ProductType::class, $product);
        // Request handling to check if the form has been submitted
        $addForm->handleRequest($request);
        if ($addForm->isSubmitted() && $addForm->isValid()) {
            // Checking the integrity of Product price
            if(!is_numeric($product->getPrice()) || $product->getPrice() < 0){
                $product->setPrice(0);
            }
            $product->setSlug($slugify->slugify($product->getName()));
            // Persistence in database 
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($product);
            $manager->flush();
            // Adding a flash message to inform the user
            $this->addFlash('success', "Votre produit {$product->getName()} a bien été ajoutée");
            return $this->redirectToRoute('product_index');
        }
        return $this->render('product/add.html.twig', [
            'addForm' => $addForm->createView(),
        ]);
    }

    /**
     * Modify a given Product from the submitted form
     * 
     * @param Request $request
     * @param Product $product
     * @return Response
     * @Route("/product/edit/{product}", name="product_edit")
     */
    public function editProduct(Request $request, Product $product = null)
    {
        // If Product not found, then we redirect the user to the Products index page
        if ($product == null) {
            $this->addFlash('danger', "Le produit que vous essayez de modifier n'existe pas");
            return $this->redirectToRoute("product_index");
        }
        // Slugify initialisation to create the slug data using the name
        $slugify = new Slugify();
        // Form creation
        $editForm = $this->createForm(ProductType::class, $product);
        // Request handling to check if the form has been submitted
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            // Checking the integrity of Product price
            if(!is_numeric($product->getPrice()) || $product->getPrice() < 0){
                $product->setPrice(0);
            }
            $product->setSlug($slugify->slugify($product->getName()));
            // Persistence in database 
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($product);
            $manager->flush();
            // Adding a flash message to inform the user
            $this->addFlash('success', "Votre produit {$product->getName()} a bien été modifié");
            return $this->redirectToRoute("product_index");
        }
        return $this->render('product/edit.html.twig', [
            'editForm' => $editForm->createView(),
            'product' => $product,
        ]);
    }

    /**
     * Delete a Product given in parameters
     * 
     * @param Product $product
     * @return Response
     * @Route("/product/delete/{product}/{confirm}", name="product_delete")
     */
    public function deleteProduct(Product $product = null, $confirm = false)
    {
        // If Product not found, then we redirect the user to the Products index page
        if ($product == null) {
            $this->addFlash('danger', "La produit que vous essayez de supprimer n'existe pas");
            return $this->redirectToRoute("product_index");
        }

        if($confirm == true){
            // Saving the Product's name to use it in the flash message
            $formerProductName = $product->getName();
    
            // Removing the Product from database
            $manager = $this->getDoctrine()->getManager();
            $manager->remove($product);
            $manager->flush();
    
            // Adding a flash message to inform the user
            $this->addFlash('success', "Votre produit $formerProductName a bien été supprimée");
            return $this->redirectToRoute("product_index");
        }

        return $this->render('product/delete.html.twig', [
            'product' => $product,
        ]);
    }
}
