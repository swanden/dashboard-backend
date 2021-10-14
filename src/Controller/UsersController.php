<?php

namespace App\Controller;

use App\Model\User\Entity\User\Id;
use App\Model\User\Entity\User\Role;
use App\Model\User\Entity\User\UserRepository;
use App\Model\User\UseCase\Create;
use App\Model\User\UseCase\Edit;
use App\Model\User\UseCase\Block;
use App\Model\User\UseCase\Activate;
use App\ReadModel\User\UserFetcher;
use App\Validator\RequestType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/users', name:'users')]
#[IsGranted('ROLE_MANAGE_USERS')]
final class UsersController extends BaseController
{
    public function __construct(
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    )
    {
        parent::__construct();
    }

    #[Route('', name: '', methods: ['GET'])]
    public function index(UserFetcher $users): JsonResponse
    {
        $dateFormat = $this->container->get('parameter_bag')->get('app.date_format');

        return $this->json(array_map(static function (array $item) use ($dateFormat) {
            return [
                'id' => $item['id'],
                'firstname' => $item['firstname'],
                'lastname' => $item['lastname'],
                'date' => (new \DateTimeImmutable($item['date']))->format($dateFormat),
                'email' => $item['email'],
                'role' => Role::from($item['role'])->getUCFirstName(),
                'status' => ucfirst(strtolower($item['status'])),
            ];
        }, $users->all()));
    }

    #[Route('/create', name: '.create', methods: ['POST'])]
    public function create(Request $request, Create\Handler $handler): JsonResponse
    {
        $errors = $this->requestValidator->validate(Create\Command::class, $request, RequestType::BODY);
        if (\count($errors)) {
            return $this->json([
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var Create\Command $command */
        $command = $this->serializer->deserialize($request->getContent(), Create\Command::class, 'json');

        $errors = $this->validator->validate($command);
        if (\count($errors)) {
            $json = $this->serializer->serialize($errors, 'json');
            return new JsonResponse($json, Response::HTTP_BAD_REQUEST, [], true);
        }

        $id = $handler->handle($command);

        return new JsonResponse(['id' => $id], status: Response::HTTP_CREATED);
    }

    #[Route('/edit', name: '.edit', methods: ['POST'])]
    public function edit(Request $request, Edit\Handler $handler, UserRepository $users): JsonResponse
    {
        $errors = $this->requestValidator->validate(Edit\Command::class, $request, RequestType::BODY);
        if (\count($errors)) {
            return $this->json([
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var Edit\Command $command */
        $command = $this->serializer->deserialize($request->getContent(), Edit\Command::class, 'json');

        $errors = $this->validator->validate($command);
        if (\count($errors)) {
            $json = $this->serializer->serialize($errors, 'json');
            return new JsonResponse($json, Response::HTTP_BAD_REQUEST, [], true);
        }

        $user = $users->get(new Id($command->id));
        if ($user->getId()->getValue() === $this->getUser()->getId()) {
            return $this->json([
                'error' => [
                    'message' => 'Unable to edit yourself.',
                    'code' => Response::HTTP_FORBIDDEN
                ]
            ], Response::HTTP_FORBIDDEN);
        }

        $handler->handle($command);

        return new JsonResponse(status: Response::HTTP_OK);
    }

    #[Route('/block', name: '.block', methods: ['POST'])]
    public function block(Request $request, Block\Handler $handler, UserRepository $users): JsonResponse
    {
        $errors = $this->requestValidator->validate(Block\Command::class, $request, RequestType::BODY);
        if (\count($errors)) {
            return $this->json([
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var Block\Command $command */
        $command = $this->serializer->deserialize($request->getContent(), Block\Command::class, 'json');

        $errors = $this->validator->validate($command);
        if (\count($errors)) {
            $json = $this->serializer->serialize($errors, 'json');
            return new JsonResponse($json, Response::HTTP_BAD_REQUEST, [], true);
        }

        $user = $users->get(new Id($command->id));
        if ($user->getId()->getValue() === $this->getUser()->getId()) {
            return $this->json([
                'error' => [
                    'message' => 'Unable to block yourself.',
                    'code' => Response::HTTP_FORBIDDEN
                ]
            ], Response::HTTP_FORBIDDEN);
        }

        $handler->handle($command);

        return new JsonResponse(status: Response::HTTP_OK);
    }

    #[Route('/activate', name: '.activate', methods: ['POST'])]
    public function activate(Request $request, Activate\Handler $handler, UserRepository $users): JsonResponse
    {
        $errors = $this->requestValidator->validate(Activate\Command::class, $request, RequestType::BODY);
        if (\count($errors)) {
            return $this->json([
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var Activate\Command $command */
        $command = $this->serializer->deserialize($request->getContent(), Activate\Command::class, 'json');

        $errors = $this->validator->validate($command);
        if (\count($errors)) {
            $json = $this->serializer->serialize($errors, 'json');
            return new JsonResponse($json, Response::HTTP_BAD_REQUEST, [], true);
        }

        $user = $users->get(new Id($command->id));
        if ($user->getId()->getValue() === $this->getUser()->getId()) {
            return $this->json([
                'error' => [
                    'message' => 'Unable to activate yourself.',
                    'code' => Response::HTTP_FORBIDDEN
                ]
            ], Response::HTTP_FORBIDDEN);
        }

        $handler->handle($command);

        return new JsonResponse(status: Response::HTTP_OK);
    }
}