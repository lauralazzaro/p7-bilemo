<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClientController extends AbstractController
{
    #[Route('/api/clients/client/{id}', name: 'app_client')]
    public function index(
        ClientRepository    $clientRepository,
        SerializerInterface $serializer,
        int                 $id): JsonResponse
    {
        $userList = $clientRepository->find($id);

        $context = SerializationContext::create()->setGroups(['getClients']);
        $jsonUserList = $serializer->serialize($userList, 'json', $context);

        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }
}
