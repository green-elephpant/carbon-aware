<?php

declare(strict_types=1);

namespace GreenElePHPant\CarbonAware\Emissions;

interface EmissionsServiceInterface
{
    public function isLow(): bool;
}
