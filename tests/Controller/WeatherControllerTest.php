<?php

namespace App\Tests\Controller;

use App\DTO\WeatherData;
use App\Exception\WeatherApiException;
use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class WeatherControllerTest extends WebTestCase
{
    /**
     *  Test that the weather page displays successfully for a valid city.
     *
     * @return void
     */
    public function testWeatherPageSuccess(): void
    {
        $client = static::createClient();

        $mockService = $this->createMock(WeatherService::class);
        $mockService
            ->method('getWeatherData')
            ->with('London')
            ->willReturn(new WeatherData(
                city: 'London',
                country: 'UK',
                temperature: 15.0,
                condition: 'Sunny',
                icon: '//cdn.weatherapi.com/weather/64x64/day/113.png',
                humidity: 50,
                windSpeed: 10.0,
                lastUpdated: '2025-04-11 12:00'
            ));

        static::getContainer()->set(WeatherService::class, $mockService);

        $client->request('GET', '/weather/London');

        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('h1', 'London');
    }

    /**
     *  Test that the weather page shows an error for a non-existent city.
     *
     * @return void
     */
    public function testWeatherPageCityNotFound(): void
    {
        $client = static::createClient();

        $mockService = $this->createMock(WeatherService::class);
        $mockService
            ->method('getWeatherData')
            ->with('UnknownCity')
            ->willThrowException(WeatherApiException::cityNotFound('UnknownCity'));

        static::getContainer()->set(WeatherService::class, $mockService);

        $client->request('GET', '/weather/UnknownCity');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $this->assertSelectorExists('.error-message');
    }
}
