<?php

namespace GreenElephpant\CarbonAware;

use GreenElephpant\CarbonAware\Location\Location;

interface CarbonAwareHistoricalInterface
{
    public function getAverage(Location $location, int $timespanHours);
}
