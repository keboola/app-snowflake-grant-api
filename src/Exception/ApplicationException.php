<?php
declare(strict_types=1);

namespace Keboola\SnowflakeGrantAPI\Exception;

use Throwable;

class ApplicationException extends \Exception
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, 500, $previous);
    }
}