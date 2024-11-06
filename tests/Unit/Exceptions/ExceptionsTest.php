<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\GiphyApiException;
use App\Exceptions\GiphyNotFoundException;
use App\Exceptions\GiphySearchException;
use App\Exceptions\FavoriteOperationException;
use Tests\TestCase;

class ExceptionsTest extends TestCase
{
    /** @test */
    public function giphy_not_found_exception_has_correct_message(): void
    {
        $exception = GiphyNotFoundException::forId('test123');

        $this->assertEquals(
            "GIF with ID 'test123' not found in GIPHY",
            $exception->getMessage()
        );
    }

    /** @test */
    public function giphy_search_exception_includes_query_in_message(): void
    {
        $exception = GiphySearchException::queryFailed('cats', 'API error');

        $this->assertEquals(
            "Failed to search GIFs with query 'cats': API error",
            $exception->getMessage()
        );
    }

    /** @test */
    public function giphy_search_exception_handles_invalid_response(): void
    {
        $exception = GiphySearchException::invalidResponse('Missing data field');

        $this->assertEquals(
            "Invalid response from GIPHY API: Missing data field",
            $exception->getMessage()
        );
    }

    /** @test */
    public function favorite_operation_exception_handles_creation_failure(): void
    {
        $exception = FavoriteOperationException::creationFailed('Database error');

        $this->assertEquals(
            "Failed to create favorite: Database error",
            $exception->getMessage()
        );
    }

    /** @test */
    public function favorite_operation_exception_handles_not_found(): void
    {
        $exception = FavoriteOperationException::notFound(123);

        $this->assertEquals(
            "Favorite with ID '123' not found",
            $exception->getMessage()
        );
    }

    /** @test */
    public function exceptions_maintain_hierarchy(): void
    {
        $searchException = new GiphySearchException();
        $notFoundException = new GiphyNotFoundException();

        $this->assertInstanceOf(GiphyApiException::class, $searchException);
        $this->assertInstanceOf(GiphyApiException::class, $notFoundException);
    }

    /** @test */
    public function exceptions_can_be_created_with_previous_exception(): void
    {
        $previous = new \Exception('Original error');
        $exception = new GiphyApiException('Wrapped error', 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
