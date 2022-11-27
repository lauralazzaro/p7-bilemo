<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Service\VersioningService;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ClientController extends AbstractController
{
//    #[Route('/api/clients', name: 'app_client', methods: ['GET'])]
//    #[IsGranted('ROLE_ADMIN', message: 'You don\'t have the rights to visualize the list of clients')]
//    #[OA\Response(
//        response: 200,
//        description: 'Returns the list of all clients',
//        content: new Model(type: Client::class, groups: ['getClients'])
//    )]
//    #[OA\Tag(name: 'Client')]
//    public function index(
//        ClientRepository $clientRepository,
//        SerializerInterface $serializer
//    ): JsonResponse {
//        $userList = $clientRepository->findAll();
//
//        $context = SerializationContext::create()->setGroups(['getClients']);
//        $jsonUserList = $serializer->serialize($userList, 'json', $context);
//
//        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
//    }

    #[Route('/api/clients/{id}/users', name: 'app_client_users', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: 'You don\'t have the rights to view the list of users')]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of all the users linked to one client',
        content: new Model(type: Client::class, groups: ['getClients', 'getUsers'])
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'The id number of the client',
        in: 'path',
        schema: new OA\Schema(type: 'integer')
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
    #[OA\Tag(name: 'Client')]
    public function findUsersOfClient(
        VersioningService $versioningService,
        ClientRepository $clientRepository,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache,
        Request $request,
        int $id
    ): JsonResponse {
        $version = $versioningService->getVersion();
        $idCache = "getUsersOfClient-$id";
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $jsonUserList = $cache->get(
            $idCache,
            function (ItemInterface $item) use ($clientRepository, $id, $serializer, $version, $page, $limit) {
                echo "No user in cache for this client\n";
                $item->tag("usersClientCache");
                $item->expiresAfter(60);
                $userList = $clientRepository->findUserWithPagination($page, $limit, $id);
                $context = SerializationContext::create()->setGroups(['getClients']);
                $context->setVersion($version);
                return $serializer->serialize($userList, 'json', $context);
            }
        );

        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }
}
