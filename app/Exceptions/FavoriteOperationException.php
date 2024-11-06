<?php

namespace App\Exceptions;

class FavoriteOperationException extends \Exception
{
    public static function creationFailed(string $details = ''): self
    {
        return new self(
            "Failed to create favorite" . ($details ? ": $details" : '')
        );
    }

    public static function notFound(int $id): self
    {
        return new self("Favorite with ID '$id' not found");
    }
}
