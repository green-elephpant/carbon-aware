<?php

declare(strict_types=1);

namespace GreenElephpant\CarbonAware\Test\DataProvider\EnergyCharts\Connector;

use GreenElephpant\CarbonAware\Location\Location;
use GreenElephpant\CarbonAware\DataProvider\EnergyCharts\Connector\EnergyChartsConnector;
use GreenElephpant\CarbonAware\DataProvider\Exception\ApiErrorResponseException;
use GreenElephpant\CarbonAware\DataProvider\Exception\InvalidApiResponseException;
use GreenElephpant\CarbonAware\DataProvider\Exception\UnexpectedApiResponseException;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @coversDefaultClass \GreenElephpant\CarbonAware\DataProvider\EnergyCharts\Connector\EnergyChartsConnector
 */
final class EnergyChartsConnectorTest extends TestCase
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

    /**
     * @covers ::getSignal
     */
    public function testGetCurrentSuccessful(): void
    {
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);

        $expectedUrl = EnergyChartsConnector::API_URL . '/' . EnergyChartsConnector::SIGNAL_ENDPOINT . '?country=de';

        $requestFactory
            ->expects($this->once())
            ->method('createRequest')
            ->with('GET', $expectedUrl)
            ->willReturn($request);

        $energyChartsConnector = new EnergyChartsConnector(
            $this->createClientMock(file_get_contents(__DIR__ . '/fixtures/response_short.json')),
            $requestFactory
        );

        $response = $energyChartsConnector->getSignal(new Location('DE'));

        $this->assertNotNull($response);
        $this->assertInstanceOf(\stdClass::class, $response);

        $this->assertObjectHasProperty('unix_seconds', $response);
        $this->assertEquals([
            0 => 1708729200,
            1 => 1708730100,
            2 => 1708731000,
            3 => 1708731900,
        ], $response->unix_seconds);

        $this->assertObjectHasProperty('share', $response);
        $this->assertEquals([
            0 => 80.4,
            1 => 51.6,
            2 => 40.7,
            3 => 52.7,
        ], $response->share);

        $this->assertObjectHasProperty('signal', $response);
        $this->assertEquals([
            0 => 2,
            1 => 1,
            2 => 0,
            3 => 1,
        ], $response->signal);

        $this->assertObjectHasProperty('substitute', $response);
        $this->assertFalse($response->substitute);
    }

    /**
     * @covers ::getSignal
     */
    public function testGetCurrentInvalidResponse(): void
    {
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);

        $expectedUrl = EnergyChartsConnector::API_URL . '/' . EnergyChartsConnector::SIGNAL_ENDPOINT . '?country=de';

        $requestFactory
            ->expects($this->once())
            ->method('createRequest')
            ->with('GET', $expectedUrl)
            ->willReturn($request);

        $energyChartsConnector = new EnergyChartsConnector(
            $this->createClientMock('some error happened here'),
            $requestFactory
        );

        $this->expectException(InvalidApiResponseException::class);

        $energyChartsConnector->getSignal(new Location('DE'));
    }

    /**
     * @covers ::getSignal
     */
    public function testGetCurrentUnexpectedResponse(): void
    {
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);

        $expectedUrl = EnergyChartsConnector::API_URL . '/' . EnergyChartsConnector::SIGNAL_ENDPOINT . '?country=de';

        $requestFactory
            ->expects($this->once())
            ->method('createRequest')
            ->with('GET', $expectedUrl)
            ->willReturn($request);

        $energyChartsConnector = new EnergyChartsConnector(
            $this->createClientMock('{"not": "what you expected"}'),
            $requestFactory
        );

        $this->expectException(UnexpectedApiResponseException::class);

        $energyChartsConnector->getSignal(new Location('DE'));
    }

    /**
     * @covers ::getSignal
     */
    public function testGetCurrentErrorResponse(): void
    {
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);

        $expectedUrl = EnergyChartsConnector::API_URL . '/' . EnergyChartsConnector::SIGNAL_ENDPOINT . '?country=de';

        $requestFactory
            ->expects($this->once())
            ->method('createRequest')
            ->with('GET', $expectedUrl)
            ->willReturn($request);

        $energyChartsConnector = new EnergyChartsConnector(
            $this->createClientMock('{"error": "API tired, API wants to sleep"}'),
            $requestFactory
        );

        $this->expectException(ApiErrorResponseException::class);
        $this->expectExceptionMessage('API error: API tired, API wants to sleep');

        $energyChartsConnector->getSignal(new Location('DE'));
    }
}
