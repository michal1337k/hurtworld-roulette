<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ItemRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Item;

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
            'icon' => $item->getIcon(),
        ], $items));
        
    }

    #[Route('/api/admin/add-item', methods: ['POST'])]
    public function addItem(Request $request, EntityManagerInterface $em): JsonResponse
    {

        $name = $request->request->get('name');
        $chance = $request->request->get('chance');

        $file = $request->files->get('icon');

        if (!$file) {
            return $this->json(['error' => 'No file'], 400);
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
        $item->setIcon('/uploads/icons/' . $filename);

        $em->persist($item);
        $em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/api/admin/item/{id}', methods: ['PUT'])]
    public function update(Item $item, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $item->setName($request->request->get('name'));
        $item->setChance((float)$request->request->get('chance'));

        $file = $request->files->get('icon');

        if ($file) {
            $filename = uniqid().'.'.$file->guessExtension();

            $file->move(
                $this->getParameter('kernel.project_dir').'/public/uploads/icons',
                $filename
            );

            $item->setIcon('/uploads/icons/'.$filename);
        }

        $em->flush();
        
        return $this->json(['success' => true]);
    }
}
