<?php

namespace App\Controller;

use App\Form\SearchType;
use App\Repository\BrandRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BrandController extends AbstractController
{
    /**
     * @Route("/brand", name="brand_index")
     */
    public function index(BrandRepository $brandRepository, Request $request): Response
    {
        $searchForm = $this->createForm(SearchType::class);
        $searchForm->handleRequest($request);
        if($searchForm->isSubmitted() && $searchForm->isValid()){
            dump($request);
            $search_category = $request->request->get('search')['search_category'];
            $search_text = $request->request->get('search')['search_text'];
            $brands = $brandRepository->searchBrands($search_category, $search_text);
        } else {
            $brands = $brandRepository->findAll();
        }
        return $this->render('brand/index.html.twig', [
            'brands' => $brands,
            'searchForm' => $searchForm->createView(),
        ]);
    }
}
