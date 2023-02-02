<?php

namespace GreenElePHPant\CarbonAware\Connector;

use GreenElePHPant\CarbonAware\Emissions\Emissions;
use GreenElePHPant\CarbonAware\Location\Location;

interface ConnectorInterface
{
    public function getEmissions(Location $region): Emissions;
}
