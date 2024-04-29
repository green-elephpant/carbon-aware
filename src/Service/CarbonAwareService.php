<?php

declare(strict_types=1);

namespace GreenElephpant\CarbonAware\Service;

use GreenElephpant\CarbonAware\Carbon\Forecast;
use GreenElephpant\CarbonAware\Carbon\Indicator;
use GreenElephpant\CarbonAware\DataProvider\DataProviderInterface;
use GreenElephpant\CarbonAware\Location\Location;
use Psr\SimpleCache\CacheInterface;

class CarbonAwareService
{
    private DataProviderInterface $dataProvider;

    private Location $defaultLocation;

    private ?CacheInterface $cache;

    public function __construct(
        DataProviderInterface $dataProvider,
        Location $defaultLocation,
        ?CacheInterface $cache = null
    ) {
        $this->dataProvider = $dataProvider;
        $this->defaultLocation = $defaultLocation;
        $this->cache = $cache;
    }

    public function isLow(?Location $location = null): bool
    {
        return $this->dataProvider->getCurrent($location)->isLow();
    }

    public function isAverage(?Location $location = null): bool
    {
        return $this->dataProvider->getCurrent($location)->isAverage();
    }

    public function isHigh(?Location $location = null): bool
    {
        return $this->dataProvider->getCurrent($location)->isHigh();
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

    private function getCacheKey(string $prefix, Location $location = null): string
    {
        $array = explode('\\', get_class($this->dataProvider));
        return $prefix . '_' .
            array_pop($array) . '_' .
            ($location ?? $this->defaultLocation)->getCountryCode();
    }

    public function getCurrent(?Location $location = null): Indicator
    {
        $cacheKey = $this->getCacheKey('current');

        /** @var Indicator|null $cachedIndicator */
        $cachedIndicator = $this->getFromCache($cacheKey);

        if ($cachedIndicator !== null) {
            return $cachedIndicator;
        }

        $indicator = $this->dataProvider->getCurrent($location);

        $this->setToCache($cacheKey, $indicator);

        return $indicator;
    }

    public function getForecast(?Location $location = null): Forecast
    {
        $cacheKey = $this->getCacheKey('forecast');

        /** @var Forecast|null $cachedEmissions */
        $cachedEmissions = $this->getFromCache($cacheKey);

        if ($cachedEmissions !== null) {
            return $cachedEmissions;
        }

        $forecast = $this->dataProvider->getForecast($location);

        $this->setToCache($cacheKey, $forecast);

        return $forecast;
    }
}
