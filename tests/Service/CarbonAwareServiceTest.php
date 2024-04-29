<?php

declare(strict_types=1);

namespace GreenElephpant\CarbonAware\Test\Service;

use GreenElephpant\CarbonAware\Carbon\Indicator;
use GreenElephpant\CarbonAware\DataProvider\DataProviderInterface;
use GreenElephpant\CarbonAware\Location\Location;
use GreenElephpant\CarbonAware\Service\CarbonAwareService;
use PHPUnit\Framework\TestCase;

/**
 * @covers CarbonAwareService
 */
final class CarbonAwareServiceTest extends TestCase
{
    /**
     * @dataProvider lowCarbonIntensityProvider
     */
    public function testIsLow(Indicator $indicator, bool $isLow): void
    {
        $dataProviderMock = $this->createMock(DataProviderInterface::class);
        $dataProviderMock
            ->expects($this->once())
            ->method('getCurrent')
            ->willReturn($indicator);

        $carbonAwareService = new CarbonAwareService(
            $dataProviderMock,
            new Location('DE')
        );

        $this->assertEquals($isLow, $carbonAwareService->isLow());
    }

    /**
     * @return array<array<\GreenElephpant\CarbonAware\Carbon\Indicator, bool>>
     */
    public function lowCarbonIntensityProvider(): array
    {
        return [
            [Indicator::createLow(time()), true],
            [Indicator::createAverage(time()), false],
            [Indicator::createHigh(time()), false],
        ];
    }

    /**
     * @dataProvider highCarbonIntensityProvider
     */
    public function testIsHigh(Indicator $indicator, bool $isHigh): void
    {
        $dataProviderMock = $this->createMock(DataProviderInterface::class);
        $dataProviderMock
            ->expects($this->once())
            ->method('getCurrent')
            ->willReturn($indicator);

        $carbonAwareService = new CarbonAwareService(
            $dataProviderMock,
            new Location('DE')
        );

        $this->assertEquals($isHigh, $carbonAwareService->isHigh());
    }

    /**
     * @return array<array<\GreenElephpant\CarbonAware\Carbon\Indicator, bool>>
     */
    public function highCarbonIntensityProvider(): array
    {
        return [
            [Indicator::createHigh(time()), true],
            [Indicator::createAverage(time()), false],
            [Indicator::createLow(time()), false],
        ];
    }

    /**
     * @dataProvider averageCarbonIntensityProvider
     */
    public function testIsAverage(Indicator $indicator, bool $isAverage): void
    {
        $dataProviderMock = $this->createMock(DataProviderInterface::class);
        $dataProviderMock
            ->expects($this->once())
            ->method('getCurrent')
            ->willReturn($indicator);

        $carbonAwareService = new CarbonAwareService(
            $dataProviderMock,
            new Location('DE')
        );

        $this->assertEquals($isAverage, $carbonAwareService->isAverage());
    }

    /**
     * @return array<array<\GreenElephpant\CarbonAware\Carbon\Indicator, bool>>
     */
    public function averageCarbonIntensityProvider(): array
    {
        return [
            [Indicator::createHigh(time()), false],
            [Indicator::createAverage(time()), true],
            [Indicator::createLow(time()), false],
        ];
    }

    /*
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
    */
}