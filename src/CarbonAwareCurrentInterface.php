<?php

namespace GreenElephpant\CarbonAware;

use GreenElephpant\CarbonAware\CarbonIntensity\CarbonIntensity;
use GreenElephpant\CarbonAware\Location\Location;

interface CarbonAwareCurrentInterface
{
    public function getCurrent(Location $location = null): CarbonIntensity;

    public function isLow(Location $location = null): bool;
    public function isAverage(Location $location = null): bool;
    public function isHigh(Location $location = null): bool;

    // TODO
    // public function getBestByLocations(array $locations);
}
