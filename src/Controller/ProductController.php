<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/api/product', name: 'app_product')]
    public function index(ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        $productList = $productRepository->findAll();

        $context = SerializationContext::create()->setGroups(['getProducts']);
        $jsonProductList = $serializer->serialize($productList, 'json', $context);

        return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
    }
}
