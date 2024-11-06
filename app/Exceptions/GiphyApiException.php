<?php

namespace App\Exceptions;

class GiphyApiException extends \Exception
{
    public static function searchFailed(string $details = ''): self
    {
        return new self(
            "Failed to search GIFs" . ($details ? ": $details" : '')
        );
    }
}

