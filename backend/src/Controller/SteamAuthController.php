<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\SteamService;
use App\Security\SteamAuthenticator;
use App\Entity\User;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

final class SteamAuthController extends AbstractController
{
    #[Route('/login/steam', name: 'steam_login')]
    public function login(): Response
    {
        $params = [
            'openid.ns' => 'http://specs.openid.net/auth/2.0',
            'openid.mode' => 'checkid_setup',
            'openid.return_to' => 'http://localhost:8080/login/steam/check',
            'openid.realm' => 'http://localhost:8080/',
            'openid.identity' => 'http://specs.openid.net/auth/2.0/identifier_select',
            'openid.claimed_id' => 'http://specs.openid.net/auth/2.0/identifier_select',
        ];

        $url = 'https://steamcommunity.com/openid/login?' . http_build_query($params);

        return $this->redirect($url);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Handled by firewall');
    }

    #[Route('/login/steam/check', name: 'steam_check')]
    public function check(Request $request, EntityManagerInterface $em, SteamService $steamService, UserAuthenticatorInterface $userAuthenticator, SteamAuthenticator $authenticator): Response
    {
        $claimedId = $request->query->get('openid_claimed_id');

        if (!$claimedId) {
            throw new \Exception('Brak Steam ID');
        }

        preg_match('#/id/([0-9]+)$#', $claimedId, $matches);
        $steamId = $matches[1] ?? null;

        if (!$steamId) {
            throw new \Exception('Nie można znaleźć Steam ID');
        }

        $steamData = $steamService->getPlayer($steamId);
        $user = $em->getRepository(User::class)->find($steamId);

        if (!$user) {
            $user = new User();
            $user->setSteamId($steamId);
            $user->setRoles(['ROLE_USER']);
            $user->setBalance(0);
        }

        $user->setNickname($steamData['personaname'] ?? 'Unknown');
        $user->setAvatar($steamData['avatarfull'] ?? null);

        $em->persist($user);
        $em->flush();


        return $userAuthenticator->authenticateUser(
            $user,
            $authenticator,
            $request
        );
    }
}
