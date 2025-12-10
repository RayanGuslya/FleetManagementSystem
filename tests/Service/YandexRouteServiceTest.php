<?php

namespace tests\Service;

use App\Service\YandexRouteService;
use PHPUnit\Framework\TestCase;

class YandexRouteServiceTest extends TestCase
{
    public function testCalculateDistanceReturnsCorrectValue()
    {
        $fakeJson = json_encode([
            'routes' => [
                ['distance' => 12345]
            ]
        ]);

        $service = new class($fakeJson) extends YandexRouteService {
            private string $fakeResponse;

            public function __construct($fakeResponse)
            {
                parent::__construct('fake');
                $this->fakeResponse = $fakeResponse;
            }

            protected function fetch(string $url, array $opts): ?string
            {
                return $this->fakeResponse;
            }

            protected function getApiResponse(string $url, array $opts): ?string
            {
                return $this->fakeResponse;
            }
        };

        $result = $service->calculateDistance(50.45, 30.52, 51.50, 31.30);

        $this->assertEquals(12345, $result);
    }

    public function testCalculateDistanceReturnsNullOnInvalidResponse()
    {
        $service = new class() extends YandexRouteService {
            public function __construct()
            {
                parent::__construct('fake');
            }

            protected function getApiResponse(string $url, array $opts): ?string
            {
                return null;
            }
        };

        $result = $service->calculateDistance(50.45, 30.52, 51.50, 31.30);

        $this->assertNull($result);
    }
}
