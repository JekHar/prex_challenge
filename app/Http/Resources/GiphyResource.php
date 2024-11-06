<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GiphyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'url' => $this->resource->url,
            'preview' => [
                'url' => $this->resource->images->preview->url ?? null,
                'width' => $this->resource->images->preview->width ?? null,
                'height' => $this->resource->images->preview->height ?? null,
            ],
            'original' => [
                'url' => $this->resource->images->original->url ?? null,
                'width' => $this->resource->images->original->width ?? null,
                'height' => $this->resource->images->original->height ?? null,
            ]
        ];
    }
}
