<?php

declare(strict_types=1);

namespace GreenElephpant\CarbonAware\Carbon;

class Indicator
{
    public const LOW = 1;
    public const AVERAGE = 0;
    public const HIGH = -1;

    public int $indicator;

    public int $timestamp;

    public function __construct(
        int $indicator,
        int $timestamp
    ) {
        $this->indicator = $indicator;
        $this->timestamp = $timestamp;
    }

    public function isHigh(): bool
    {
        return $this->indicator === self::HIGH;
    }

    public function isAverage(): bool
    {
        return $this->indicator === self::AVERAGE;
    }

    public function isLow(): bool
    {
        return $this->indicator === self::LOW;
    }

    public static function createHigh(int $timestamp): self
    {
        return new self(self::HIGH, $timestamp);
    }

    public static function createAverage(int $timestamp): self
    {
        return new self(self::AVERAGE, $timestamp);
    }

    public static function createLow(int $timestamp): self
    {
        return new self(self::LOW, $timestamp);
    }
}
