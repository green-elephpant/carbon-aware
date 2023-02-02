<?php

declare(strict_types=1);

namespace GreenElePHPant\CarbonAware\Emissions;

use GreenElePHPant\CarbonAware\CarbonAwareCurrentInterface;
use GreenElePHPant\CarbonAware\CarbonAwareHistoricalInterface;
use GreenElePHPant\CarbonAware\Connector\ConnectorInterface;
use GreenElePHPant\CarbonAware\Location\Location;
use GreenElePHPant\CarbonAware\Repository\EmissionsHistory;
use Psr\SimpleCache\CacheInterface;

class EmissionsService implements CarbonAwareCurrentInterface, CarbonAwareHistoricalInterface
{
    public function __construct(
        private ConnectorInterface $connector,
        private Location $defaultRegion,
        private ?EmissionsHistory $emissionsHistory = null,
        private ?CacheInterface $cache = null
    ) {
    }

    public function isLow(): bool
    {
        $emissions = $this->getCurrent();

        return $emissions->co2e < 250;
    }

    private function getEmissionsFromConnector(Location $location = null): Emissions
    {
        return $this->connector->getEmissions($location ?? $this->defaultRegion);
    }

    private function getCacheKey(Location $location = null): string
    {
        return 'emissions_' . ($location ?? $this->defaultRegion)->getCountryCode() . date('H');
    }

    private function getFromCache(string $cacheKey): mixed
    {
        if (!isset($this->cache)) {
            return null;
        }

        return $this->cache->get($cacheKey);
    }

    private function setToCache(string $cacheKey, mixed $value)
    {
        if (!isset($this->cache)) {
            return;
        }

        $this->cache->set($cacheKey, $value);
    }

    private function storeEmissions(Emissions $emissions)
    {
        if (!isset($this->emissionsHistory)) {
            return;
        }

        $this->emissionsHistory->storeEmissions($emissions);
    }

    public function getCurrent(Location $location = null)
    {
        $cacheKey = $this->getCacheKey($location);

        $cachedEmissions = $this->getFromCache($cacheKey);

        if ($cachedEmissions !== null) {
            return $cachedEmissions;
        }

        $emissions = $this->getEmissionsFromConnector($location);

        $this->storeEmissions($emissions);

        $this->setToCache($cacheKey, $emissions);

        return $emissions;
    }

    public function getAverage(Location $location, int $timespanHours)
    {
        if (!isset($this->emissionsHistory)) {
            throw new EmissionsHistoryNotConfiguredException();
        }

        return $this->emissionsHistory->getAveragOverHours($location, $timespanHours);
    }

    public function getBestByLocations(array $locations)
    {
    }
}
