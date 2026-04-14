<?php

namespace App\Controller;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

class FrontRedirectController
{
    #[Route('/', name: 'home')]
    public function home(
        #[Autowire('%env(FRONTEND_URL)%')] string $frontendUrl
    ): RedirectResponse {
        return new RedirectResponse($frontendUrl);
    }
}