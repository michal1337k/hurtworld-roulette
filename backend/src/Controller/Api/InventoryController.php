<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\User;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

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

            $grouped[$itemId]['count'] += $inv->getCount();
        }

        return $this->json(array_values($grouped));
    }
    
    #[Route('/api/inventory/claim', name: 'api_inventory_claim', methods: ['POST'])]
    public function claim(Request $request, UserRepository $userRepository, ItemRepository $itemRepository, InventoryRepository $inventoryRepository, EntityManagerInterface $em): JsonResponse 
    {
        if (!$this->isValidGameToken($request)) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        $steamId = $data['steam_id'] ?? null;
        $gameItemId = isset($data['game_item_id']) ? (int) $data['game_item_id'] : null;
        $count = isset($data['count']) ? (int) $data['count'] : 0;

        if (!$steamId || !$gameItemId || $count <= 0) {
            return $this->json(['error' => 'Invalid request data'], 400);
        }

        $user = $userRepository->find($steamId);

        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $item = $itemRepository->findOneBy([
            'gameItemId' => $gameItemId,
        ]);

        if (!$item) {
            return $this->json(['error' => 'Item not found'], 404);
        }

        $inventories = $inventoryRepository->findBy(
            [
                'player' => $user,
                'item' => $item,
            ],
            [
                'createdAt' => 'ASC',
            ]
        );

        $availableCount = 0;

        foreach ($inventories as $inventory) {
            $availableCount += $inventory->getCount();
        }

        if ($availableCount < $count) {
            return $this->json(['error' => 'Not enough items'], 400);
        }

        $leftToRemove = $count;

        foreach ($inventories as $inventory) {
            if ($leftToRemove <= 0) {
                break;
            }

            $inventoryCount = $inventory->getCount();

            if ($inventoryCount <= $leftToRemove) {
                $leftToRemove -= $inventoryCount;
                $em->remove($inventory);
                continue;
            }

            $inventory->setCount($inventoryCount - $leftToRemove);
            $leftToRemove = 0;
        }

        $em->flush();

        return $this->json([
            'success' => true,
            'claimed' => $count,
            'game_item_id' => $gameItemId,
        ]);
    }

    private function isValidGameToken(Request $request): bool
    {
        $auth = $request->headers->get('Authorization');

        if (!$auth || !str_starts_with($auth, 'Bearer ')) {
            return false;
        }

        $token = substr($auth, 7);

        return hash_equals($_ENV['GAME_API_TOKEN'] ?? '', $token);
    }
}
