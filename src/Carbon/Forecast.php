<?php

declare(strict_types=1);

namespace GreenElephpant\CarbonAware\Carbon;

use GreenElephpant\CarbonAware\Location\Location;

class Forecast
{
    /**
     * Length of a segment in seconds
     * A segment is a time period for which the carbon intensity is forecasted
     */
    public int $segmentLength;

    /**
     * The difference in seconds between two segments is defined by $segmentLength
     *
     * [
     *    1234577 => 1.00,
     *    1234590 => 1.20,
     *    1234800 => 0.80
     * ]
     *
     * @var array<int|string, float> $carbonForecast
     */
    public array $carbonForecast;

    /**
     *  [
     *     1234577 => 0,
     *     1234590 => 1,
     *     1234800 => -1
     *  ]
     *
     * @var array<int|string, \GreenElephpant\CarbonAware\Carbon\Indicator> $carbonIndicator
     */
    public array $carbonIndicator;

    /**
     *  [
     *     1 => [ 'start' => 1234590, 'end' => 1234800 ],
     *     2 => [ 'start' => 2234590, 'end' => 2234800 ],
     *  ]
     *
     * @var array<int, array<string, int>> $segmentsAboveAverage
     */
    public array $segmentsAboveAverage;

    public Location $location;

    public string $datetime;

    /**
     * @param array<int|string, \GreenElephpant\CarbonAware\Carbon\Indicator> $carbonIndicator
     * @param array<int|string, float> $carbonForecast
     * @param array<int, array<string, int>> $segmentsAboveAverage
     */
    public function __construct(
        array $carbonIndicator,
        array $carbonForecast,
        array $segmentsAboveAverage,
        int $segmentLength,
        Location $location,
        string $datetime
    ) {
        $this->carbonIndicator = $carbonIndicator;
        $this->carbonForecast = $carbonForecast;
        $this->segmentsAboveAverage = $segmentsAboveAverage;
        $this->segmentLength = $segmentLength;
        $this->location = $location;
        $this->datetime = $datetime;
    }

    public function getNext(int $indicatorThreshold = 1, int $timespan = 0): void
    {
        // timespan = 0 -> no restriction, anytime in the available forecast
    }

    public function getCurrentIndicator(): void
    {
        // get the current indicator
    }
}
