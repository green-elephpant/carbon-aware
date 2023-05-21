<?php

declare(strict_types=1);

namespace GreenElephpant\CarbonAware\Location;

class Location implements LocationInterface
{
    /**
     * @readonly
     * @var string
     */
    private $countryCode;

    public function __construct(string $countryCode)
    {
        $this->countryCode = $countryCode;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }
}
