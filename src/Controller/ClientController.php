<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

class ClientController extends AbstractController
{
    #[Route('/api/clients', name: 'app_client', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: 'You don\'t have the rights to visualize the list of clients')]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of all clients',
        content: new Model(type: Client::class, groups: ['getClients'])
    )]
    #[OA\Tag(name: 'Client')]
    public function index(
        ClientRepository $clientRepository,
        SerializerInterface $serializer
    ): JsonResponse {
        $userList = $clientRepository->findAll();

        $context = SerializationContext::create()->setGroups(['getClients']);
        $jsonUserList = $serializer->serialize($userList, 'json', $context);

        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }

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
    #[OA\Tag(name: 'Client')]
    public function findUsersOfClient(
        ClientRepository $clientRepository,
        SerializerInterface $serializer,
        int $id
    ): JsonResponse {
        $userList = $clientRepository->find($id);

        $context = SerializationContext::create()->setGroups(['getClients']);
        $jsonUserList = $serializer->serialize($userList, 'json', $context);

        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }
}
