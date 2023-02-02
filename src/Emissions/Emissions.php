<?php

declare(strict_types=1);

namespace GreenElePHPant\CarbonAware\Emissions;

use GreenElePHPant\CarbonAware\Location\Location;

class Emissions
{
    public function __construct(
        public int $co2e,
        public Location $location
    ) {
    }

    public function dehydrate(): array
    {
        return [
            'co2e' => $this->co2e,
            'location' => $this->location->getCountryCode()
        ];
    }
}
