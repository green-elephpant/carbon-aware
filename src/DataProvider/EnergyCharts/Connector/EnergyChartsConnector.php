<?php

namespace GreenElephpant\CarbonAware\DataProvider\EnergyCharts\Connector;

use GreenElephpant\CarbonAware\Location\Location;
use GreenElephpant\CarbonAware\DataProvider\Exception\ApiErrorResponseException;
use GreenElephpant\CarbonAware\DataProvider\Exception\InvalidApiResponseException;
use GreenElephpant\CarbonAware\DataProvider\Exception\UnexpectedApiResponseException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

/**
 * https://api.energy-charts.info/
 */
class EnergyChartsConnector
{
    public const API_URL = 'https://api.energy-charts.info';
    public const SIGNAL_ENDPOINT = 'signal';

    /**
     * @readonly
     * @var \Psr\Http\Client\ClientInterface;
     */
    private ClientInterface $httpClient;

    /**
     * @readonly
     * @var \Psr\Http\Message\RequestFactoryInterface
     */
    private RequestFactoryInterface $requestFactory;

    public function __construct(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory
    ) {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
    }

    public function getSignal(Location $location): object
    {
        $url = self::API_URL . '/' . self::SIGNAL_ENDPOINT . '?country=' . strtolower($location->getCountryCode());

        $request = $this->requestFactory->createRequest('GET', $url);
        $response = $this->httpClient->sendRequest($request);

        try {
            $result = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR | 0);
        } catch (\Exception $e) {
            throw new InvalidApiResponseException();
        }

        if (isset($result->error)) {
            throw new ApiErrorResponseException(sprintf('API error: %s', $result->error));
        }

        if (!isset($result->unix_seconds, $result->share, $result->signal)) {
            throw new UnexpectedApiResponseException();
        }

        return $result;
    }
}
