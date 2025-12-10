<?php

namespace App\Service;

class YandexRouteService
{
    private string $apiKey;

    public function __construct(string $apiKey = 'demo')
    {
        $this->apiKey = $apiKey;
    }

    public function calculateDistance(
        float $startLat,
        float $startLon,
        float $endLat,
        float $endLon
    ): ?int {
        $url = "https://api.routing.yandex.net/v2/route?apikey={$this->apiKey}"
             . "&waypoints={$startLat},{$startLon}|{$endLat},{$endLon}";

        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "Content-Type: application/json"
            ]
        ];

        $json = $this->getApiResponse($url, $opts);

        if (!$json) {
            return null;
        }

        $data = json_decode($json, true);

        if (!isset($data["routes"][0]["distance"])) {
            return null;
        }

        return (int) $data["routes"][0]["distance"];
    }

    protected function getApiResponse(string $url, array $opts): ?string
    {
        return $this->fetch($url, $opts);
    }

    protected function fetch(string $url, array $opts): ?string
    {
        return @file_get_contents($url, false, stream_context_create($opts));
    }
}
