<?php

namespace GreenElePHPant\CarbonAware;

use GreenElePHPant\CarbonAware\Location\Location;

interface CarbonAwareForecastInterface
{
    public function getBestTimeForLocation(Location $location);
    public function getBestTime();
}
