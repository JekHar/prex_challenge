<?php

namespace App\Services;

use App\Contracts\GiphyServiceInterface;
use App\Exceptions\GiphyApiException;
use App\Exceptions\GiphyNotFoundException;
use App\Exceptions\GiphySearchException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

class GiphyService implements GiphyServiceInterface
{
    private ?Client $client = null;

    public function __construct(
        private readonly string $apiKey
    ) {}

    protected function getClient(): Client
    {
        if ($this->client === null) {
            $this->client = new Client([
                'base_uri' => 'https://api.giphy.com',
                'timeout' => 5.0,
            ]);
        }

        return $this->client;
    }

    public function search(string $query, ?int $limit = 25, ?int $offset = 0): array
    {
        try {
            $response = $this->getClient()->get('/v1/gifs/search', [
                'query' => [
                    'api_key' => $this->apiKey,
                    'q' => $query,
                    'limit' => $limit,
                    'offset' => $offset,
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (!isset($result['data'])) {
                throw GiphySearchException::queryFailed($query, 'Invalid response format from Giphy API');
            }

            return $result;
        } catch (ClientException $e) {
            throw GiphySearchException::queryFailed($query, $e->getMessage());
        } catch (RequestException $e) {
            throw GiphySearchException::queryFailed($query, $e->getMessage());
        } catch (GuzzleException $e) {
            throw new GiphyApiException("Failed to connect to Giphy API: " . $e->getMessage());
        }
    }

    public function getById(string $id): array
    {
        try {
            $response = $this->getClient()->get("/v1/gifs/{$id}", [
                'query' => [
                    'api_key' => $this->apiKey,
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (!isset($result['data']) || empty($result['data'])) {
                throw GiphyNotFoundException::forId($id);
            }

            return $result;
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                throw GiphyNotFoundException::forId($id);
            }
            throw new GiphyApiException("Failed to fetch GIF: " . $e->getMessage());
        } catch (RequestException $e) {
            throw new GiphyApiException("Failed to connect to Giphy API: " . $e->getMessage());
        } catch (GuzzleException $e) {
            throw new GiphyApiException("Failed to connect to Giphy API: " . $e->getMessage());
        }
    }
}
