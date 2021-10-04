<?php

namespace App\Controller\User;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class UserController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    #[Route('/user/role', name: 'auth.user.role', methods: ['GET'])]
    public function user(): Response
    {
        return $this->json($this->getUser()->getRoles(), Response::HTTP_OK);
    }
}