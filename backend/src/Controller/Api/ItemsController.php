<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ItemRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Item;
use App\Service\ItemChanceValidator;
use App\Enum\ItemRarity;

final class ItemsController extends AbstractController
{
    #[Route('/api/admin/items', methods: ['GET'])]
    public function items(ItemRepository $repo): JsonResponse
    {

        $items = $repo->findAll();

        if (!$items) {
            return $this->json([]);
        }

        return $this->json(array_map(fn($item) => [
            'id' => $item->getId(),
            'name' => $item->getName(),
            'chance' => $item->getChance(),
            'rarity' => $item->getRarity(),
            'count' => $item->getCount(),
            'game_item_id' => $item->getGameItemId(),
            'icon' => $item->getIcon(),
        ], $items));
        
    }

    #[Route('/api/admin/add-item', methods: ['POST'])]
    public function addItem(Request $request, EntityManagerInterface $em, ItemChanceValidator $validator): JsonResponse
    {

        $name = $request->request->get('name');
        $chance = (float) $request->request->get('chance');
        $rarity = $request->request->get('rarity', ItemRarity::COMMON);
        $count = $request->request->get('count');
        $gameid = $request->request->get('game_item_id');

        try {
            $validator->assertValid($chance);
        } catch (\RuntimeException $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 400);
        }

        $file = $request->files->get('icon');

        if (!$file) {
            return $this->json(['error' => 'Brak pliku z ikoną'], 400);
        }

        if (!in_array($rarity, ItemRarity::ALL, true)) {
            return $this->json(['error' => 'Nieprawidłowa rzadkość'], 400);
        }

        $extension = $file->guessExtension() ?: 'png';

        $filename = uniqid() . '.' . $extension;

        $file->move(
            $this->getParameter('kernel.project_dir') . '/public/uploads/icons',
            $filename
        );

        $item = new Item();
        $item->setName($name);
        $item->setChance($chance);
        $item->setRarity($rarity);
        $item->setCount($count);
        $item->setGameItemId($gameid);
        $item->setIcon('/uploads/icons/' . $filename);

        $em->persist($item);
        $em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/api/admin/edit-item/{id}', methods: ['POST'])]
    public function editItem(int $id, Request $request, EntityManagerInterface $em, ItemChanceValidator $validator): JsonResponse 
    {
        $item = $em->getRepository(Item::class)->find($id);

        if (!$item) {
            return $this->json(['error' => 'Not found'], 404);
        }

        $name = $request->request->get('name');
        $chance = $request->request->get('chance');
        $rarity = $request->request->get('rarity');
        $count = $request->request->get('count');
        $gameid = $request->request->get('game_item_id');

        if ($name) {
            $item->setName($name);
        }

        if ($chance !== null) {
            $chance = (float) $chance;

            try {
                $validator->assertValid($chance, $item);
            } catch (\RuntimeException $e) {
                return $this->json([
                    'error' => $e->getMessage(),
                ], 400);
            }

            $item->setChance($chance);
        }

        if ($rarity !== null) {
            if (!in_array($rarity, ItemRarity::ALL, true)) {
                return $this->json(['error' => 'Invalid rarity'], 400);
            }
            $item->setRarity($rarity);
        }

        if ($count !== null) {
            $item->setCount($count);
        }

        if ($gameid !== null) {
            $item->setGameItemId($gameid);
        }

        $file = $request->files->get('icon');

        if ($file) {
            $extension = $file->guessExtension() ?: 'png';

            $filename = uniqid() . '.' . $extension;

            $file->move(
                $this->getParameter('kernel.project_dir') . '/public/uploads/icons',
                $filename
            );

            $item->setIcon('/uploads/icons/' . $filename);
        }

        $em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/api/admin/delete-item/{id}', methods: ['DELETE'])]
    public function deleteItem(int $id, EntityManagerInterface $em): JsonResponse
    {
        $item = $em->getRepository(Item::class)->find($id);

        if (!$item) {
            return $this->json(['error' => 'Not found'], 404);
        }

        $em->remove($item);
        $em->flush();

        return $this->json(['success' => true]);
    }
}
