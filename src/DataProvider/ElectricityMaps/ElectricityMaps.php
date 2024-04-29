<?php

namespace GreenElephpant\CarbonAware\DataProvider\ElectricityMaps;

use GreenElephpant\CarbonAware\Carbon\Forecast;
use GreenElephpant\CarbonAware\Carbon\Indicator;
use GreenElephpant\CarbonAware\DataProvider\DataProviderInterface;
use GreenElephpant\CarbonAware\Location\Location;
use GreenElephpant\CarbonAware\DataProvider\ElectricityMaps\Connector\ElectricityMapsConnector;

/**
 * https://static.electricitymaps.com/api/docs/index.html
 */
class ElectricityMaps implements DataProviderInterface
{
    // Length of a segment in seconds
    public const SEGMENT_LENGTH = 900;

    private Location $defaultLocation;

    private ElectricityMapsConnector $electricityMapsConnector;

    public function __construct(
        Location $defaultLocation,
        ElectricityMapsConnector $electricityMapsConnector
    ) {
        $this->defaultLocation = $defaultLocation;
        $this->electricityMapsConnector = $electricityMapsConnector;
    }

    public function getCurrent(Location $location = null): Indicator
    {
        $result = $this->electricityMapsConnector->getCarbonIntensityLatest($location ?? $this->defaultLocation);

        // TODO add Location?
        return $this->createCarbonIndicatorFromIntensity(
            $result->carbonIntensity,
            strtotime($result->datetime)
        );
    }

    public function getForecast(Location $location = null): Forecast
    {
        throw new \Exception("Not implemented");
    }

    /**
     * Convert CO2e API signal to CarbonIndicator
     */
    private function createCarbonIndicatorFromIntensity(int $intensity, int $timestamp): Indicator
    {
        // TODO values only for Germany, review
        if ($intensity > 400) {
            return Indicator::createHigh($timestamp);
        }

        if ($intensity < 200) {
            return Indicator::createLow($timestamp);
        }

        return Indicator::createAverage($timestamp);
    }
}
