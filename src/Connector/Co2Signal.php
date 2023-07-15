<?php

namespace GreenElephpant\CarbonAware\Connector;

use GreenElephpant\CarbonAware\CarbonForecast\CarbonForecast;
use GreenElephpant\CarbonAware\CarbonIntensity\CarbonIntensity;
use GreenElephpant\CarbonAware\Location\Location;
use Http\Client\HttpClient;
use Psr\Http\Message\RequestFactoryInterface;

class Co2Signal implements ConnectorInterface
{
    /**
     * @readonly
     * @var string
     */
    private $apiKey;

    /**
     * @readonly
     * @var \Http\Client\HttpClient
     */
    private $httpClient;

    /**
     * @readonly
     * @var \Psr\Http\Message\RequestFactoryInterface
     */
    private $requestFactory;

    public function __construct(
        string $apiKey,
        HttpClient $httpClient,
        RequestFactoryInterface $requestFactory
    ) {
        $this->apiKey = $apiKey;
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
    }

    public function getCurrent(Location $region): CarbonIntensity
    {
        $url = 'https://api.co2signal.com/v1/latest?countryCode=' . $region->getCountryCode();

        $request = $this->requestFactory->createRequest('GET', $url);
        $request = $request->withHeader('auth-token', $this->apiKey);
        $response = $this->httpClient->sendRequest($request);

        $result = json_decode($response->getBody()->getContents(), false, 512, 0);

        if (!is_object($result)) {
            throw new \Exception();
        }

        if (!isset($result->data, $result->data->carbonIntensity, $result->data->datetime)) {
            throw new \Exception();
        }

        // IMPORTANT: datetime is UTC. We save as UTC in DB.

        // $dateTime = new \DateTime($result->data->datetime);
        // $dateTime->setTimezone(new \DateTimeZone('Europe/Berlin'));
        //  $dateTime->format('Y-m-d H:i:s')

        // SELECT CONVERT_TZ(created_at, 'UTC', 'Europe/Berlin'), created_at FROM emissions WHERE id = 1079;
        // https://dev.mysql.com/doc/refman/8.0/en/date-and-time-functions.html#function_convert-tz

        return new CarbonIntensity(
            $result->data->carbonIntensity,
            $region,
            date('Y-m-d H:i:s', strtotime($result->data->datetime))
        );
    }

    public function getForecast(Location $region): CarbonForecast
    {
        throw new \RuntimeException('CO2Signal does not support forecast');
    }
}
