<?php

declare(strict_types=1);

namespace GreenElephpant\CarbonAware\Carbon;

use GreenElephpant\CarbonAware\Location\Location;

class Intensity
{
    public int $co2e;

    public Location $location;

    public string $datetime;

    public function __construct(
        int $co2e,
        Location $location,
        string $datetime
    ) {
        $this->co2e = $co2e;
        $this->location = $location;
        $this->datetime = $datetime;
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
