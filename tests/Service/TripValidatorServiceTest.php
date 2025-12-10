<?php

namespace tests\Service;

use App\Service\TripValidatorService;
use App\Repository\TripRepository;
use PHPUnit\Framework\TestCase;

class TripValidatorServiceTest extends TestCase
{
    public function testDuplicateDetected()
    {
        $repo = $this->createMock(TripRepository::class);

        $repo->method('findOneBy')->willReturn(new \stdClass());

        $service = new TripValidatorService($repo);

        $result = $service->isDuplicate(new \DateTime('2024-02-01'), 5);

        $this->assertTrue($result, 'Ожидалось, что дубль будет обнаружен');
    }

    public function testNotDuplicate()
    {
        $repo = $this->createMock(TripRepository::class);

        $repo->method('findOneBy')->willReturn(null);

        $service = new TripValidatorService($repo);

        $result = $service->isDuplicate(new \DateTime('2024-02-01'), 5);

        $this->assertFalse($result, 'Ожидалось, что дубля нет');
    }
}
