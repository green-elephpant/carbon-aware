<?php

namespace GreenElephpant\CarbonAware;

use GreenElephpant\CarbonAware\Location\Location;

interface CarbonAwareHistoricalInterface
{
    /**
     * @return array<int>
     */
    public function getAverage(Location $location, int $timespanHours): array;
}
