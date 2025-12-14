<?php

namespace App\Service;

class YandexRouteService
{
    private string $apiKey;

    public function __construct(string $apiKey = 'demo')
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @param float $startLat Широта начальной точки
     * @param float $startLon Долгота начальной точки
     * @param float $endLat   Широта конечной точки
     * @param float $endLon   Долгота конечной точки
     * @return int|null Расстояние в метрах или null при ошибке
     */
    public function calculateDistance(
        float $startLat,
        float $startLon,
        float $endLat,
        float $endLon
    ): ?int {
        $waypoints = "{$startLon},{$startLat}|{$endLon},{$endLat}";

        $url = 'https://api.routing.yandex.net/v2/route'
            . '?apikey=' . urlencode($this->apiKey)
            . '&waypoints=' . urlencode($waypoints)
            . '&mode=driving'
            . '&results=1';

        /**
         * @var array{
         *     http: array{
         *         method: string,
         *         header: string
         *     }
         * } $opts
         */
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => 'Content-Type: application/json'
            ]
        ];

        $json = $this->getApiResponse($url, $opts);

        if (!$json) {
            return null;
        }

        $data = json_decode($json, true);

        return isset($data['routes'][0]['distance'])
            ? (int) $data['routes'][0]['distance']
            : null;
    }

    /**
     * @param string $url
     * @param array{
     *     http: array{
     *         method: string,
     *         header: string
     *     }
     * } $opts
     * @return string|null
     */
    protected function getApiResponse(string $url, array $opts): ?string
    {
        return $this->fetch($url, $opts);
    }

    /**
     * @param string $url
     * @param array{
     *     http: array{
     *         method: string,
     *         header: string
     *     }
     * } $opts
     * @return string|null
     */
    protected function fetch(string $url, array $opts): ?string
    {
        $response = @file_get_contents($url, false, stream_context_create($opts));

        return $response !== false ? $response : null;
    }
}
