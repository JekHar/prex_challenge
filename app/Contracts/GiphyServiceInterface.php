<?php

namespace App\Contracts;

interface GiphyServiceInterface
{
    /**
     * Search for GIFs
     *
     * @param string $query Search query
     * @param int|null $limit Results limit
     * @param int|null $offset Results offset
     * @throws GiphySearchException
     * @return array
     */
    public function search(string $query, ?int $limit = 25, ?int $offset = 0): array;

    /**
     * Get a specific GIF by ID
     *
     * @param string $id GIF ID
     * @throws GiphyNotFoundException
     * @return array
     */
    public function getById(string $id): array;
}
