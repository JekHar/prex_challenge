<?php

namespace App\DTO;

class GiphyGifDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $url,
        public readonly array $images
    ) {}

    public static function fromApiResponse(array $data): self
    {
        return new self(
            id: $data['id'],
            title: $data['title'],
            url: $data['url'],
            images: $data['images']
        );
    }
}
