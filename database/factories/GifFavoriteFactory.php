<?php

namespace Database\Factories;

use App\Models\GifFavorite;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GifFavoriteFactory extends Factory
{
    protected $model = GifFavorite::class;

    public function definition(): array
    {
        return [
            'gif_id' => fake()->uuid(),
            'alias' => fake()->words(3, true),
            'user_id' => User::factory(),
            'created_at' => fake()->dateTimeBetween('-1 year'),
            'updated_at' => fn (array $attributes) => $attributes['created_at']
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function withGifId(string $gifId): static
    {
        return $this->state(fn (array $attributes) => [
            'gif_id' => $gifId,
        ]);
    }
}
