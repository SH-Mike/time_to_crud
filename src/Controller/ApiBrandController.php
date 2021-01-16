<?php

namespace App\Controller;

use App\Repository\BrandRepository;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiBrandController extends AbstractController
{
    /**
     * Api function to get all the brands
     * 
     * @Route("/api/brands/list", name="api_brands_list", methods={"GET"})
     */
    public function index(BrandRepository $brandRepository): Response
    {
        // Getting all the brands
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
}
