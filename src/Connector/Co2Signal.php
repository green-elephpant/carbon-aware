<?php

namespace GreenElePHPant\CarbonAware\Connector;

use Buzz\Client\Curl;
use GreenElePHPant\CarbonAware\Emissions\Emissions;
use GreenElePHPant\CarbonAware\Location\Location;
use Http\Client\HttpClient;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;

class Co2Signal implements ConnectorInterface
{
    // Capability
    // * Hourly
    // * Minute
    // Caching
    public function __construct(
        private readonly string $apiKey,
        private readonly HttpClient $httpClient,
        private readonly RequestFactoryInterface $requestFactory
    )
    {
    }

    public function getEmissions(Location $region): Emissions
    {
        $url = 'https://api.co2signal.com/v1/latest?countryCode=' . $region->getCountryCode();

        $request = $this->requestFactory->createRequest('GET', $url);
        $request = $request->withHeader('auth-token', $this->apiKey);
        $response = $this->httpClient->sendRequest($request);

        $result = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);

        return new Emissions($result->data->carbonIntensity, $region);
    }
}
