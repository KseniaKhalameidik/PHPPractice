<?php

namespace App\Tests\Unit\Service;

use App\Model\Weather;
use App\Service\WeatherService;
use DateTime;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherServiceTest extends TestCase
{
    private CacheInterface|MockObject $cache;
    private HttpClientInterface|MockObject $client;
    private WeatherService $weatherService;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        // TODO: настроить моки и тестируемый сервис
        $this->cache = $this->createMock(CacheInterface::class);
        $this->client = $this->createMock(HttpClientInterface::class);
        $this->weatherService = new WeatherService($this->cache, $this->client);
    }

    #[DataProvider('provideWeathers')]
    public function testGetWeather(
        float $expectedLat,
        float $expectedLon,
        int $forecastDays,
        bool $includeWind,
        array $expectedWeathers,
    ): void {
        // TODO: Написать тест, что WeatherService вернет массив Weather
        // Проверить, что CacheInterface вызовется и вернется массив Weather
        $cacheKey = 'weather.' . $expectedLat . '.' . $expectedLon . '.' . $forecastDays . ($includeWind ? '.wind' : '');

        $this->cache->expects($this->once())
            ->method('get')
            ->with($cacheKey, $this->isCallable())
            ->willReturn($expectedWeathers);

        $this->client->expects($this->never())->method('request');

        $actualWeathers = $this->weatherService->getWeather($expectedLat, $expectedLon, $forecastDays, $includeWind);

        $this->assertEquals($expectedWeathers, $actualWeathers);
        foreach ($actualWeathers as $weather) {
            $this->assertInstanceOf(Weather::class, $weather);
        }
    }

    public static function provideWeathers(): array
    {
        return [
            'vladivostokWithoutWind' => [
                43.128292,
                131.98212,
                10,
                false,
                [
                    new Weather(new DateTime('2026-01-01'), 10.0, -10.0),
                    new Weather(new DateTime('2026-01-02'), 12.0, -8.0),
                ],
            ],
            'moscowWithWind' => [
                55.752,
                37.6178,
                5,
                true,
                [
                    new Weather(new DateTime('2026-05-26'), 14.4, 7.6, 16.9, 54, 301),
                ],
            ],
        ];
    }
}
