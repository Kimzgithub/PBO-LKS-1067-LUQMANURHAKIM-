<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Exception dasar untuk error yang punya kode status HTTP.
 * Router akan menangkapnya dan mengubahnya menjadi response JSON.
 */
class HttpException extends RuntimeException
{
    private int $statusCode;

    public function __construct(string $message, int $statusCode = 400)
    {
        parent::__construct($message, $statusCode);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
