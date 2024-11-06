<?php

namespace Tests\Unit\Services;

use App\Exceptions\GiphyApiException;
use App\Exceptions\GiphyNotFoundException;
use App\Exceptions\GiphySearchException;
use App\Services\GiphyService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;

class GiphyServiceTest extends TestCase
{
    private MockHandler $mockHandler;
    private GiphyService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $client = new Client(['handler' => $handlerStack]);

        $this->service = new class($client, config('services.giphy.api_key')) extends GiphyService {
            private $client;

            public function __construct(Client $client, string $apiKey)
            {
                parent::__construct($apiKey);
                $this->client = $client;
            }

            protected function getClient(): Client
            {
                return $this->client;
            }
        };
    }

    /** @test */
    public function it_can_search_gifs_successfully(): void
    {
        $expectedResponse = [
            'data' => [
                [
                    'id' => '1',
                    'title' => 'Test GIF 1',
                    'type' => 'gif',
                    'url' => 'https://test.com/1',
                    'rating' => 'g',
                    'images' => []
                ],
                [
                    'id' => '2',
                    'title' => 'Test GIF 2',
                    'type' => 'gif',
                    'url' => 'https://test.com/2',
                    'rating' => 'g',
                    'images' => []
                ]
            ],
            'pagination' => [
                'total_count' => 2,
                'count' => 2,
                'offset' => 0
            ],
            'meta' => [
                'status' => 200,
                'msg' => 'OK',
                'response_id' => 'test'
            ]
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedResponse))
        );

        $result = $this->service->search('test query', 2, 0);
        $this->assertEquals($expectedResponse, $result);
    }

    /** @test */
    public function it_throws_exception_when_search_fails(): void
    {
        $this->mockHandler->append(
            new RequestException(
                'Error Communicating with Server',
                new Request('GET', 'test')
            )
        );

        $this->expectException(GiphySearchException::class);
        $this->expectExceptionMessage("Failed to search GIFs with query 'test query': Error Communicating with Server");

        $this->service->search('test query');
    }

    /** @test */
    public function it_throws_api_exception_for_other_failures(): void
    {
        $this->mockHandler->append(
            new ConnectException(
                'Unexpected error',
                new Request('GET', 'test')
            )
        );

        $this->expectException(GiphyApiException::class);
        $this->expectExceptionMessage('Failed to connect to Giphy API: Unexpected error');

        $this->service->search('test query');
    }

    /** @test */
    public function it_can_get_gif_by_id_successfully(): void
    {
        $expectedResponse = [
            'data' => [
                'id' => '123',
                'title' => 'Test GIF',
                'type' => 'gif',
                'url' => 'https://test.com/123',
                'rating' => 'g',
                'images' => []
            ],
            'meta' => [
                'status' => 200,
                'msg' => 'OK',
                'response_id' => 'test'
            ]
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedResponse))
        );

        $result = $this->service->getById('123');
        $this->assertEquals($expectedResponse, $result);
    }

    /** @test */
    public function it_throws_exception_when_gif_not_found(): void
    {
        $this->mockHandler->append(
            new Response(404, [], json_encode([
                'meta' => [
                    'status' => 404,
                    'msg' => 'Not Found'
                ]
            ]))
        );

        $this->expectException(GiphyNotFoundException::class);
        $this->service->getById('non_existent');
    }

    /** @test */
    public function it_throws_exception_when_search_response_is_invalid(): void
    {
        $invalidResponse = [
            'meta' => [
                'status' => 200,
                'msg' => 'OK'
            ]
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($invalidResponse))
        );

        $this->expectException(GiphySearchException::class);
        $this->expectExceptionMessage("Failed to search GIFs with query 'test query': Invalid response format from Giphy API");

        $this->service->search('test query');
    }

    /** @test */
    public function it_throws_exception_when_get_by_id_returns_empty_data(): void
    {
        $emptyResponse = [
            'data' => [],
            'meta' => [
                'status' => 200,
                'msg' => 'OK'
            ]
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($emptyResponse))
        );

        $this->expectException(GiphyNotFoundException::class);
        $this->expectExceptionMessage("GIF with ID 'test_id' not found");

        $this->service->getById('test_id');
    }

    /** @test */
    public function it_throws_api_exception_for_non_404_client_exception_in_get_by_id(): void
    {
        $this->mockHandler->append(
            new ClientException(
                'Server error',
                new Request('GET', 'test'),
                new Response(500, [], json_encode([
                    'meta' => [
                        'status' => 500,
                        'msg' => 'Internal Server Error'
                    ]
                ]))
            )
        );

        $this->expectException(GiphyApiException::class);
        $this->expectExceptionMessage('Failed to fetch GIF:');

        $this->service->getById('test_id');
    }

    /** @test */
    public function it_throws_api_exception_when_request_fails_in_get_by_id(): void
    {
        $this->mockHandler->append(
            new RequestException(
                'Network error',
                new Request('GET', 'test'),
                new Response(500)
            )
        );

        $this->expectException(GiphyApiException::class);
        $this->expectExceptionMessage('Failed to connect to Giphy API: Network error');

        $this->service->getById('test_id');
    }

    /** @test */
    public function it_creates_new_client_when_none_exists(): void
    {
        $service = new class(config('services.giphy.api_key')) extends GiphyService {
            public function getTestClient(): Client
            {
                return $this->getClient();
            }
        };

        $client = $service->getTestClient();
        $this->assertInstanceOf(Client::class, $client);

        $secondClient = $service->getTestClient();
        $this->assertSame($client, $secondClient);
    }
}
