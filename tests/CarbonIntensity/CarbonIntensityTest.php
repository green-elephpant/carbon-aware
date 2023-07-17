<?php

declare(strict_types=1);

namespace GreenElephpant\CarbonAware\Test\Location;

use GreenElephpant\CarbonAware\CarbonIntensity\CarbonIntensity;
use GreenElephpant\CarbonAware\Location\Location;
use PHPUnit\Framework\TestCase;

final class CarbonIntensityTest extends TestCase
{
    public function testDehydrate(): void
    {
        $dateTime = date('Y-m-d H:i:s');

        $carbonIntensity = new CarbonIntensity(
            123,
            new Location('DE'),
            $dateTime
        );

        $expectedArray = [
            'co2e' => 123,
            'location' => 'DE',
            'datetime' => $dateTime
        ];

        $this->assertSame($expectedArray, $carbonIntensity->dehydrate());
    }
}