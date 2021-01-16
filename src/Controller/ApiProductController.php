<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\BrandRepository;
use Cocur\Slugify\Slugify;
use Doctrine\DBAL\Exception;
use App\Repository\ProductRepository;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiProductController extends AbstractController
{
    /**
     * Api function to get all the products
     * 
     * @Route("/api/products/list", name="api_products_list", methods={"GET"})
     */
    public function index(ProductRepository $productRepository): Response
    {
        // Getting all the products
        $products = $productRepository->findAll();

        // We use JSON Encoder
        $encoders = [new JsonEncoder()];

        // We use a normalizer to convert collection into array
        $normalizers = [new ObjectNormalizer()];

        // We instantiate the converter
        $serializer = new Serializer($normalizers, $encoders);

        // We convert into JSON and instantiate the Response
        $jsonProducts = $serializer->serialize($products, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
        $response = new Response($jsonProducts);

        // We specify HTTP header request
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Api function to get only one product
     * 
     * @param Product $product
     * @return Response
     * 
     * @Route("/api/products/search/{id}", name="api_products_search", methods={"GET"})
     */
    public function getSearchedProduct(Product $product = null): Response
    {
        if ($product == null) {
            $error = 404;
            return new Response('Le produit recherché n\'a pas été trouvé', $error);
        }

        // We use JSON Encoder
        $encoders = [new JsonEncoder()];

        // We use a normalizer to convert collection into array
        $normalizers = [new ObjectNormalizer()];

        // We instantiate the converter
        $serializer = new Serializer($normalizers, $encoders);

        // We convert into JSON and instantiate the Response
        $jsonProduct = $serializer->serialize($product, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
        $response = new Response($jsonProduct);

        // We specify HTTP header request
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Api function to add a product
     * 
     * @param Request $request
     * @return Response
     * 
     * @Route("/api/products/add", name="api_products_add", methods={"POST"})
     */
    public function addProducts(Request $request, BrandRepository $brandRepository): Response
    {
        // We create a new Product
        $product = new Product();

        $slugify = new Slugify();
        $manager = $this->getDoctrine()->getManager();
        // We decode the products received
        $productData = json_decode($request->getContent());

        // With the data form products, we hydrate our objects
        try {
            $product->setName($productData->name)
                ->setPrice($productData->price)
                ->setCreationDate(new \DateTime($productData->creation_date))
                ->setDescription($productData->description)
                ->setSlug($slugify->slugify($productData->name));
            if($brandRepository->findOneById($productData->brand) != null){
                $product->setBrand($brandRepository->findOneById($productData->brand));
            } else {
                return new Response('La marque à associer à ce produit n\'a pas été trouvée', 404);
            }
            $manager->persist($product);
            $manager->flush();
        } catch (Exception $e) {
            return new Response('Les données saisies sont invalides', 500);
        } catch (\Exception $e) {
            return new Response($e->getMessage(), 500);
        }

        return new Response("Le produit {$product->getName()} a bien été ajouté", 201);
    }

    /**
     * Api function to edit a product and create it if not found
     * 
     * @param Product $product
     * @param Request $request
     * @return Response
     * 
     * @Route("/api/products/edit/{id}", name="api_products_edit", methods={"PUT"})
     */
    public function editArticle(Product $product = null, Request $request, BrandRepository $brandRepository)
    {

        // On décode les données envoyées
        $productData = json_decode($request->getContent());
        $slugify = new Slugify();
        $manager = $this->getDoctrine()->getManager();

        // On initialise le code de réponse
        $code = 200;

        // Si l'product n'est pas trouvé
        if ($product == null) {
            // On instancie un nouvel product
            $product = new Product();
            // On change le code de réponse
            $code = 201;
        }

        // With the data form products, we hydrate our objects
        try {
            $product->setName($productData->name)
                ->setPrice($productData->price)
                ->setCreationDate(new \DateTime($productData->creation_date))
                ->setDescription($productData->description)
                ->setSlug($slugify->slugify($productData->name));
            if($brandRepository->findOneById($productData->brand) != null){
                $product->setBrand($brandRepository->findOneById($productData->brand));
            } else {
                return new Response('La marque à associer à ce produit n\'a pas été trouvée', 404);
            }
            $manager->persist($product);
            $manager->flush();
        } catch (Exception $e) {
            return new Response('Les données saisies sont invalides', 500);
        } catch (\Exception $e) {
            return new Response($e->getMessage(), 500);
        }

        // On sauvegarde en base
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($product);
        $entityManager->flush();

        if($code == 200){
            $message = "Ce produit a bien été modifié";
        } else {
            $message = "Ce produit n'ayant pas été trouvé, il a été créé à la place";
        }
        // On retourne la confirmation
        return new Response($message, $code);
    }

    /**
     * Api function to delete a product if found
     * 
     * @param Product $product
     * @return Response
     * 
     * @Route("/api/products/delete/{id}", name="api_products_delete", methods={"DELETE"})
     */
    public function removeArticle(Product $product = null)
    {
        if($product == null){
            return new Response('Ce produit n\'existe pas. Inutile de le supprimer', 404);
        }

        $manager = $this->getDoctrine()->getManager();
        $manager->remove($product);
        $manager->flush();
        return new Response('Ce produit a bien été supprimé', 200);
    }
}
