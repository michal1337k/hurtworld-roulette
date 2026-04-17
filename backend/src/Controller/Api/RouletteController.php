<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Item;
use App\Entity\Inventory;

final class RouletteController extends AbstractController
{
    #[Route('/api/roll', methods: ['POST'])]
    public function roll(EntityManagerInterface $em): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        if ($user->getBalance() < 1000) { 
            return $this->json(['error' => 'Brak środków na koncie!'], 400);
        }

        $items = $em->getRepository(Item::class)->findAll();

        $totalChance = array_sum(array_map(fn($i) => $i->getChance(), $items));
        $rand = mt_rand() / mt_getrandmax() * $totalChance;

        $current = 0;
        $wonItem = null;

        foreach ($items as $item) {
            $current += $item->getChance();

            if ($rand <= $current) {
                $wonItem = $item;
                break;
            }
        }

        if (!$wonItem) {
            return $this->json(['error' => 'Błąd losowania'], 500);
        }

        $user->setBalance($user->getBalance() - 1000);

        $inventory = new Inventory();
        $inventory->setPlayer($user);
        $inventory->setItem($wonItem);

        $em->persist($inventory);
        $em->flush();

        return $this->json([
            'id' => $wonItem->getId(),
            'name' => $wonItem->getName(),
            'icon' => $wonItem->getIcon(),
            'rarity' => $wonItem->getRarity()
        ]);
    }
}
