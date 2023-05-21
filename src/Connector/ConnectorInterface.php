<?php

namespace GreenElephpant\CarbonAware\Connector;

use GreenElephpant\CarbonAware\CarbonForecast\CarbonForecast;
use GreenElephpant\CarbonAware\CarbonIntensity\CarbonIntensity;
use GreenElephpant\CarbonAware\Location\Location;

interface ConnectorInterface
{
    public function getCurrent(Location $region): CarbonIntensity;

    public function getForecast(Location $region): CarbonForecast;
}
