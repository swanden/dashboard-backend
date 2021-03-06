<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return new JsonResponse(['name' => 'API', 'version'=> '1.0']);
    }

    #[Route('/api', name: 'api')]
    public function api(): Response
    {
        return new JsonResponse(['name' => 'API', 'version'=> '1.0']);
    }

    #[Route('/api/profile', name: 'user_profile')]
    public function profile(): Response
    {
        return $this->json([
            'data' => true
        ]);
    }
}