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
    #[Route('/api/products', name: 'app_product', methods: ['GET'])]
    public function index(ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        $productList = $productRepository->findAll();

        $context = SerializationContext::create()->setGroups(['getProducts']);
        $jsonProductList = $serializer->serialize($productList, 'json', $context);

        return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/products/product/{id}', name: 'app_product_detail', methods: ['GET'])]
    public function details(ProductRepository $productRepository, SerializerInterface $serializer, int $id): JsonResponse
    {
        $productDetail = $productRepository->find($id);

        if($productDetail){
            $context = SerializationContext::create()->setGroups(['getProducts']);
            $jsonProductDetail = $serializer->serialize($productDetail, 'json', $context);
            return new JsonResponse($jsonProductDetail, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
