<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use OpenApi\Attributes as OA;

class UserController extends AbstractController
{
    #[Route('/api/users/{id}', name: 'app_user_detail', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: 'You don\'t have the right to view the details of a user')]
    #[OA\Response(
        response: 200,
        description: 'Returns the details of one user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: User::class, groups: ['detailUser']))
        )
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'The id number of the user',
        in: 'path',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Tag(name: 'User')]
    public function detailsUser(
        UserRepository $userRepository,
        SerializerInterface $serializer,
        int $id
    ): JsonResponse {
        $userDetails = $userRepository->find($id);

        if ($userDetails) {
            $context = SerializationContext::create()->setGroups(['getUsers']);
            $jsonUserDetails = $serializer->serialize($userDetails, 'json', $context);
            return new JsonResponse($jsonUserDetails, Response::HTTP_OK, [], true);
        }

        return new JsonResponse('User not found', Response::HTTP_NOT_FOUND);
    }

    #[Route('/api/users', name: "app_create_user", methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'You don\'t have the right to create a user')]
    #[OA\Response(
        response: 200,
        description: 'Create one user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: User::class, groups: ['getUsers']))
        )
    )]
    #[OA\RequestBody(
        description: 'Body of the user to create',
        required: true,
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: User::class, groups: ['createUser']))
        )
    )]
    #[OA\Tag(name: 'User')]
    public function createUser(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator,
        ClientRepository $clientRepository,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        $content = $request->toArray();

        $user->setRoles(['ROLE_USER']);

        if (!isset($content['clientId'])) {
            throw new BadRequestException('The clientId must not be empty', 400);
        }

        $idClient = $content['clientId'];

        $user->setClient($clientRepository->find($idClient));

        $hashedPassword = $passwordHasher->hashPassword($user, $content['password']);
        $user->setPassword($hashedPassword);

        $em->persist($user);
        $em->flush();

        $context = SerializationContext::create()->setGroups(['getUsers']);
        $jsonUser = $serializer->serialize($user, 'json', $context);

        $location = $urlGenerator->generate(
            'app_user_detail',
            ['id' => $user->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["Location" => $location], true);
    }


    #[Route('/api/users/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'You don\'t have the right to delete a user')]
    #[OA\Response(
        response: 204,
        description: 'Delete one user'
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'The id number of the user',
        in: 'path',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Tag(name: 'User')]
    public function deleteUser(
        User $user,
        EntityManagerInterface $em
    ): JsonResponse {
        $em->remove($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/users/{id}', name: 'app_user_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'You don\'t have the right to update the details of a user')]
    #[OA\Response(
        response: 200,
        description: 'Returns the details of one user after an update',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: User::class, groups: ['detailUser']))
        )
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'The id number of the user',
        in: 'path',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        description: 'Body of the user to update',
        required: true,
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: User::class, groups: ['updateUser']))
        )
    )]
    #[OA\Tag(name: 'User')]
    public function updateUser(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator,
        int $id
    ): JsonResponse {

        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException(
                'No user found for id ' . $id
            );
        }

        $bodyUser = $serializer->deserialize($request->getContent(), User::class, 'json');

        if ($bodyUser->getEmail() !== '') {
            $user->setEmail($bodyUser->getEmail());
        }

        if ($bodyUser->getPassword() !== '') {
            $user->setPassword($bodyUser->getPassword());
        }

        if ($bodyUser->getName() !== '') {
            $user->setName($bodyUser->getName());
        }

        if ($bodyUser->getLastname() !== '') {
            $user->setLastname($bodyUser->getLastname());
        }

        if ($bodyUser->getTelephone() !== '') {
            $user->setTelephone($bodyUser->getTelephone());
        }

        $em->flush();

        $context = SerializationContext::create()->setGroups(['getUsers']);
        $jsonUser = $serializer->serialize($user, 'json', $context);

        $location = $urlGenerator->generate(
            'app_user_detail',
            ['id' => $id],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse($jsonUser, Response::HTTP_OK, ["Location" => $location], true);
    }
}
