<?php

namespace App\Controller\Auth;

use App\Model\User\UseCase\Reset;
use App\Controller\BaseController;
use App\ReadModel\User\UserFetcher;
use App\Validator\RequestType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ResetController extends BaseController
{
    public function __construct(
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    )
    {
        parent::__construct();
    }

    #[Route('/auth/reset/request', name: 'auth.reset', methods: ["POST"])]
    public function request(Request $request, Reset\Request\Handler $handler): JsonResponse
    {
        $errors = $this->requestValidator->validate(Reset\Request\Command::class, $request, RequestType::BODY);
        if (\count($errors)) {
            return $this->json([
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var Reset\Request\Command $command */
        $command = $this->serializer->deserialize($request->getContent(), Reset\Request\Command::class, 'json');

        $errors = $this->validator->validate($command);
        if (\count($errors)) {
            $json = $this->serializer->serialize($errors, 'json');
            return new JsonResponse($json, Response::HTTP_BAD_REQUEST, [], true);
        }

        $handler->handle($command);

        return new JsonResponse(status: Response::HTTP_CREATED);
    }

    #[Route('/auth/reset', name: 'auth.reset.confirm', methods: ["POST"])]
    public function reset(Request $request, Reset\Reset\Handler $handler, UserFetcher $users): JsonResponse
    {
        $errors = $this->requestValidator->validate(Reset\Reset\Command::class, $request, RequestType::BODY);
        if (\count($errors)) {
            return $this->json([
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var Reset\Reset\Command $command */
        $command = $this->serializer->deserialize($request->getContent(), Reset\Reset\Command::class, 'json');

        $errors = $this->validator->validate($command);
        if (\count($errors)) {
            $json = $this->serializer->serialize($errors, 'json');
            return new JsonResponse($json, Response::HTTP_BAD_REQUEST, [], true);
        }

        if (!$users->existsByResetToken($command->token)) {
            return $this->json([
                'error' => [
                    'message' => 'Incorrect or already confirmed token.',
                    'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                ]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $handler->handle($command);

        return new JsonResponse(status: Response::HTTP_OK);
    }

    #[Route('/auth/reset/validate_token/{token}', name: 'auth.reset.token.validate', methods: ["GET"])]
    public function validate_token(string $token, UserFetcher $users): JsonResponse
    {
        if (!$users->existsByResetToken($token)) {
            return $this->json([
                'error' => [
                    'message' => 'Incorrect or already confirmed token.',
                    'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                ]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(status: Response::HTTP_OK);
    }
}