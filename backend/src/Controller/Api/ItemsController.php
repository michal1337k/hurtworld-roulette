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
            'icon' => $item->getIcon(),
        ], $items));
        
    }

    #[Route('/api/admin/add-item', methods: ['POST'])]
    public function addItem(Request $request, EntityManagerInterface $em, ItemChanceValidator $validator): JsonResponse
    {

        $name = $request->request->get('name');
        $chance = $request->request->get('chance');
        $rarity = $request->request->get('rarity', ItemRarity::COMMON);
        $count = $request->request->get('count');

        $validator->assertValid($chance);

        $file = $request->files->get('icon');

        if (!$file) {
            return $this->json(['error' => 'No file'], 400);
        }

        if (!in_array($rarity, ItemRarity::ALL, true)) {
            return $this->json(['error' => 'Invalid rarity'], 400);
        }

        $extension = $file->guessExtension() ?: 'png';

        $filename = uniqid() . '.' . $extension;

        $file->move(
            $this->getParameter('kernel.project_dir') . '/public/uploads/icons',
            $filename
        );

        $item = new Item();
        $item->setName($name);
        $item->setChance((float)$chance);
        $item->setRarity($rarity);
        $item->setCount($count);
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

        if ($name) {
            $item->setName($name);
        }

        if ($chance !== null) {
            $validator->assertValid((float)$chance, $item);
            $item->setChance((float)$chance);
        }

        if ($rarity !== null) {
            if (!in_array($rarity, ItemRarity::ALL, true)) {
                return $this->json(['error' => 'Invalid rarity'], 400);
            }
            $item->setRarity($rarity);
        }

        if ($count) {
            $item->setCount($count);
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
