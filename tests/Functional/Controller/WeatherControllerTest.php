<?php

namespace App\Tests\Functional\Controller;

use Override;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherControllerTest extends WebTestCase
{
    #[Override]
    protected function setUp(): void
    {
        // TODO: Установить ArrayCache адаптер с помощью установки соответсвующей реализации
        // CacheInterface в Service Container
        parent::setUp();
        // ArrayAdapter настроен в config/packages/cache.yaml
        static::ensureKernelShutdown();
    }

    public function testGetWeatherForVladovostok(): void
    {
        $client = static::createClient();
        $this->mockHttpClient($this->buildApiJson(10, false));
        static::getContainer()->get(CacheInterface::class)->clear();

        $client->request('GET', '/weather/vladivostok');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1.vdk-forecast', 'Владивосток');
        $this->assertSelectorTextContains('body', 'максимальная температура');
        $this->assertSelectorTextContains('body', '2026-05-01');
    }

    public function testGetWeatherFormMoscow(): void
    {
        // TODO: Реализовать функциональный тест, проверяющий, что в ответе хранится прогноз погоды для Москвы
        $client = static::createClient();
        $this->mockHttpClient($this->buildApiJson(5, true));
        static::getContainer()->get(CacheInterface::class)->clear();

        $client->request('GET', '/weather/moscow');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1.msk-forecast', 'Москве');
        $this->assertSelectorTextContains('body', 'скорость ветра');
        $this->assertSelectorTextContains('body', 'направление ветра');
        $this->assertSelectorTextContains('body', '2026-06-01');
    }

    private function mockHttpClient(string $json): void
    {
        static::getContainer()->set(
            HttpClientInterface::class,
            new MockHttpClient([new MockResponse($json, ['http_code' => 200])]),
        );
    }

    private function buildApiJson(int $days, bool $withWind): string
    {
        $times = [];
        $maxTemps = [];
        $minTemps = [];
        $windSpeeds = [];
        $windGusts = [];
        $windDirections = [];

        for ($i = 0; $i < $days; ++$i) {
            $month = $withWind ? '06' : '05';
            $times[] = sprintf('2026-%s-%02d', $month, $i + 1);
            $maxTemps[] = 10.0 + $i;
            $minTemps[] = -10.0 + $i;
            $windSpeeds[] = 5.0 + $i;
            $windGusts[] = 8.0 + $i;
            $windDirections[] = 90 + $i;
        }

        $daily = [
            'time' => $times,
            'temperature_2m_max' => $maxTemps,
            'temperature_2m_min' => $minTemps,
        ];

        if ($withWind) {
            $daily['wind_speed_10m_max'] = $windSpeeds;
            $daily['wind_gusts_10m_max'] = $windGusts;
            $daily['wind_direction_10m_dominant'] = $windDirections;
        }

        return json_encode(['daily' => $daily], JSON_THROW_ON_ERROR);
    }
}
