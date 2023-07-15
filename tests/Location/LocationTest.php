<?php

declare(strict_types=1);

namespace GreenElephpant\CarbonAware\Test\Location;

use GreenElephpant\CarbonAware\Location\Location;
use GreenElephpant\CarbonAware\Location\LocationInterface;
use PHPUnit\Framework\TestCase;

final class LocationTest extends TestCase
{
    public function testHandleLocationString(): void
    {
        $location = new Location('de');

        $this->assertInstanceOf(LocationInterface::class, $location);
        $this->assertSame('de', $location->getCountryCode());
    }
}