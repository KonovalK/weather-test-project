<?php

namespace App\Controller;

use App\Exception\WeatherApiException;
use App\Service\WeatherService;
use App\Service\WeatherApiStatusCodeResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WeatherController extends AbstractController
{
    /**
     * WeatherController constructor.
     *
     * @param WeatherService $weatherService Service for fetching weather data
     * @param WeatherApiStatusCodeResolver $statusCodeResolver Resolver for converting domain exceptions to HTTP status codes
     */
    public function __construct(
        private readonly WeatherService $weatherService,
        private readonly WeatherApiStatusCodeResolver $statusCodeResolver
    ){}

    /**
     * Fetches weather information for a given city and renders it.
     *
     * @param string $city City name to fetch weather for
     * @return Response HTML response with either weather data or an error message
     */
    #[Route('/weather/{city}', name: 'weather_info', methods: ['GET'])]
    public function getWeather(string $city): Response
    {
        try {
            $weatherData = $this->weatherService->getWeatherData($city);

            return $this->render('weather/index.html.twig', [
                'weather' => $weatherData,
            ]);
        } catch (WeatherApiException $e) {
            $statusCode = $this->statusCodeResolver->resolve($e);

            return $this->render('weather/error.html.twig', [
                'error' => $e->getMessage(),
                'context' => $e->getContext(),
                'city' => $city
            ], new Response('', $statusCode));
        }
    }
}
