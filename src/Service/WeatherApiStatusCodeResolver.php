<?php

namespace App\Service;

use App\Exception\WeatherApiException;
use Symfony\Component\HttpFoundation\Response;

class WeatherApiStatusCodeResolver
{
    /**
     * Determines the HTTP status code based on the API error type.
     *
     * @param WeatherApiException $e
     * @return int
     */
    public function resolve(WeatherApiException $e): int
    {
        return match ($e->getContext()['error_type'] ?? '') {
            'city_not_found' => Response::HTTP_NOT_FOUND,
            'invalid_api_key' => Response::HTTP_UNAUTHORIZED,
            'rate_limit_exceeded' => Response::HTTP_TOO_MANY_REQUESTS,
            'connection_error' => Response::HTTP_SERVICE_UNAVAILABLE,
            default => Response::HTTP_INTERNAL_SERVER_ERROR,
        };
    }
}
