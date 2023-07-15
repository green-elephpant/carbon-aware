<?php

declare(strict_types=1);

namespace GreenElephpant\CarbonAware;

use GreenElephpant\CarbonAware\CarbonForecast\CarbonForecast;
use GreenElephpant\CarbonAware\CarbonIntensity\CarbonIntensity;
use GreenElephpant\CarbonAware\Connector\ConnectorInterface;
use GreenElephpant\CarbonAware\Location\Location;
use Psr\SimpleCache\CacheInterface;

class CarbonAwareService implements CarbonAwareCurrentInterface, CarbonAwareForecastInterface
{
    public const THRESHOLD_LOW = 350;
    public const THRESHOLD_HIGH = 600;

    /**
     * @var \GreenElephpant\CarbonAware\Connector\ConnectorInterface
     */
    private $connector;

    /**
     * @var \GreenElephpant\CarbonAware\Location\Location
     */
    private $defaultLocation;

    /**
     * @var \Psr\SimpleCache\CacheInterface|null
     */
    private $cache;

    public function __construct(
        ConnectorInterface $connector,
        Location $defaultLocation,
        ?CacheInterface $cache = null
    ) {
        $this->connector = $connector;
        $this->defaultLocation = $defaultLocation;
        $this->cache = $cache;
    }

    public function isLow(Location $location = null): bool
    {
        $carbonIntensity = $this->getCurrent($location);

        // TODO use Parambag to set low, high
        // OR get from OpenCarbonForecast API?
        return $carbonIntensity->co2e <= self::THRESHOLD_LOW;
    }

    public function isAverage(Location $location = null): bool
    {
        $carbonIntensity = $this->getCurrent($location);

        return ($carbonIntensity->co2e > self::THRESHOLD_LOW && $carbonIntensity->co2e < self::THRESHOLD_HIGH);
    }

    public function isHigh(Location $location = null): bool
    {
        $carbonIntensity = $this->getCurrent($location);

        return $carbonIntensity->co2e >= self::THRESHOLD_HIGH;
    }

    private function getCurrentFromConnector(Location $location = null): CarbonIntensity
    {
        return $this->connector->getCurrent($location ?? $this->defaultLocation);
    }

    private function getForecastFromConnector(Location $location = null): CarbonForecast
    {
        return $this->connector->getForecast($location ?? $this->defaultLocation);
    }

    /**
     * @return mixed
     */
    private function getFromCache(string $cacheKey)
    {
        if (!isset($this->cache)) {
            return null;
        }

        return $this->cache->get($cacheKey);
    }

    /**
     * @param mixed $value
     */
    private function setToCache(string $cacheKey, $value): void
    {
        if (!isset($this->cache)) {
            return;
        }

        $this->cache->set($cacheKey, $value, 1800);
    }

    public function getCurrent(Location $location = null): CarbonIntensity
    {
        $cacheKey = 'emissions_current_' . ($location ?? $this->defaultLocation)->getCountryCode();

        /** @var CarbonIntensity|null $cachedEmissions */
        $cachedEmissions = $this->getFromCache($cacheKey);

        if ($cachedEmissions !== null) {
            return $cachedEmissions;
        }

        $emissions = $this->getCurrentFromConnector($location);

        $this->setToCache($cacheKey, $emissions);

        return $emissions;
    }

    public function getForecast(Location $location = null): CarbonForecast
    {
        $cacheKey = 'emissions_forecast_' . ($location ?? $this->defaultLocation)->getCountryCode();

        /** @var CarbonForecast|null $cachedEmissions */
        $cachedEmissions = $this->getFromCache($cacheKey);

        if ($cachedEmissions !== null) {
            return $cachedEmissions;
        }

        $forecast = $this->getForecastFromConnector($location);

        $this->setToCache($cacheKey, $forecast);

        return $forecast;
    }

    public function getAverage(Location $location, int $timespanHours): int
    {
        if (!isset($this->emissionsHistory)) {
            // TODO Rename Emissionshistory
            throw new \RuntimeException('EmissionsHistory not configured');
        }

        return $this->emissionsHistory->getAveragOverHours($location, $timespanHours);
    }

    /**
     * @param array<Location> $locations
     */
    public function getBestByLocations(array $locations): void
    {
    }
}
