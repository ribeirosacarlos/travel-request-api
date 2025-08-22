<?php

namespace Database\Factories;

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TravelRequestFactory extends Factory
{
    public function definition(): array
    {
        $departureDate = fake()->dateTimeBetween('+1 week', '+2 months');
        $returnDate = fake()->dateTimeBetween($departureDate, '+3 months');

        return [
            'user_id' => User::factory(),
            'requester_name' => fake()->name(),
            'destination' => fake()->city() . ', ' . fake()->country(),
            'departure_date' => $departureDate,
            'return_date' => $returnDate,
            'status' => fake()->randomElement([
                TravelRequest::STATUS_REQUESTED,
                TravelRequest::STATUS_APPROVED,
                TravelRequest::STATUS_CANCELLED,
            ]),
        ];
    }

    public function requested()
    {
        return $this->state(fn (array $attributes) => [
            'status' => TravelRequest::STATUS_REQUESTED,
        ]);
    }

    public function approved()
    {
        return $this->state(fn (array $attributes) => [
            'status' => TravelRequest::STATUS_APPROVED,
            'approved_by' => User::factory()->admin(),
            'approved_at' => now(),
        ]);
    }

    public function cancelled()
    {
        return $this->state(fn (array $attributes) => [
            'status' => TravelRequest::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => fake()->sentence(),
        ]);
    }
}