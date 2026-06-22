<?php

namespace App\Service;

use App\Model\Weather;
use DateTime;
use Psr\Cache\CacheItemInterface;
use RuntimeException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    private const OPEN_METEO_URL = 'https://api.open-meteo.com/v1/forecast';
    private const CACHE_KEY_PREFIX = 'weather.';

    public function __construct(private CacheInterface $cache, private HttpClientInterface $client)
    {
    }

    // @return array<Weather>
    // Возвращает массив объектов Weather по определенной широте ($lat) и долготе ($lon)
    // Содержание объектов определяется по количеству $forecastDays
    public function getWeather(float $lat, float $lon, int $forecastDays = 10, bool $includeWind = false): array
    {
        $cacheKey = $this->buildCacheKey($lat, $lon, $forecastDays, $includeWind);

        return $this->cache->get($cacheKey, function (CacheItemInterface $item) use ($lat, $lon, $forecastDays, $includeWind) {
            $item->expiresAfter(60);

            return $this->fetchWeather($lat, $lon, $forecastDays, $includeWind);
        });
    }

    public function clearCache(): void
    {
        $this->cache->delete($this->buildCacheKey(43.128292, 131.98212, 10, false));
        $this->cache->delete($this->buildCacheKey(55.752, 37.6178, 5, true));
    }

    private function buildCacheKey(float $lat, float $lon, int $forecastDays, bool $includeWind): string
    {
        $windSuffix = $includeWind ? '.wind' : '';

        return self::CACHE_KEY_PREFIX . $lat . '.' . $lon . '.' . $forecastDays . $windSuffix;
    }

    /**
     * @return array<Weather>
     */
    private function fetchWeather(float $lat, float $lon, int $forecastDays, bool $includeWind): array
    {
        $dailyParams = ['temperature_2m_max', 'temperature_2m_min'];
        if ($includeWind) {
            $dailyParams = array_merge($dailyParams, [
                'wind_speed_10m_max',
                'wind_gusts_10m_max',
                'wind_direction_10m_dominant',
            ]);
        }

        $response = $this->client->request('GET', self::OPEN_METEO_URL, [
            'query' => [
                'latitude' => $lat,
                'longitude' => $lon,
                'daily' => implode(',', $dailyParams),
                'forecast_days' => $forecastDays,
            ],
        ]);

        $statusCode = $response->getStatusCode();
        if ($statusCode >= 400) {
            throw new RuntimeException(sprintf('Weather API returned HTTP %d', $statusCode));
        }

        $data = json_decode($response->getContent(), true);
        if (!is_array($data)) {
            throw new RuntimeException('Weather API returned invalid data');
        }

        return $this->buildWeathersFromApiResponse($data, $includeWind);
    }

    /**
     * @return array<Weather>
     */
    private function buildWeathersFromApiResponse(array $data, bool $includeWind): array
    {
        $daily = $data['daily'] ?? [];

        if (!isset($daily['time'], $daily['temperature_2m_max'], $daily['temperature_2m_min'])) {
            throw new RuntimeException('Weather API returned invalid data');
        }

        if ($includeWind && !isset(
            $daily['wind_speed_10m_max'],
            $daily['wind_gusts_10m_max'],
            $daily['wind_direction_10m_dominant'],
        )) {
            throw new RuntimeException('Weather API returned invalid wind data');
        }

        $weathers = [];
        foreach ($daily['time'] as $dayIndex => $date) {
            $weathers[] = new Weather(
                new DateTime($date),
                (float) $daily['temperature_2m_max'][$dayIndex],
                (float) $daily['temperature_2m_min'][$dayIndex],
                $includeWind ? (float) $daily['wind_speed_10m_max'][$dayIndex] : null,
                $includeWind ? (float) $daily['wind_gusts_10m_max'][$dayIndex] : null,
                $includeWind ? (float) $daily['wind_direction_10m_dominant'][$dayIndex] : null,
            );
        }

        // TODO: Реализовать запрос к open-meteo.com по $lat, $lon и $forecastDays
        // Достаточно вернуть дату прогноза с максимальной и минимальной температурой 
        // Обработайте ответ и верните массив объектов Weather
        // Ссылка на документацию: https://open-meteo.com/en/docs
        //return [new Weather(new DateTime('2026-05-01 00:00:00'), 15.0, 5.5)];

        return $weathers;
    }
}
