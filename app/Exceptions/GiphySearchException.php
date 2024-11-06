<?php

namespace App\Exceptions;

class GiphySearchException extends GiphyApiException
{
    public static function queryFailed(string $query, string $details = ''): self
    {
        return new self(
            "Failed to search GIFs with query '$query'" . ($details ? ": $details" : '')
        );
    }

    public static function invalidResponse(string $details = ''): self
    {
        return new self(
            "Invalid response from GIPHY API" . ($details ? ": $details" : '')
        );
    }
}
