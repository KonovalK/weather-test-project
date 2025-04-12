<?php

namespace App\Exception;

use Throwable;

class WeatherApiException extends \Exception
{
    private array $context;

    public function __construct(
        string $message = "",
        array $context = [],
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @param string $details
     * @param Throwable|null $previous
     * @return self
     */
    public static function connectionError(string $details, ?Throwable $previous = null): self
    {
        return new self(
            "Failed to connect to weather API: $details",
            ['error_type' => 'connection_error'],
            0,
            $previous
        );
    }

    /**
     * @param string $details
     * @param array $response
     * @return self
     */
    public static function invalidResponse(string $details, array $response = []): self
    {
        return new self(
            "Invalid API response: $details",
            [
                'error_type' => 'invalid_response',
                'response_data' => $response
            ]
        );
    }

    /**
     * @param string $city
     * @return self
     */
    public static function cityNotFound(string $city): self
    {
        return new self(
            "City not found: $city",
            [
                'error_type' => 'city_not_found',
                'city' => $city
            ]
        );
    }

    /**
     * @return self
     */
    public static function invalidApiKey(): self
    {
        return new self(
            "Invalid API key or API key not provided",
            ['error_type' => 'invalid_api_key']
        );
    }
}