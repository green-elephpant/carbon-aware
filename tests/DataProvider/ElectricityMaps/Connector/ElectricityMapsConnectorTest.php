<?php

declare(strict_types=1);

namespace GreenElephpant\CarbonAware\Test\DataProvider\ElectricityMaps\Connector;

use GreenElephpant\CarbonAware\Location\Location;
use GreenElephpant\CarbonAware\DataProvider\ElectricityMaps\Connector\ElectricityMapsConnector;
use GreenElephpant\CarbonAware\DataProvider\Exception\ApiErrorResponseException;
use GreenElephpant\CarbonAware\DataProvider\Exception\InvalidApiResponseException;
use GreenElephpant\CarbonAware\DataProvider\Exception\UnexpectedApiResponseException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @coversDefaultClass \GreenElephpant\CarbonAware\DataProvider\ElectricityMaps\Connector\ElectricityMapsConnector
 */
final class ElectricityMapsConnectorTest extends TestCase
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
     * @covers ::getCarbonIntensityLatest
     */
    public function testGetCarbonIntensityLatestSuccessful(): void
    {
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);

        /* Token doesn't seem to be needed for CarbonIntensityLatest?
        $request
            ->expects($this->once())
            ->method('withHeader')
            ->with('auth-token', 'test-api-key')
            ->willReturn(
                // because withHeader() will return MessageInterface instead of RequestInterface
                $this->createMock(RequestInterface::class)
            );
        */

        $expectedUrl = ElectricityMapsConnector::API_URL .
            '/' . ElectricityMapsConnector::CARBON_INTENSITY_LATEST . '?zone=de';

        $requestFactory
            ->expects($this->once())
            ->method('createRequest')
            ->with('GET', $expectedUrl)
            ->willReturn($request);

        $electricityMapsConnector = new ElectricityMapsConnector(
            $this->createClientMock(
                file_get_contents(__DIR__ . '/fixtures/response_carbon_intensity_latest.json')
            ),
            $requestFactory,
            'test-api-key'
        );

        $response = $electricityMapsConnector->getCarbonIntensityLatest(new Location('DE'));

        $this->assertNotNull($response);
        $this->assertInstanceOf(\stdClass::class, $response);

        $this->assertObjectHasProperty('zone', $response);
        $this->assertEquals('DE', $response->zone);

        $this->assertObjectHasProperty('carbonIntensity', $response);
        $this->assertEquals(428, $response->carbonIntensity);

        $this->assertObjectHasProperty('isEstimated', $response);
        $this->assertTrue($response->isEstimated);

        $this->assertObjectHasProperty('datetime', $response);
        $this->assertEquals('2024-03-01T23:00:00.000Z', $response->isEstimated);

        $this->assertObjectHasProperty('updatedAt', $response);
        $this->assertEquals('2024-03-01T22:48:49.093ZZ', $response->isEstimated);

        $this->assertObjectHasProperty('createdAt', $response);
        $this->assertEquals('2024-02-27T23:49:20.326Z', $response->isEstimated);
    }

    /**
     * @covers ::getSignal
     */
    public function testGetCurrentInvalidResponse(): void
    {
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);

        $expectedUrl = ElectricityMapsConnector::API_URL . '/' .
            ElectricityMapsConnector::CARBON_INTENSITY_LATEST . '?zone=de';

        $requestFactory
            ->expects($this->once())
            ->method('createRequest')
            ->with('GET', $expectedUrl)
            ->willReturn($request);

        $electricityMapsConnector = new ElectricityMapsConnector(
            $this->createClientMock('some error happened here'),
            $requestFactory,
            'test-api-key'
        );

        $this->expectException(InvalidApiResponseException::class);

        $electricityMapsConnector->getCarbonIntensityLatest(new Location('DE'));
    }

    /**
     * @covers ::getSignal
     */
    public function testGetCurrentUnexpectedResponse(): void
    {
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);

        $expectedUrl = ElectricityMapsConnector::API_URL . '/' .
            ElectricityMapsConnector::CARBON_INTENSITY_LATEST . '?zone=de';

        $requestFactory
            ->expects($this->once())
            ->method('createRequest')
            ->with('GET', $expectedUrl)
            ->willReturn($request);

        $electricityMapsConnector = new ElectricityMapsConnector(
            $this->createClientMock('{"not": "what you expected"}'),
            $requestFactory,
            'test-api-key'
        );

        $this->expectException(UnexpectedApiResponseException::class);

        $electricityMapsConnector->getCarbonIntensityLatest(new Location('DE'));
    }

    /**
     * @covers ::getSignal
     */
    public function testGetCurrentErrorResponse(): void
    {
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);

        $expectedUrl = ElectricityMapsConnector::API_URL . '/' .
            ElectricityMapsConnector::CARBON_INTENSITY_LATEST . '?zone=de';

        $requestFactory
            ->expects($this->once())
            ->method('createRequest')
            ->with('GET', $expectedUrl)
            ->willReturn($request);

        $electricityMapsConnector = new ElectricityMapsConnector(
            $this->createClientMock('{"status": "error", "message": "API tired, API wants to sleep"}'),
            $requestFactory,
            'test-api-key'
        );

        $this->expectException(ApiErrorResponseException::class);
        $this->expectExceptionMessage('API error: API tired, API wants to sleep');

        $electricityMapsConnector->getCarbonIntensityLatest(new Location('DE'));
    }
}
