<?php

declare(strict_types=1);

namespace GreenElephpant\CarbonAware\Test\DataProvider\EnergyCharts;

use GreenElephpant\CarbonAware\Location\Location;
use GreenElephpant\CarbonAware\DataProvider\ElectricityMaps\Connector\ElectricityMapsConnector;
use GreenElephpant\CarbonAware\DataProvider\EnergyCharts\Connector\EnergyChartsConnector;
use GreenElephpant\CarbonAware\DataProvider\EnergyCharts\EnergyCharts;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @coversDefaultClass \GreenElephpant\CarbonAware\DataProvider\EnergyCharts\EnergyCharts
 */
final class EnergyChartsTest extends TestCase
{
    // See function time() at the end of the file
    public static int $now;

    private function createClientMock(string $responseBody): ClientInterface
    {
        $httpClient = $this->createMock(ClientInterface::class);

        $contentStream = $this->createMock(StreamInterface::class);
        $contentStream
            ->expects($this->once())
            ->method('getContents')
            ->willReturn($responseBody);
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($contentStream);
        $httpClient
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response);

        return $httpClient;
    }

    private function getEnergyChartsConnector(string $jsonFilename): EnergyChartsConnector
    {
        return new EnergyChartsConnector(
            $this->createClientMock(file_get_contents($jsonFilename)),
            new Psr17Factory()
        );
    }

    /**
     * @dataProvider getCurrentDataProvider
     * @covers ::getCurrent
     */
    public function testGetCurrent(int $timestamp, int $expectedIndicatorLevel): void
    {
        self::$now = $timestamp;

        $service = new EnergyCharts(
            new Location('DE'),
            $this->getEnergyChartsConnector(
                __DIR__ . '/Connector/fixtures/response_short.json'
            )
        );

        $indicator = $service->getCurrent();

        $this->assertEquals($expectedIndicatorLevel, $indicator->indicator);
    }

    /**
     * @return array<array<int, int>>
     */
    public function getCurrentDataProvider(): array
    {
        return [
            [1708729200, -1],
            [1708730100, 0],
            [1708731000, 1],
            [1708731900, 0],
        ];
    }

    /**
     * @covers ::getForecast
     */
    public function testGetForecast(): void
    {
        self::$now = 1708730100;

        $service = new EnergyCharts(
            new Location('DE'),
            $this->getEnergyChartsConnector(
                __DIR__ . '/Connector/fixtures/response_short.json'
            )
        );

        $forecast = $service->getForecast();

        $this->assertEquals(900, $forecast->segmentLength);

        // Forecast (normalized)
        $this->assertEquals([
            1708730100 => 1.0676,
            1708731000 => 0.8421,
            1708731900 => 1.0903,
        ], $forecast->carbonForecast);

        // Indicators
        $this->assertArrayHasKey(1708730100, $forecast->carbonIndicator);
        $this->assertArrayHasKey(1708731000, $forecast->carbonIndicator);
        $this->assertArrayHasKey(1708731900, $forecast->carbonIndicator);

        $this->assertEquals(0, $forecast->carbonIndicator[1708730100]->indicator);
        $this->assertEquals(1708730100, $forecast->carbonIndicator[1708730100]->timestamp);
        $this->assertEquals(1, $forecast->carbonIndicator[1708731000]->indicator);
        $this->assertEquals(1708731000, $forecast->carbonIndicator[1708731000]->timestamp);
        $this->assertEquals(0, $forecast->carbonIndicator[1708731900]->indicator);
        $this->assertEquals(1708731900, $forecast->carbonIndicator[1708731900]->timestamp);

        // Segments Above Average
        $this->assertEquals([
            1 => [
                'start' => 1708730100,
                'end' => 1708731000,
            ],
            2 => [
                'start' => 1708731900,
                'end' => 1708731900,
            ],
        ], $forecast->segmentsAboveAverage);
    }

    /**
     * @covers ::getForecast
     */
    public function testGetLongForecast(): void
    {
        self::$now = 1708730100;

        $service = new EnergyCharts(
            new Location('DE'),
            $this->getEnergyChartsConnector(
                __DIR__ . '/Connector/fixtures/response.json'
            )
        );

        $forecast = $service->getForecast();

        $this->assertEquals([
            1 => [
                'start' => 1708760700,
                'end' => 1708789500,
            ],
            2 => [
                'start' => 1708812000,
                'end' => 1708872300,
            ],
        ], $forecast->segmentsAboveAverage);
    }
}

namespace GreenElephpant\CarbonAware\DataProvider\EnergyCharts;

use GreenElephpant\CarbonAware\Test\DataProvider\EnergyCharts\EnergyChartsTest;

/**
 * Override time() function in GreenElephpant\CarbonAware\Service\EnergyCharts namespace
 * @see https://www.schmengler-se.de/en/2011/03/php-mocking-built-in-functions-like-time-in-unit-tests/
 */
function time(): int
{
    return EnergyChartsTest::$now ?: \time();
}
