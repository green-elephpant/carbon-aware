<?php

namespace GreenElePHPant\CarbonAware;

use GreenElePHPant\CarbonAware\Location\Location;

interface CarbonAwareHistoricalInterface
{
    public function getAverage(Location $location, int $timespanHours);
}
