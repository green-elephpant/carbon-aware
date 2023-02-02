<?php

declare(strict_types=1);

namespace GreenElePHPant\CarbonAware\Location;

class Location implements LocationInterface
{
    public function __construct(
        private readonly string $countryCode,
    ) {
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }
}
