<?php

namespace App\Controller;

use App\Entity\Brand;
use Cocur\Slugify\Slugify;
use Doctrine\DBAL\Exception;
use App\Repository\BrandRepository;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiBrandController extends AbstractController
{
    /**
     * Api function to get all the Brands
     * 
     * @param BrandRepository $brandRepository
     * @return Response
     * 
     * @Route("/api/brands/list", name="api_brands_list", methods={"GET"})
     */
    public function index(BrandRepository $brandRepository): Response
    {
        // Getting all the Brands
        $brands = $brandRepository->findAll();

        // We use JSON Encoder
        $encoders = [new JsonEncoder()];

        // We use a normalizer to convert collection into array
        $normalizers = [new ObjectNormalizer()];

        // We instantiate the converter
        $serializer = new Serializer($normalizers, $encoders);

        // We convert into JSON and instantiate the Response
        $jsonBrands = $serializer->serialize($brands, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
        $response = new Response($jsonBrands);

        // We specify HTTP header request
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Api function to get only one Brand
     * 
     * @param Brand $brand
     * @return Response
     * 
     * @Route("/api/brands/search/{id}", name="api_brands_search", methods={"GET"})
     */
    public function getSearchedBrand(Brand $brand = null): Response
    {
        // We give a direct 404 Error if no Brand found for that id
        if ($brand == null) {
            $error = 404;
            return new Response('La marque recherchée n\'a pas été trouvée', $error);
        }

        // We use JSON Encoder
        $encoders = [new JsonEncoder()];

        // We use a normalizer to convert collection into array
        $normalizers = [new ObjectNormalizer()];

        // We instantiate the converter
        $serializer = new Serializer($normalizers, $encoders);

        // We convert into JSON and instantiate the Response
        $jsonBrand = $serializer->serialize($brand, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
        $response = new Response($jsonBrand);

        // We specify HTTP header request
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Api function to add a Brand
     * 
     * @param Request $request
     * @return Response
     * 
     * @Route("/api/brands/add", name="api_brands_add", methods={"POST"})
     */
    public function addBrands(Request $request): Response
    {
        // We create a new Brand
        $brand = new Brand();

        $slugify = new Slugify();
        $manager = $this->getDoctrine()->getManager();
        // We decode the Brand received
        $brandData = json_decode($request->getContent());

        // With the data from Brand, we hydrate our object
        try {
            $brand->setName($brandData->name)
                ->setLogo($brandData->logo)
                ->setCreationDate(new \DateTime($brandData->creation_date))
                ->setNationality($brandData->nationality)
                ->setSlogan($brandData->slogan)
                ->setWebsite($brandData->website)
                ->setSlug($slugify->slugify($brandData->name));
            $manager->persist($brand);
            $manager->flush();
        } catch (Exception $e) {
            // Data Exception
            return new Response('Les données saisies sont invalides', 500);
        } catch (\Exception $e) {
            // Other Exceptions
            return new Response($e->getMessage(), 500);
        }

        return new Response("La marque {$brand->getName()} a bien été ajoutée", 201);
    }

    /**
     * Api function to edit a Brand and create it if not found
     * 
     * @param Brand $brand
     * @param Request $request
     * @return Response
     * 
     * @Route("/api/brands/edit/{id}", name="api_brands_edit", methods={"PUT"})
     */
    public function editArticle(Brand $brand = null, Request $request)
    {

        // We decode sent data
        $brandData = json_decode($request->getContent());
        $slugify = new Slugify();
        $manager = $this->getDoctrine()->getManager();

        // We initialize the return code
        $code = 200;

        // If Brand not found
        if ($brand == null) {
            // We create a new Brand and change response code
            $brand = new Brand();
            $code = 201;
        }

        // With the data from Brand, we hydrate our object
        try {
            $brand->setName($brandData->name)
                ->setLogo($brandData->logo)
                ->setCreationDate(new \DateTime($brandData->creation_date))
                ->setNationality($brandData->nationality)
                ->setSlogan($brandData->slogan)
                ->setWebsite($brandData->website)
                ->setSlug($slugify->slugify($brandData->name));
            $manager->persist($brand);
            $manager->flush();
        } catch (Exception $e) {
            // Data Exception
            return new Response('Les données saisies sont invalides', 500);
        } catch (\Exception $e) {
            // Other Exceptions
            return new Response($e->getMessage(), 500);
        }

        // We save in database
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($brand);
        $entityManager->flush();

        // We initialize the message (edit or new Brand)
        if($code == 200){
            $message = "Cette marque a bien été modifiée";
        } else {
            $message = "Cette marque n'ayant pas été trouvée, elle a été créée à la place";
        }
        // And we send the Response
        return new Response($message, $code);
    }

    /**
     * Api function to delete a Brand if found
     * 
     * @param Brand $brand
     * @return Response
     * 
     * @Route("/api/brands/delete/{id}", name="api_brands_delete", methods={"DELETE"})
     */
    public function removeArticle(Brand $brand = null)
    {
        // We send a Response if Brand not found
        if($brand == null){
            return new Response('Cette marque n\'existe pas. Inutile de la supprimer', 404);
        }

        // We remove the Brand from database and send confirmation message in Response
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($brand);
        $manager->flush();
        return new Response('Cette marque a bien été supprimée', 200);
    }
}
