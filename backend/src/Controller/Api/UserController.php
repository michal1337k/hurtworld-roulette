<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'logged' => false
            ]);
        }

        return $this->json([
            'logged' => true,
            'steamId' => $user->getSteamId(),
            'nickname' => $user->getNickname(),
            'avatar' => $user->getAvatar(),
            'balance' => $user->getBalance(),
            'roles' => $user->getRoles(),
        ]);
    }
}