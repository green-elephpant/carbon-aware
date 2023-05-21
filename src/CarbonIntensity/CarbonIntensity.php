<?php

declare(strict_types=1);

namespace GreenElephpant\CarbonAware\CarbonIntensity;

use GreenElephpant\CarbonAware\Location\Location;

class CarbonIntensity
{
    /**
     * @var int
     */
    public $co2e;
    /**
     * @var \GreenElephpant\CarbonAware\Location\Location
     */
    public $location;
    /**
     * @var string
     */
    public $datetime;
    public function __construct(int $co2e, Location $location, string $datetime)
    {
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
