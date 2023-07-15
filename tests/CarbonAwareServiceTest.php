<?php

declare(strict_types=1);

namespace GreenElephpant\CarbonAware\Test;

use GreenElephpant\CarbonAware\CarbonAwareService;
use GreenElephpant\CarbonAware\Location\Location;
use GreenElephpant\CarbonAware\Test\Connector\ConnectorMock;
use PHPUnit\Framework\TestCase;

final class CarbonAwareServiceTest extends TestCase
{
    public function testIsLow(): void
    {
        $connector = new ConnectorMock(123);
        $carbonAwareService = new CarbonAwareService(
            $connector,
            new Location('de')
        );

        $this->assertTrue($carbonAwareService->isLow());
    }
}