<?php

namespace GreenElePHPant\CarbonAware;

use GreenElePHPant\CarbonAware\Location\Location;

interface CarbonAwareCurrentInterface
{
    public function getCurrent(Location $location = null);

    public function getBestByLocations(array $locations);
}
