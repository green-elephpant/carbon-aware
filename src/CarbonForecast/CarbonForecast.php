<?php

declare(strict_types=1);

namespace GreenElephpant\CarbonAware\CarbonForecast;

use GreenElephpant\CarbonAware\Location\Location;

class CarbonForecast
{
    /**
     * @var mixed[]
     */
    public $forecast;

    /**
     * @var \GreenElephpant\CarbonAware\Location\Location
     */
    public $location;

    /**
     * @var string
     */
    public $datetime;

    /**
     * @param array<Object> $forecast
     */
    public function __construct(array $forecast, Location $location, string $datetime)
    {
        $this->forecast = $forecast;
        $this->location = $location;
        $this->datetime = $datetime;
    }
}
