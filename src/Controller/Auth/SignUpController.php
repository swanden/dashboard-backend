<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Controller\BaseController;
use App\Model\User\UseCase\SignUp;
use App\Validator\RequestType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

final class SignUpController extends BaseController
{
    public function __construct(
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    )
    {
        parent::__construct();
    }

    #[Route('/auth/signup', name: 'auth.signup', methods: ['POST'])]
    public function request(Request $request, SignUp\Request\Handler $handler): Response
    {
        $errors = $this->requestValidator->validate(SignUp\Request\Command::class, $request);
        if (\count($errors)) {
            return $this->json([
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var SignUp\Request\Command $command */
        $command = $this->serializer->deserialize($request->getContent(), SignUp\Request\Command::class, 'json');
//        $command = new SignUp\Request\Command();
//        $command->email = $request->request->get('email');
//        $command->password = $request->request->get('password');

        $violations = $this->validator->validate($command);
        if (\count($violations)) {
            $json = $this->serializer->serialize($violations, 'json');
            return new JsonResponse($json, Response::HTTP_BAD_REQUEST, [], true);
        }

        $handler->handle($command);

        return new JsonResponse(status: Response::HTTP_CREATED);
    }

    #[Route('/auth/signup/confirm', name: 'auth.signup.confirm')]
    public function confirm(Request $request, SignUp\Confirm\Handler $handler): JsonResponse
    {
        $errors = $this->requestValidator->validate(SignUp\Confirm\Command::class, $request, RequestType::GET);
        if (\count($errors)) {
            return $this->json([
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var SignUp\Confirm\Command $command */
//        $command = $this->serializer->deserialize(json_encode($request->query->all()), SignUp\Confirm\Command::class, 'json');
        try {
            $command = $this->serializer->deserialize(json_encode($request->query->all()), SignUp\Confirm\Command::class, 'json');
        } catch (ExceptionInterface $e) {
            return $this->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => Response::HTTP_BAD_REQUEST
                ]
            ], Response::HTTP_BAD_REQUEST);
        }


        $violations = $this->validator->validate($command);
        if (\count($violations)) {
            $json = $this->serializer->serialize($violations, 'json');
            return new JsonResponse($json, Response::HTTP_BAD_REQUEST, [], true);
        }

        $handler->handle($command);

        return new JsonResponse(status: Response::HTTP_OK);
    }

    public function user(): Response
    {
        return $this->json(['roles' => $this->getUser()->getRoles()], Response::HTTP_OK);
    }
}