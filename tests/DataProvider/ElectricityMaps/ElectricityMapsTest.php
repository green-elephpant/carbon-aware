<?php

declare(strict_types=1);

namespace GreenElephpant\CarbonAware\Test\DataProvider\ElectricityMaps;

use GreenElephpant\CarbonAware\Carbon\Indicator;
use GreenElephpant\CarbonAware\Location\Location;
use GreenElephpant\CarbonAware\DataProvider\ElectricityMaps\Connector\ElectricityMapsConnector;
use GreenElephpant\CarbonAware\DataProvider\ElectricityMaps\ElectricityMaps;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @coversDefaultClass \GreenElephpant\CarbonAware\DataProvider\ElectricityMaps\ElectricityMaps
 */
final class ElectricityMapsTest extends TestCase
{
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

    private function getElectricityMapsConnector(string $jsonFilename): ElectricityMapsConnector
    {
        return new ElectricityMapsConnector(
            $this->createClientMock(file_get_contents($jsonFilename)),
            new Psr17Factory(),
            "test-key"
        );
    }

    /**
     * @covers ::getCurrent
     */
    public function testGetCurrent(): void
    {
        $service = new ElectricityMaps(
            new Location('DE'),
            $this->getElectricityMapsConnector(
                __DIR__ . '/Connector/fixtures/response_carbon_intensity_latest.json'
            )
        );

        $indicator = $service->getCurrent();

        $this->assertEquals(Indicator::HIGH, $indicator->indicator);
        $this->assertEquals(strtotime("2024-03-01T23:00:00.000Z"), $indicator->timestamp);
    }
}

