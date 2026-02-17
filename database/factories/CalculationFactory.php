<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Calculation>
 */
class CalculationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $operand1 = $this->faker->numberBetween(1, 100);
        $operand2 = $this->faker->numberBetween(1, 100);
        $operators = ['+', '-', '*', '/'];
        $operator = $this->faker->randomElement($operators);

        $expression = "{$operand1} {$operator} {$operand2}";
        $result = match ($operator) {
            '+' => $operand1 + $operand2,
            '-' => $operand1 - $operand2,
            '*' => $operand1 * $operand2,
            '/' => $operand2 != 0 ? $operand1 / $operand2 : 0,
        };

        return [
            'expression' => $expression,
            'result' => $result,
        ];
    }
}
