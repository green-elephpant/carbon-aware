<?php

namespace GreenElephpant\CarbonAware;

use GreenElephpant\CarbonAware\CarbonForecast\CarbonForecast;
use GreenElephpant\CarbonAware\Location\Location;

interface CarbonAwareForecastInterface
{
    public function getForecast(Location $location = null): CarbonForecast;

    // public function isBelowForecastAverage();
    // public function isAboveForecastAverage();

    // public function getBestTimeForLocation(Location $location);
    // public function getBestTime();
}
