<?php

namespace App\Service;

use App\DTO\WeatherData;
use App\Exception\WeatherApiException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

// Service responsible for fetching weather data from an external Weather API
class WeatherService
{
    // API error codes for specific known error scenarios
    private const ERROR_INVALID_API_KEY = 2008;
    private const ERROR_CITY_NOT_FOUND = 1006;
    private const ERROR_RATE_LIMIT_EXCEEDED = 2007;

    /**
     * WeatherService constructor.
     *
     * @param HttpClientInterface $httpClient  Symfony HTTP client for making API requests
     * @param LoggerInterface     $logger      Logger for recording info and error messages
     * @param string              $apiKey      API key for authenticating with the weather service
     * @param string              $apiUrl      Base URL of the weather API
     * @param string              $apiLang
     */
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly string $apiKey,
        private readonly string $apiUrl,
        private readonly string $apiLang,
    ) {}

    /**
     * Fetches weather data for a given city from the weather API.
     *
     * @param string $city The name of the city to fetch weather data for
     * @return WeatherData Returns a DTO containing the weather data
     * @throws WeatherApiException If an API error or HTTP request error occurs
     */
    public function getWeatherData(string $city): WeatherData
    {
        try {
            if (empty($this->apiKey)) {
                throw WeatherApiException::invalidApiKey();
            }

            $response = $this->httpClient->request(
                'GET',
                $this->apiUrl,
                [
                    'query' => [
                        'key' => $this->apiKey,
                        'q' => $city,
                        'lang' => $this->apiLang,
                    ]
                ]
            );

            $weatherApiResponse = $response->toArray(false);

            if (isset($weatherApiResponse['error'])) {
                $errorCode = $weatherApiResponse['error']['code'] ?? 0;
                return $this->handleApiError($errorCode, $city, $weatherApiResponse);
            }

            $weatherData = new WeatherData(
                city: $weatherApiResponse['location']['name'],
                country: $weatherApiResponse['location']['country'],
                temperature: (float) $weatherApiResponse['current']['temp_c'],
                condition: $weatherApiResponse['current']['condition']['text'],
                icon: $weatherApiResponse['current']['condition']['icon'],
                humidity: (int) $weatherApiResponse['current']['humidity'],
                windSpeed: (float) $weatherApiResponse['current']['wind_kph'],
                lastUpdated: $weatherApiResponse['current']['last_updated']
            );

            $this->logger->info('Weather data retrieved successfully', [
                'city' => $weatherData->getCity(),
                'temperature' => $weatherData->getTemperature()
            ]);

            return $weatherData;

        } catch (ExceptionInterface $e) {
            $this->logger->error('Error retrieving weather data', [
                'city' => $city,
                'error' => $e->getMessage()
            ]);

            throw WeatherApiException::connectionError($e->getMessage(), $e);
        }
    }

    /**
     * Handles specific API error codes and throws corresponding domain exceptions.
     *
     * @param int    $errorCode      Error code returned by the API
     * @param string $city           City name used in the request
     * @param array  $errorResponse  Full error response from the API
     * @return mixed Never returns; always throws an exception
     * @throws WeatherApiException
     */
    private function handleApiError(int $errorCode, string $city, array $errorResponse): mixed
    {
        return match ($errorCode) {
            self::ERROR_INVALID_API_KEY, self::ERROR_RATE_LIMIT_EXCEEDED => throw WeatherApiException::invalidApiKey(),
            self::ERROR_CITY_NOT_FOUND => throw WeatherApiException::cityNotFound($city),
            default => throw WeatherApiException::invalidResponse(
                $errorResponse['error']['message'],
                $errorResponse
            )
        };
    }
}
