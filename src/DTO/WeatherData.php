<?php

namespace App\DTO;

readonly class WeatherData
{
    /**
     * @param string $city
     * @param string $country
     * @param float $temperature
     * @param string $condition
     * @param string $icon
     * @param int $humidity
     * @param float $windSpeed
     * @param string $lastUpdated
     */
    public function __construct(
        private string $city,
        private string $country,
        private float  $temperature,
        private string $condition,
        private string $icon,
        private int    $humidity,
        private float  $windSpeed,
        private string $lastUpdated
    ) {}

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @return float
     */
    public function getTemperature(): float
    {
        return $this->temperature;
    }

    /**
     * @return string
     */
    public function getCondition(): string
    {
        return $this->condition;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @return int
     */
    public function getHumidity(): int
    {
        return $this->humidity;
    }

    /**
     * @return float
     */
    public function getWindSpeed(): float
    {
        return $this->windSpeed;
    }

    /**
     * @return string
     */
    public function getLastUpdated(): string
    {
        return $this->lastUpdated;
    }
}