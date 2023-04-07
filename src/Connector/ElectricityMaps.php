<?php

namespace GreenElePHPant\CarbonAware\Connector;

use GreenElePHPant\CarbonAware\CarbonIntensity\CarbonIntensity;
use GreenElePHPant\CarbonAware\Location\Location;
use Http\Client\HttpClient;
use Psr\Http\Message\RequestFactoryInterface;

class ElectricityMaps implements ConnectorInterface
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $urlCode,
        private readonly HttpClient $httpClient,
        private readonly RequestFactoryInterface $requestFactory
    ) {
    }

    private function getApiBaseUrl(): string
    {
        return 'https://api-access.electricitymaps.com/' . $this->urlCode;
    }

    public function getCurrent(Location $region): CarbonIntensity
    {
        $url = $this->getApiBaseUrl() . '/carbon-intensity/latest?zone=' . $region->getCountryCode();

        $request = $this->requestFactory->createRequest('GET', $url);
        $request = $request->withHeader('X-BLOBR-KEY', $this->apiKey);
        $response = $this->httpClient->sendRequest($request);

        $result = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);

        if (!is_object($result)) {
            throw new \Exception();
        }

        if (!isset($result->carbonIntensity, $result->datetime)) {
            throw new \Exception();
        }

        // IMPORTANT: datetime is UTC. We save as UTC in DB.

        // $dateTime = new \DateTime($result->datetime);
        // $dateTime->setTimezone(new \DateTimeZone('Europe/Berlin'));
        //  $dateTime->format('Y-m-d H:i:s')

        // SELECT CONVERT_TZ(created_at, 'UTC', 'Europe/Berlin'), created_at FROM emissions WHERE id = 1079;
        // https://dev.mysql.com/doc/refman/8.0/en/date-and-time-functions.html#function_convert-tz

        return new CarbonIntensity(
            $result->carbonIntensity,
            $region,
            date('Y-m-d H:i:s', strtotime($result->datetime))
        );
    }

    public function getForecast(Location $region): CarbonIntensity
    {
        $url = $this->getApiBaseUrl() . '/carbon-intensity/forecast?zone=' . $region->getCountryCode();

        $request = $this->requestFactory->createRequest('GET', $url);
        $request = $request->withHeader('X-BLOBR-KEY', $this->apiKey);
        $response = $this->httpClient->sendRequest($request);

        $result = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);

        // Idee: in Array umwandeln, key: Date, value:

        if (!is_object($result)) {
            throw new \Exception();
        }

        if (!isset($result->carbonIntensity, $result->datetime)) {
            throw new \Exception();
        }

        // IMPORTANT: datetime is UTC. We save as UTC in DB.

        // $dateTime = new \DateTime($result->datetime);
        // $dateTime->setTimezone(new \DateTimeZone('Europe/Berlin'));
        //  $dateTime->format('Y-m-d H:i:s')

        // SELECT CONVERT_TZ(created_at, 'UTC', 'Europe/Berlin'), created_at FROM emissions WHERE id = 1079;
        // https://dev.mysql.com/doc/refman/8.0/en/date-and-time-functions.html#function_convert-tz

        return new CarbonIntensity(
            $result->carbonIntensity,
            $region,
            date('Y-m-d H:i:s', strtotime($result->datetime))
        );
    }
}
