<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class InventoryController extends AbstractController
{
    #[Route('/api/inventory', name: 'api_inventory', methods: ['GET'])]
    public function inventory(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

         /** @var \App\Entity\User $user */
        $inventory = $user->getInventories();

        $grouped = [];

        foreach ($inventory as $inv) {
            $item = $inv->getItem();
            $itemId = $item->getId();

            if (!isset($grouped[$itemId])) {
                $grouped[$itemId] = [
                    'id' => $itemId,
                    'name' => $item->getName(),
                    'icon' => $item->getIcon(),
                    'rarity' => $item->getRarity(),
                    'game_item_id' => $item->getGameItemId(),
                    'count' => 0
                ];
            }

            $grouped[$itemId]['count'] += $item->getCount();
        }

        return $this->json(array_values($grouped));
    }
}
