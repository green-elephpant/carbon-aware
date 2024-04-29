<?php

namespace GreenElephpant\CarbonAware\DataProvider\ElectricityMaps\Connector;

use GreenElephpant\CarbonAware\Location\Location;
use GreenElephpant\CarbonAware\DataProvider\Exception\ApiErrorResponseException;
use GreenElephpant\CarbonAware\DataProvider\Exception\InvalidApiResponseException;
use GreenElephpant\CarbonAware\DataProvider\Exception\UnexpectedApiResponseException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

/**
 * https://static.electricitymaps.com/api/docs/index.html
 */
class ElectricityMapsConnector
{
    public const API_URL = 'https://api.electricitymap.org/v3';
    public const CARBON_INTENSITY_LATEST = 'carbon-intensity/latest';

    private string $apiKey;

    private ClientInterface $httpClient;

    private RequestFactoryInterface $requestFactory;

    public function __construct(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        string $apiKey
    ) {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->apiKey = $apiKey;
    }

    public function getCarbonIntensityLatest(Location $location): object
    {
        $url = self::API_URL . '/' . self::CARBON_INTENSITY_LATEST . '?zone=' . strtolower($location->getCountryCode());

        $request = $this->requestFactory->createRequest('GET', $url);
        // TODO Token doesn't seem to be needed for CarbonIntensityLatest?
        // $request = $request->withHeader('auth-token', $this->apiKey);
        $response = $this->httpClient->sendRequest($request);

        try {
            $result = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR | 0);
        } catch (\Exception $e) {
            throw new InvalidApiResponseException();
        }

        if (isset($result->status) && $result->status === "error") {
            throw new ApiErrorResponseException(sprintf('API error: %s', $result->message ?? 'unknown'));
        }

        if (!isset($result->carbonIntensity, $result->datetime)) {
            throw new UnexpectedApiResponseException();
        }

        return $result;
    }
}
