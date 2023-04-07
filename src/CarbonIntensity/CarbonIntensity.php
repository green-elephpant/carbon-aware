<?php

declare(strict_types=1);

namespace GreenElePHPant\CarbonAware\CarbonIntensity;

use GreenElePHPant\CarbonAware\Location\Location;

class CarbonIntensity
{
    public function __construct(
        public int $co2e,
        public Location $location,
        public string $datetime
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function dehydrate(): array
    {
        return [
            'co2e' => $this->co2e,
            'location' => $this->location->getCountryCode(),
            'datetime' => $this->datetime
        ];
    }
}
