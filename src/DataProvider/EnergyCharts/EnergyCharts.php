<?php

namespace GreenElephpant\CarbonAware\DataProvider\EnergyCharts;

use GreenElephpant\CarbonAware\Carbon\Forecast;
use GreenElephpant\CarbonAware\Carbon\Indicator;
use GreenElephpant\CarbonAware\DataProvider\DataProviderInterface;
use GreenElephpant\CarbonAware\Location\Location;
use GreenElephpant\CarbonAware\DataProvider\EnergyCharts\Connector\EnergyChartsConnector;

/**
 * https://api.energy-charts.info/
 */
class EnergyCharts implements DataProviderInterface
{
    private Location $defaultLocation;

    // Length of a segment in seconds
    public const SEGMENT_LENGTH = 900;

    private EnergyChartsConnector $energyChartsConnector;

    public function __construct(
        Location $defaultLocation,
        EnergyChartsConnector $energyChartsConnector
    ) {
        $this->defaultLocation = $defaultLocation;
        $this->energyChartsConnector = $energyChartsConnector;
    }

    public function getCurrent(?Location $location = null): Indicator
    {
        $result = $this->energyChartsConnector->getSignal($location ?? $this->defaultLocation);

        $currentTime = time();
        $key = 0;

        foreach ($result->unix_seconds as $key => $timestamp) {
            // Loop until we get the current time
            if ($timestamp >= $currentTime) {
                break;
            }
        }

        return $this->createCarbonIndicatorFromSignal($result->signal[$key], $result->unix_seconds[$key]);
    }

    public function getForecast(?Location $location = null): Forecast
    {
        $location = $location ?? $this->defaultLocation;

        $result = $this->energyChartsConnector->getSignal($location);

        $segmentsAboveAverage = [];
        $carbonIndicatorArray = [];
        $averageShare = 0;
        $shareNormalizedArray = [];
        $segmentsAboveAverageCurrentSet = 1;
        $currentTime = time();
        $segmentsCounter = 0;

        foreach ($result->unix_seconds as $key => $timestamp) {
            // We're only interested in future forecasts
            if ($timestamp < $currentTime) {
                continue;
            }

            if (!isset($result->signal[$key], $result->share[$key])) {
                continue;
            }

            $segmentsCounter++;

            $carbonIndicatorArray[$timestamp] = $this->createCarbonIndicatorFromSignal(
                $result->signal[$key],
                $timestamp
            );

            $averageShare += $result->share[$key];
        }

        $averageShare /= $segmentsCounter;

        foreach ($result->unix_seconds as $key => $timestamp) {
            // We're only interested in future forecasts
            if ($timestamp < $currentTime) {
                continue;
            }

            $shareNormalizedArray[$timestamp] = round($result->share[$key] / $averageShare, 4);

            if ($shareNormalizedArray[$timestamp] >= 1) {
                if (!isset($segmentsAboveAverage[$segmentsAboveAverageCurrentSet]['start'])) {
                    $segmentsAboveAverage[$segmentsAboveAverageCurrentSet] = [ 'start' => $timestamp ];
                }
            }

            if ($shareNormalizedArray[$timestamp] < 1) {
                if (isset($segmentsAboveAverage[$segmentsAboveAverageCurrentSet]['start'])) {
                    $segmentsAboveAverage[$segmentsAboveAverageCurrentSet]['end'] = $timestamp;
                    $segmentsAboveAverageCurrentSet++;
                }
            }
        }

        if (
            isset($timestamp, $segmentsAboveAverage[$segmentsAboveAverageCurrentSet]['start']) &&
            !isset($segmentsAboveAverage[$segmentsAboveAverageCurrentSet]['end'])
        ) {
            $segmentsAboveAverage[$segmentsAboveAverageCurrentSet]['end'] = $timestamp;
        }

        // getForecastTimespan (hours)
        return new Forecast(
            $carbonIndicatorArray,
            $shareNormalizedArray,
            $segmentsAboveAverage,
            self::SEGMENT_LENGTH,
            $location,
            date('Y-m-d H:i:s')
        );
    }

    /**
     * Convert Energy-charts API signal to CarbonIndicator
     */
    private function createCarbonIndicatorFromSignal(int $signal, int $timestamp): Indicator
    {
        // https://api.energy-charts.info/#/ren_share/traffic_signal_signal_get
        if ($signal <= 0) {
            return Indicator::createHigh($timestamp);
        }

        if ($signal === 1) {
            return Indicator::createAverage($timestamp);
        }

        return Indicator::createLow($timestamp);
    }
}
