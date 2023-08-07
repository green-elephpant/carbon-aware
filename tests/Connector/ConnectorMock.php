<?php

namespace GreenElephpant\CarbonAware\Test\Connector;

use DateTime;
use GreenElephpant\CarbonAware\CarbonIntensity\CarbonIntensity;
use GreenElephpant\CarbonAware\CarbonForecast\CarbonForecast;
use GreenElephpant\CarbonAware\Connector\ConnectorInterface;
use GreenElephpant\CarbonAware\Location\Location;

class ConnectorMock implements ConnectorInterface
{
    private $intensity;
    private $locationCode;

    public const DATETIME_STAMP = '2023-07-15 01:23:45';

    public function __construct(int $intensity = 123, string $locationCode = 'de')
    {
        $this->intensity = $intensity;
        $this->locationCode = $locationCode;
    }

    public function getCurrent(Location $region): CarbonIntensity
    {
        return new CarbonIntensity(
            $this->intensity,
            new Location($this->locationCode),
            self::DATETIME_STAMP
        );
    }

    public function getForecast(Location $region): CarbonForecast
    {
        return new CarbonForecast(
            [$this->intensity],
            new Location($this->locationCode),
            self::DATETIME_STAMP
        );
    }
}
