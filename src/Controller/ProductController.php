<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\BrandRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
            $jsonProductList = $serializer->serialize($productDetail, 'json', $context);
            return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/api/products', name:"createProduct", methods: ['POST'])]
    public function createProduct(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, BrandRepository $brandRepository): JsonResponse
    {
        $product = $serializer->deserialize($request->getContent(), Product::class, 'json');

        $content = $request->toArray();

        $idBrand = $content['brandId'] ?? -1;

        $product->setBrand($brandRepository->find($idBrand));

        $em->persist($product);
        $em->flush();

        $context = SerializationContext::create()->setGroups(['getProducts']);
        $jsonProduct = $serializer->serialize($product, 'json', $context);

        $location = $urlGenerator->generate('app_product_detail', ['id' => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonProduct, Response::HTTP_CREATED, ["Location" => $location], true);
    }


    #[Route('/api/products/product/{id}/delete', name: 'app_product_delete', methods: ['DELETE'])]
    public function deleteProduct(Product $product, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($product);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
