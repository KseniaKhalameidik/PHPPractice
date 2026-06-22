<?php

namespace App\Tests\Integration\Service;

use App\Model\Weather;
use App\Service\WeatherService;
use DateTime;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Cache\CacheItemInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CacheWeatherServiceTest extends KernelTestCase
{
    private ArrayAdapter $realCache;
    private HttpClientInterface|MockObject $api;
    private WeatherService $weatherService;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        // TODO: настроить моки и тестируемый сервис
        // использовать настоящий кеш ArrayAdapted
        self::bootKernel();
        $this->assertSame('test', self::$kernel->getEnvironment());

        $this->realCache = new ArrayAdapter();
        $this->api = $this->createMock(HttpClientInterface::class);
        $this->weatherService = new WeatherService($this->realCache, $this->api);
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->realCache->clear();
        parent::tearDown();
        // TODO: реализовать очистку кеша
    }

    #[DataProvider('provideWeathers')]
    public function testGetWeathersWithoutCache(
        float $expectedLat,
        float $expectedLon,
        int $forecastDays,
        bool $includeWind,
        array $expectedWeathers,
        string $rawJson,
    ): void {
        // TODO: Реализовать тест получения прогноза погоды без использования кеша
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')
                ->willReturn(200);
        $response->method('getContent')->willReturn($rawJson);

        $this->api->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $actualWeathers = $this->weatherService->getWeather($expectedLat, $expectedLon, $forecastDays, $includeWind);

        $this->assertEquals($expectedWeathers, $actualWeathers);
    }

    #[DataProvider('provideWeathers')]
    public function testGetWeatherWithCache(
        float $expectedLat,
        float $expectedLon,
        int $forecastDays,
        bool $includeWind,
        array $expectedWeathers,
        string $rawJson,
    ): void {
        // TODO: Реализовать тест получения прогноза погоды с использования кеша
        $cacheKey = 'weather.' . $expectedLat . '.' . $expectedLon . '.' . $forecastDays . ($includeWind ? '.wind' : '');

        $this->realCache->get($cacheKey, function (CacheItemInterface $item) use ($expectedWeathers, $forecastDays) {
            $item->expiresAfter(60);
            $item->set($expectedWeathers);

            return $expectedWeathers;
        });

        $this->api->expects($this->never())->method('request');

        $actualWeathers = $this->weatherService->getWeather($expectedLat, $expectedLon, $forecastDays, $includeWind);

        $this->assertEquals($expectedWeathers, $actualWeathers);
    }

    public function testClearCache(): void
    {
        $cacheKeys = [
            'weather.43.128292.131.98212.10',
            'weather.55.752.37.6178.5.wind',
        ];

        foreach ($cacheKeys as $cacheKey) {
            $this->realCache->get($cacheKey, function (CacheItemInterface $item) {
                $item->expiresAfter(60);

                return ['test' => 'test'];
            });

            $this->assertTrue($this->realCache->hasItem($cacheKey));
        }

        $this->weatherService->clearCache();

        foreach ($cacheKeys as $cacheKey) {
            $this->assertFalse($this->realCache->hasItem($cacheKey));
        }
    }

    #[DataProvider('provideInvalidApiResponses')]
    public function testGetWeatherClientReturnInvalidData(int $statusCode, string $rawJson): void
    {
        // TODO: Реализовать тест, в котором Api возвращает невалидные данные (пару 4**, 5** тест-кейсов в DataProvider)
        // Необходимо обрботать такой сценарий в коде серивса
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($statusCode);
        if ($statusCode < 400) {
            $response->method('getContent')
                    ->willReturn($rawJson);
        }

        $this->api->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $this->expectException(RuntimeException::class);

        $this->weatherService->getWeather(1.0, 1.0);
    }

    public static function provideInvalidApiResponses(): array
    {
        return [
            'http400' => [400, '{}'],
            'http404' => [404, '{}'],
            'http500' => [500, '{}'],
            'http503' => [503, '{}'],
            'invalidBody' => [200, '{"daily": {}}'],
        ];
    }

    public static function provideWeathers(): array
    {
        // TODO: Дополнить тест-кейсы
        $jsonWithoutWind = json_encode([
            'daily' => [
                'time' => ['2026-01-01'],
                'temperature_2m_max' => [10.0],
                'temperature_2m_min' => [-10.0],
            ],
        ], JSON_THROW_ON_ERROR);

        $jsonWithWind = json_encode([
            'daily' => [
                'time' => ['2026-03-01'],
                'temperature_2m_max' => [3.0],
                'temperature_2m_min' => [-3.0],
                'wind_speed_10m_max' => [7.0],
                'wind_gusts_10m_max' => [14.0],
                'wind_direction_10m_dominant' => [90],
            ],
        ], JSON_THROW_ON_ERROR);

        return [
            'coordinatesWithoutWind' => [
                1.0,
                1.0,
                10,
                false,
                [new Weather(new DateTime('2026-01-01'), 10.0, -10.0)],
                $jsonWithoutWind,
            ],
            'coordinatesWithWind' => [
                2.0,
                2.0,
                5,
                true,
                [new Weather(new DateTime('2026-03-01'), 3.0, -3.0, 7.0, 14.0, 90.0)],
                $jsonWithWind,
            ],
        ];
    }
}
