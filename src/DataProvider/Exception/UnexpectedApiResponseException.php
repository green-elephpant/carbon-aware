<?php

namespace GreenElephpant\CarbonAware\DataProvider\Exception;

class UnexpectedApiResponseException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Response is missing expected fields');
    }
}
