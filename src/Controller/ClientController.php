<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClientController extends AbstractController
{
    #[Route('/api/clients', name: 'app_client')]
    #[IsGranted('ROLE_ADMIN', message: 'You don\'t have the visualize the list of clients')]
    public function index(
        ClientRepository    $clientRepository,
        SerializerInterface $serializer
    ): JsonResponse {
        $userList = $clientRepository->findAll();

        $context = SerializationContext::create()->setGroups(['getClients']);
        $jsonUserList = $serializer->serialize($userList, 'json', $context);

        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/clients/client/{id}', name: 'app_client_users')]
    #[IsGranted('ROLE_ADMIN', message: 'You don\'t have the right to create a user')]
    public function findUsersOfClient(
        ClientRepository    $clientRepository,
        SerializerInterface $serializer,
        int                 $id
    ): JsonResponse {
        $userList = $clientRepository->find($id);

        $context = SerializationContext::create()->setGroups(['getClients']);
        $jsonUserList = $serializer->serialize($userList, 'json', $context);

        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }
}
