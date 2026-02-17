<?php

namespace Tests\Feature;

use App\Models\Calculation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculationApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test retrieving all calculations.
     */
    public function test_can_retrieve_all_calculations(): void
    {
        Calculation::factory()->count(3)->create();

        $response = $this->getJson('/api/calculations');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /**
     * Test storing a new calculation.
     */
    public function test_can_store_calculation(): void
    {
        $data = [
            'expression' => '9 + 5',
            'result' => 14,
        ];

        $response = $this->postJson('/api/calculations', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['expression' => '9 + 5']);

        $this->assertDatabaseHas('calculations', $data);
    }

    /**
     * Test validation when storing calculation without expression.
     */
    public function test_validation_fails_without_expression(): void
    {
        $data = [
            'result' => 14,
        ];

        $response = $this->postJson('/api/calculations', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['expression']);
    }

    /**
     * Test validation when storing calculation without result.
     */
    public function test_validation_fails_without_result(): void
    {
        $data = [
            'expression' => '9 + 5',
        ];

        $response = $this->postJson('/api/calculations', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['result']);
    }

    /**
     * Test validation when result is not numeric.
     */
    public function test_validation_fails_with_non_numeric_result(): void
    {
        $data = [
            'expression' => '9 + 5',
            'result' => 'not-a-number',
        ];

        $response = $this->postJson('/api/calculations', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['result']);
    }

    /**
     * Test deleting a specific calculation.
     */
    public function test_can_delete_calculation(): void
    {
        $calculation = Calculation::factory()->create();

        $response = $this->deleteJson("/api/calculations/{$calculation->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Calculation deleted successfully.']);

        $this->assertDatabaseMissing('calculations', ['id' => $calculation->id]);
    }

    /**
     * Test clearing all calculations.
     */
    public function test_can_clear_all_calculations(): void
    {
        Calculation::factory()->count(5)->create();

        $response = $this->deleteJson('/api/calculations');

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'All calculations cleared successfully.']);

        $this->assertDatabaseCount('calculations', 0);
    }

    /**
     * Test calculations are returned in descending order by creation date.
     */
    public function test_calculations_returned_in_descending_order(): void
    {
        $now = now();
        $calc1 = Calculation::factory()->create([
            'expression' => '1 + 1',
            'created_at' => $now->copy()->subSeconds(2),
        ]);
        $calc2 = Calculation::factory()->create([
            'expression' => '2 + 2',
            'created_at' => $now->copy()->subSecond(),
        ]);
        $calc3 = Calculation::factory()->create([
            'expression' => '3 + 3',
            'created_at' => $now,
        ]);

        $response = $this->getJson('/api/calculations');

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertEquals('3 + 3', $data[0]['expression']);
        $this->assertEquals('2 + 2', $data[1]['expression']);
        $this->assertEquals('1 + 1', $data[2]['expression']);
    }

    /**
     * Test storing calculation with exponentiation operator.
     */
    public function test_can_store_calculation_with_exponentiation(): void
    {
        $data = [
            'expression' => '2^8',
            'result' => 256,
        ];

        $response = $this->postJson('/api/calculations', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['expression' => '2^8']);

        $this->assertDatabaseHas('calculations', $data);
    }

    /**
     * Test storing calculation with sqrt function.
     */
    public function test_can_store_calculation_with_sqrt(): void
    {
        $data = [
            'expression' => 'sqrt(16)',
            'result' => 4,
        ];

        $response = $this->postJson('/api/calculations', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['expression' => 'sqrt(16)']);

        $this->assertDatabaseHas('calculations', $data);
    }

    /**
     * Test storing calculation with complex expression (stretch goal example).
     */
    public function test_can_store_calculation_with_complex_expression(): void
    {
        $expression = 'sqrt((((9*9)/12)+(13-4))*2)^2';
        // Calculate expected result: sqrt((((81)/12)+(9))*2)^2 = sqrt((6.75+9)*2)^2 = sqrt(31.5)^2 = 31.5
        $expectedResult = 31.5;

        $data = [
            'expression' => $expression,
            'result' => $expectedResult,
        ];

        $response = $this->postJson('/api/calculations', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['expression' => $expression]);

        $this->assertDatabaseHas('calculations', [
            'expression' => $expression,
        ]);
    }

    /**
     * Test storing calculation with trigonometric functions.
     */
    public function test_can_store_calculation_with_trig_functions(): void
    {
        $data = [
            'expression' => 'sin(PI/2)',
            'result' => 1,
        ];

        $response = $this->postJson('/api/calculations', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['expression' => 'sin(PI/2)']);

        $this->assertDatabaseHas('calculations', [
            'expression' => 'sin(PI/2)',
        ]);
    }
}
