<?php

namespace GreenElephpant\CarbonAware\DataProvider\Exception;

class InvalidApiResponseException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Invalid response');
    }
}
