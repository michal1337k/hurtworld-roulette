<?php

namespace App\Enum;

final class ItemRarity
{
    public const COMMON = 'common';
    public const UNCOMMON = 'uncommon';
    public const RARE = 'rare';
    public const EPIC = 'epic';
    public const LEGENDARY = 'legendary';

    public const ALL = [
        self::COMMON,
        self::UNCOMMON,
        self::RARE,
        self::EPIC,
        self::LEGENDARY,
    ];
}