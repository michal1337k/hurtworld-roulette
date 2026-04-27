<?php

namespace App\Service;

use App\Repository\ItemRepository;
use App\Entity\Item;

class ItemChanceValidator
{
    public function __construct(
        private ItemRepository $itemRepository
    ) {}

    public function assertValid(float $newChance, ?Item $ignoreItem = null): void
    {
        $items = $this->itemRepository->findAll();

        $sum = 0.0;

        foreach ($items as $item) {
            if ($ignoreItem && $item->getId() === $ignoreItem->getId()) {
                continue;
            }

            $sum += (float) $item->getChance();
        }

        $newTotal = $sum + $newChance;

        if ($newTotal > 100) {
            throw new \RuntimeException(sprintf(
                'Nie można zapisać itemu, ponieważ łączna ilość szans na drop wynosiłaby %.2f%%, a maksymalnie może wynosić 100%%.',
                $newTotal
            ));
        }
    }
}