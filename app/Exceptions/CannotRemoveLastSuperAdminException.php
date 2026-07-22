<?php

namespace App\Exceptions;

use RuntimeException;

class CannotRemoveLastSuperAdminException extends RuntimeException
{
    public function __construct(string $message = 'Son superadmin rolü kaldırılamaz.')
    {
        parent::__construct($message);
    }
}
