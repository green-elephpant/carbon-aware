<?php

namespace GreenElephpant\CarbonAware\DataProvider;

use GreenElephpant\CarbonAware\Carbon\Forecast;
use GreenElephpant\CarbonAware\Carbon\Indicator;
use GreenElephpant\CarbonAware\Location\Location;

interface DataProviderInterface
{
    public function getCurrent(Location $location = null): Indicator;

    public function getForecast(Location $location = null): Forecast;
}
