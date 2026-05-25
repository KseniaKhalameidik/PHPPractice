<?php

namespace App\Unit\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Service\UniversityService;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;

class UniversityServiceTest extends TestCase
{
    public function testSomething(): void
    {
        $api = $this->createMock(HttpClientInterface::class);
        $api->expects($this->once())
            ->method('withOptions')
            ->with(['base_uri' => 'http://universities.hipolabs.com/']);

        $cache = $this->createMock(CacheInterface::class);

        $cacheKey = 'universities.Far';

        $rawJson = '[{"domains": ["dvgups.ru"], "state-province": null, "country": "Russian Federation", "name": "Far East State Transport University", "web_pages": ["http://www.dvgups.ru/"], "alpha_two_code": "RU"}, {"domains": ["khspu.ru"], "state-province": null, "country": "Russian Federation", "name": "Far Easten State University of Humanities", "web_pages": ["http://www.khspu.ru/"], "alpha_two_code": "RU"}, {"domains": ["dvgu.ru"], "state-province": null, "country": "Russian Federation", "name": "Far Eastern State University", "web_pages": ["http://www.dvgu.ru/"], "alpha_two_code": "RU"}, {"domains": ["festu.ru"], "state-province": null, "country": "Russian Federation", "name": "Far Eastern State Technical University", "web_pages": ["http://www.festu.ru/"], "alpha_two_code": "RU"}]';

        $cache->expects($this->once())
            ->method('get')
            ->with($cacheKey)
            ->willReturn($rawJson);


        $universityService = new UniversityService($api, $cache);
        $universityService->getUniversitiesByName('Far');
        $this->assertTrue(true);
    }
}
