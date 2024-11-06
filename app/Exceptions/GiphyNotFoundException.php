<?php

namespace App\Exceptions;

class GiphyNotFoundException extends GiphyApiException
{
    public static function forId(string $gifId): self
    {
        return new self("GIF with ID '$gifId' not found in GIPHY");
    }
}
