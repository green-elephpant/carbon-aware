<?php

namespace GreenElePHPant\CarbonAware\Connector;

use GreenElePHPant\CarbonAware\CarbonIntensity\CarbonIntensity;
use GreenElePHPant\CarbonAware\Location\Location;

interface ConnectorInterface
{
    public function getCurrent(Location $region): CarbonIntensity;

    public function getForecast(Location $region): CarbonIntensity;
}
