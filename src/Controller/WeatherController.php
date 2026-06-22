<?php

namespace App\Controller;

use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WeatherController extends AbstractController
{
    public function __construct(private WeatherService $weatherService)
    {
    }

    // Возвращает прогноз погоды (без информации о ветре) для Владивостока
    #[Route('/weather/vladivostok', name: 'app_weather_vladivostok')]
    public function vladovostokWeather(): Response
    {
        return $this->render('weather/index.html.twig', [
            'weathers' => $this->weatherService->getWeather(43.128292, 131.98212),
        ]);
    }

    // TODO: Реализовать метод и тесты получения прогноза погоды для Москвы за 5 дней
    // Для Москвы нужно отобразить информацию по ветру (скорость, порыв, направление)
    #[Route('/weather/moscow', name: 'app_weather_moscow')]
    public function moscowWeather(): Response
    {
        return $this->render('weather/moscow.html.twig', [
            'weathers' => $this->weatherService->getWeather(55.752, 37.6178, 5, true),
        ]);
    }

    // TODO: Релизовать метод очистки кеша прогноза погоды
    #[Route('/weather/clear', name: 'app_weather_clear')]
    public function clearWeatherCache(): Response
    {
        $this->weatherService->clearCache();

        return new Response('Cache cleared');
    }
}
