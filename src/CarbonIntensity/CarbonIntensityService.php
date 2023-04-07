<?php

declare(strict_types=1);

namespace GreenElePHPant\CarbonAware\CarbonIntensity;

use GreenElePHPant\CarbonAware\CarbonAwareCurrentInterface;
use GreenElePHPant\CarbonAware\CarbonAwareHistoricalInterface;
use GreenElePHPant\CarbonAware\Connector\ConnectorInterface;
use GreenElePHPant\CarbonAware\Location\Location;
use Psr\SimpleCache\CacheInterface;

class CarbonIntensityService implements CarbonAwareCurrentInterface, CarbonAwareHistoricalInterface
{
    public const THRESHOLD_LOW = 350;
    public const THRESHOLD_HIGH = 350;

    public function __construct(
        private ConnectorInterface      $connector,
        private Location                $defaultLocation,
        private ?CarbonIntensityHistory $emissionsHistory = null,
        private ?CacheInterface         $cache = null
    ) {
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

    private function getForecastFromConnector(Location $location = null): CarbonIntensity
    {
        return $this->connector->getForecast($location ?? $this->defaultLocation);
    }

    private function getFromCache(string $cacheKey): mixed
    {
        if (!isset($this->cache)) {
            return null;
        }

        return $this->cache->get($cacheKey);
    }

    private function setToCache(string $cacheKey, mixed $value): void
    {
        if (!isset($this->cache)) {
            return;
        }

        $this->cache->set($cacheKey, $value, 1800);
    }

    private function storeEmissions(CarbonIntensity $emissions): void
    {
        if (!isset($this->emissionsHistory)) {
            return;
        }

        $this->emissionsHistory->store($emissions);
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

        $this->storeEmissions($emissions);

        $this->setToCache($cacheKey, $emissions);

        return $emissions;
    }

    public function getForecast(Location $location = null): CarbonIntensity
    {
        $cacheKey = 'emissions_forecast_' . ($location ?? $this->defaultLocation)->getCountryCode();

        /** @var CarbonIntensity|null $cachedEmissions */
        $cachedEmissions = $this->getFromCache($cacheKey);

        if ($cachedEmissions !== null) {
            return $cachedEmissions;
        }

        $emissions = $this->getForecastFromConnector($location);

        // $this->store($emissions);

        $this->setToCache($cacheKey, $emissions);

        return $emissions;
    }

    public function getAverage(Location $location, int $timespanHours): int
    {
        if (!isset($this->emissionsHistory)) {
            throw new EmissionsHistoryNotConfiguredException();
        }

        return $this->emissionsHistory->getAveragOverHours($location, $timespanHours);
    }

    public function getBestByLocations(array $locations): void
    {
    }
}
