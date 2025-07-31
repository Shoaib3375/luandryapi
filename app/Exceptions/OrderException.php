<?php

namespace App\Exceptions;

use Exception;

class OrderException extends Exception
{
    public static function cannotUpdateNonPendingOrder(): self
    {
        return new self('Only pending orders can be updated.');
    }

    public static function cannotCancelNonPendingOrder(): self
    {
        return new self('Only pending orders can be cancelled.');
    }

    public static function unauthorized(): self
    {
        return new self('You are not authorized to perform this action.');
    }

    public static function invalidStatusTransition(): self
    {
        return new self('Invalid status transition.');
    }
}