<?php

namespace Database\Factories;

use App\Enums\ShiftStatus;
use App\Models\CashierShift;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CashierShift>
 */
class CashierShiftFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'opening_cash' => 100000,
            'opened_at' => now(),
            'closing_cash' => null,
            'expected_cash' => null,
            'difference_cash' => null,
            'status' => ShiftStatus::Open,
            'closing_note' => null,
            'closed_at' => null,
        ];
    }
}
