<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ProductController extends AbstractController
{
    #[Route(
        '/api/products',
        name: 'app_product',
        methods: ['GET']
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of the products',
        content: new Model(type: Product::class, groups: ['getProducts']),
    )]
    #[OA\Parameter(
        name: 'page',
        description: 'The page where to start the research',
        in: 'query',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Parameter(
        name: 'limit',
        description: 'How many results to include in the research',
        in: 'query',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Tag(name: 'Product')]
    public function index(
        ProductRepository $productRepository,
        SerializerInterface $serializer,
        Request $request,
        TagAwareCacheInterface $cache
    ): JsonResponse {

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getAllProducts-$page-$limit";
        $productList = $cache->get($idCache, function (ItemInterface $item) use ($productRepository, $page, $limit) {
            echo "No products in cache\n";
            $item->tag("productsCache");
            return $productRepository->findAllWithPagination($page, $limit);
        });

        $context = SerializationContext::create()->setGroups(['getProducts']);
        $jsonProductList = $serializer->serialize($productList, 'json', $context);

        return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/products/{id}', name: 'app_product_detail', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the details of one product',
        content: new Model(type: Product::class, groups: ['getProducts'])
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'The id number of the product',
        in: 'path',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Tag(name: 'Product')]
    public function details(
        ProductRepository $productRepository,
        SerializerInterface $serializer,
        int $id
    ): JsonResponse {
        $productDetail = $productRepository->find($id);

        if ($productDetail) {
            $context = SerializationContext::create()->setGroups(['getProducts']);
            $jsonProductDetail = $serializer->serialize($productDetail, 'json', $context);
            return new JsonResponse($jsonProductDetail, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
