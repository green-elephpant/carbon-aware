<?php

declare(strict_types=1);

namespace GreenElePHPant\CarbonAware\Emissions;

use RuntimeException;

class EmissionsHistoryNotConfiguredException extends RuntimeException
{
    protected $message = 'No EmissionsHistory repository configured';
}
