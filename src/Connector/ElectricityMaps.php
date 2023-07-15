<?php

namespace GreenElephpant\CarbonAware\Connector;

use GreenElephpant\CarbonAware\CarbonForecast\CarbonForecast;
use GreenElephpant\CarbonAware\CarbonIntensity\CarbonIntensity;
use GreenElephpant\CarbonAware\Location\Location;
use Http\Client\HttpClient;
use Psr\Http\Message\RequestFactoryInterface;

class ElectricityMaps implements ConnectorInterface
{
    /**
     * @readonly
     * @var string
     */
    private $apiKey;

    /**
     * @readonly
     * @var string
     */
    private $urlCode;

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
        string $urlCode,
        HttpClient $httpClient,
        RequestFactoryInterface $requestFactory
    ) {
        $this->apiKey = $apiKey;
        $this->urlCode = $urlCode;
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
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

        $result = json_decode($response->getBody()->getContents(), false, 512, 0);

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

    public function getForecast(Location $region): CarbonForecast
    {
        $url = $this->getApiBaseUrl() . '/carbon-intensity/forecast?zone=' . $region->getCountryCode();

        $request = $this->requestFactory->createRequest('GET', $url);
        $request = $request->withHeader('X-BLOBR-KEY', $this->apiKey);
        $response = $this->httpClient->sendRequest($request);

        $result = json_decode($response->getBody()->getContents(), false, 512, 0);

        // Idee: in Array umwandeln, key: Date, value:

        if (!is_object($result)) {
            throw new \Exception();
        }

        if (!isset($result->forecast, $result->updatedAt, $result->zone)) {
            throw new \Exception();
        }

        // IMPORTANT: datetime is UTC. We save as UTC in DB.

        // $dateTime = new \DateTime($result->datetime);
        // $dateTime->setTimezone(new \DateTimeZone('Europe/Berlin'));
        //  $dateTime->format('Y-m-d H:i:s')

        // SELECT CONVERT_TZ(created_at, 'UTC', 'Europe/Berlin'), created_at FROM emissions WHERE id = 1079;
        // https://dev.mysql.com/doc/refman/8.0/en/date-and-time-functions.html#function_convert-tz

        $forecastArray = [];

        foreach ($result->forecast as $forecastItem) {
            $forecastArray[date('Y-m-d H:i:s', strtotime($forecastItem->datetime))] = $forecastItem->carbonIntensity;
        }

        return new CarbonForecast(
            $forecastArray,
            $region,
            date('Y-m-d H:i:s')
        );
    }
}

/*

{"zone":"DE","forecast":[{"carbonIntensity":557,"datetime":"2023-05-02T20:00:00.000Z"},{"carbonIntensity":552,"datetime":"2023-05-02T21:00:00.000Z"},{"carbonIntensity":538,"datetime":"2023-05-02T22:00:00.000Z"},{"carbonIntensity":527,"datetime":"2023-05-02T23:00:00.000Z"},{"carbonIntensity":520,"datetime":"2023-05-03T00:00:00.000Z"},{"carbonIntensity":512,"datetime":"2023-05-03T01:00:00.000Z"},{"carbonIntensity":537,"datetime":"2023-05-03T02:00:00.000Z"},{"carbonIntensity":563,"datetime":"2023-05-03T03:00:00.000Z"},{"carbonIntensity":555,"datetime":"2023-05-03T04:00:00.000Z"},{"carbonIntensity":535,"datetime":"2023-05-03T05:00:00.000Z"},{"carbonIntensity":487,"datetime":"2023-05-03T06:00:00.000Z"},{"carbonIntensity":433,"datetime":"2023-05-03T07:00:00.000Z"},{"carbonIntensity":386,"datetime":"2023-05-03T08:00:00.000Z"},{"carbonIntensity":340,"datetime":"2023-05-03T09:00:00.000Z"},{"carbonIntensity":323,"datetime":"2023-05-03T10:00:00.000Z"},{"carbonIntensity":324,"datetime":"2023-05-03T11:00:00.000Z"},{"carbonIntensity":329,"datetime":"2023-05-03T12:00:00.000Z"},{"carbonIntensity":347,"datetime":"2023-05-03T13:00:00.000Z"},{"carbonIntensity":369,"datetime":"2023-05-03T14:00:00.000Z"},{"carbonIntensity":420,"datetime":"2023-05-03T15:00:00.000Z"},{"carbonIntensity":481,"datetime":"2023-05-03T16:00:00.000Z"},{"carbonIntensity":514,"datetime":"2023-05-03T17:00:00.000Z"},{"carbonIntensity":542,"datetime":"2023-05-03T18:00:00.000Z"},{"carbonIntensity":590,"datetime":"2023-05-03T19:00:00.000Z"},{"carbonIntensity":557,"datetime":"2023-05-03T20:00:00.000Z"},{"carbonIntensity":552,"datetime":"2023-05-03T21:00:00.000Z"},{"carbonIntensity":538,"datetime":"2023-05-03T22:00:00.000Z"},{"carbonIntensity":527,"datetime":"2023-05-03T23:00:00.000Z"},{"carbonIntensity":520,"datetime":"2023-05-04T00:00:00.000Z"},{"carbonIntensity":512,"datetime":"2023-05-04T01:00:00.000Z"},{"carbonIntensity":537,"datetime":"2023-05-04T02:00:00.000Z"},{"carbonIntensity":563,"datetime":"2023-05-04T03:00:00.000Z"},{"carbonIntensity":555,"datetime":"2023-05-04T04:00:00.000Z"},{"carbonIntensity":535,"datetime":"2023-05-04T05:00:00.000Z"},{"carbonIntensity":487,"datetime":"2023-05-04T06:00:00.000Z"},{"carbonIntensity":433,"datetime":"2023-05-04T07:00:00.000Z"},{"carbonIntensity":386,"datetime":"2023-05-04T08:00:00.000Z"},{"carbonIntensity":340,"datetime":"2023-05-04T09:00:00.000Z"},{"carbonIntensity":323,"datetime":"2023-05-04T10:00:00.000Z"},{"carbonIntensity":324,"datetime":"2023-05-04T11:00:00.000Z"},{"carbonIntensity":329,"datetime":"2023-05-04T12:00:00.000Z"},{"carbonIntensity":347,"datetime":"2023-05-04T13:00:00.000Z"},{"carbonIntensity":369,"datetime":"2023-05-04T14:00:00.000Z"},{"carbonIntensity":420,"datetime":"2023-05-04T15:00:00.000Z"},{"carbonIntensity":481,"datetime":"2023-05-04T16:00:00.000Z"},{"carbonIntensity":514,"datetime":"2023-05-04T17:00:00.000Z"},{"carbonIntensity":542,"datetime":"2023-05-04T18:00:00.000Z"},{"carbonIntensity":590,"datetime":"2023-05-04T19:00:00.000Z"}],"updatedAt":"2023-05-02T19:52:09.141Z"}

 */
