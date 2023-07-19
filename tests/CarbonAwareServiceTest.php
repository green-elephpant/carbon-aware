<?php

declare(strict_types=1);

namespace GreenElephpant\CarbonAware\Test;

use GreenElephpant\CarbonAware\CarbonAwareService;
use GreenElephpant\CarbonAware\CarbonIntensity\CarbonIntensity;
use GreenElephpant\CarbonAware\Location\Location;
use GreenElephpant\CarbonAware\Test\Connector\ConnectorMock;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

final class CarbonAwareServiceTest extends TestCase
{
    /**
     * @dataProvider lowCarbonIntensityProvider
     */
    public function testIsLow(int $intensity, bool $isLow): void
    {
        $connector = new ConnectorMock($intensity);
        $carbonAwareService = new CarbonAwareService(
            $connector,
            new Location('de')
        );

        $this->assertEquals($isLow, $carbonAwareService->isLow());
    }

    public function lowCarbonIntensityProvider(): array
    {
        $lowThreshold = CarbonAwareService::THRESHOLD_LOW;

        return [
            [ $lowThreshold - 1, true ],
            [ $lowThreshold, true ],
            [ $lowThreshold + 1, false ],
        ];
    }

    /**
     * @dataProvider highCarbonIntensityProvider
     */
    public function testIsHigh(int $intensity, bool $isHigh): void
    {
        $connector = new ConnectorMock($intensity);
        $carbonAwareService = new CarbonAwareService(
            $connector,
            new Location('de')
        );

        $this->assertEquals($isHigh, $carbonAwareService->isHigh());
    }

    public function highCarbonIntensityProvider(): array
    {
        $highThreshold = CarbonAwareService::THRESHOLD_HIGH;

        return [
            [ $highThreshold - 1, false ],
            [ $highThreshold, true ],
            [ $highThreshold + 1, true ],
        ];
    }

    /**
     * @dataProvider averageCarbonIntensityProvider
     */
    public function testIsAverage(int $intensity, bool $isAverage): void
    {
        $connector = new ConnectorMock($intensity);
        $carbonAwareService = new CarbonAwareService(
            $connector,
            new Location('de')
        );

        $this->assertEquals($isAverage, $carbonAwareService->isAverage());
    }

    public function averageCarbonIntensityProvider(): array
    {
        $lowThreshold = CarbonAwareService::THRESHOLD_LOW;
        $highThreshold = CarbonAwareService::THRESHOLD_HIGH;

        return [
            [ $lowThreshold, false ],
            [ $lowThreshold + 1, true ],
            [ $highThreshold - 1, true ],
            [ $highThreshold, false ],
        ];
    }

    public function testGetCurrent(): void
    {
        $location = new Location('de');
        $expectedIntensity = new CarbonIntensity(
            123,
            $location,
            ConnectorMock::DATETIME_STAMP
        );

        $connector = new ConnectorMock(123);
        $carbonAwareService = new CarbonAwareService(
            $connector,
            $location
        );

        $this->assertEquals($expectedIntensity, $carbonAwareService->getCurrent());
    }

    public function testGetCurrentSetToCache(): void
    {
        $location = new Location('de');
        $connector = new ConnectorMock(123);

        $expectedIntensity = new CarbonIntensity(
            123,
            $location,
            ConnectorMock::DATETIME_STAMP
        );

        $expectedCacheKey = 'emissions_current_' . $location->getCountryCode();
        $cacheMock = $this->createMock(CacheInterface::class);
        $cacheMock
            ->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo($expectedCacheKey)
            )
            ->willReturn(null);
        $cacheMock
            ->expects($this->once())
            ->method('set')
            ->with(
                $this->equalTo($expectedCacheKey),
                $this->equalTo($expectedIntensity),
                $this->equalTo(1800)
            );

        $carbonAwareService = new CarbonAwareService(
            $connector,
            $location,
            $cacheMock
        );

        $this->assertEquals($expectedIntensity, $carbonAwareService->getCurrent());
    }

    public function testGetCurrentGetFromCache(): void
    {
        $location = new Location('de');
        $connector = new ConnectorMock(123);

        $expectedIntensity = new CarbonIntensity(
            123,
            $location,
            ConnectorMock::DATETIME_STAMP
        );

        $expectedCacheKey = 'emissions_current_' . $location->getCountryCode();
        $cacheMock = $this->createMock(CacheInterface::class);
        $cacheMock
            ->expects($this->never())
            ->method('set');
        $cacheMock
            ->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo($expectedCacheKey)
            )
            ->willReturn($expectedIntensity);

        $carbonAwareService = new CarbonAwareService(
            $connector,
            $location,
            $cacheMock
        );

        $this->assertEquals($expectedIntensity, $carbonAwareService->getCurrent());
    }
}