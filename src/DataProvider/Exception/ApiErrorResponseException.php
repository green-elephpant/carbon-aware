<?php

namespace GreenElephpant\CarbonAware\DataProvider\Exception;

class ApiErrorResponseException extends \RuntimeException
{
    public function __construct(string $message = '')
    {
        parent::__construct($message);
    }
}
