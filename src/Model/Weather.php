<?php

namespace App\Model;

use DateTime;

// Класс-DTO для погоды, хранит максимальную и минимальную температуру и время, в котором температура была зафиксирована
readonly class Weather
{
    // TODO: Дополнить информацией о ветре (скорость, порывы, направление)
    public function __construct(
        public DateTime $dateTime,
        public float $maxTemp,
        public float $minTemp,
        public ?float $windSpeed = null,
        public ?float $windGust = null,
        public ?float $windDirection = null,
    ) {
    }
}
