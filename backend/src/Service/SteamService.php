<?php

namespace App\Service;

class SteamService
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = $_ENV['STEAM_API_KEY'];
    }

    public function getPlayer(string $steamId): array
    {
        $url = sprintf(
            'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/?key=%s&steamids=%s',
            $this->apiKey,
            $steamId
        );

        $response = file_get_contents($url);

        if (!$response) {
            return [];
        }

        $data = json_decode($response, true);

        return $data['response']['players'][0] ?? [];
    }
}