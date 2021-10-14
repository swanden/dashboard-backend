<?php

namespace App\Controller;

use App\ReadModel\User\UserFetcher;
use App\Model\User\UseCase\Name;
use App\Validator\RequestType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ProfileController extends BaseController
{
    public function __construct(
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private UserFetcher $users
    )
    {
        parent::__construct();
    }

    #[Route('/profile', name: 'profile', methods: ['GET'])]
    public function show(): JsonResponse
    {
        $user = $this->users->get($this->getUser()->getId());

        return $this->json([
            'first' => $user->getName()->getFirst(),
            'last' => $user->getName()->getLast(),
            'email' => $user->getEmail()->getValue(),
            'created' => $user->getDate()->format($this->getParameter('app.datetime_format')),
            'role' => $user->getRole()->getUCFirstName(),
            'status' => $user->getUCFirstStatus(),
        ], Response::HTTP_OK);
    }

    #[Route('/profile/name', name: 'profile.name', methods: ['POST'])]
    public function name(Request $request, Name\Handler $handler): JsonResponse
    {
        $errors = $this->requestValidator->validate(Name\Command::class, $request, RequestType::BODY);
        if (\count($errors)) {
            return $this->json([
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var Name\Command $command */
        $command = $this->serializer->deserialize($request->getContent(), Name\Command::class, 'json', [
            'object_to_populate' => new Name\Command($this->getUser()->getId()),
            'ignored_attributes' => ['id'],
        ]);

        $errors = $this->validator->validate($command);
        if (\count($errors)) {
            $json = $this->serializer->serialize($errors, 'json');
            return new JsonResponse($json, Response::HTTP_BAD_REQUEST, [], true);
        }

        $handler->handle($command);

        return new JsonResponse(status: Response::HTTP_OK);
    }

    #[Route('/profile/role', name: 'profile.role', methods: ['GET'])]
    public function role(): Response
    {
        return $this->json($this->getUser()->getRoles(), Response::HTTP_OK);
    }
}