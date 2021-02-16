<?php

namespace tsvetkov\tinkoff_open_api\Exception;

use Throwable;

/**
 * Class WrongEnvException
 * @package tsvetkov\tinkoff_open_api\Exception
 */
class WrongEnvException extends BaseException
{
    /**
     * WrongEnvException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "Only sandbox method", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}