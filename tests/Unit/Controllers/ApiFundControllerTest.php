<?php

namespace Tests\Unit\Controllers;

use App\Models\Fund;
use Database\Seeders\FundManagerSeeder;
use Database\Seeders\FundSeeder;
use Illuminate\Http\Response;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiFundControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(FundManagerSeeder::class);
        $this->seed(FundSeeder::class);
    }

    public function testFundCanBeCreated()
    {
        $data = [
            'fund' => 'Test Fund',
            'manager' => 'Test Manager',
            'year' => '2023',
            'aliases' => ['alias1', 'alias2'],
        ];

        $response = $this->postJson('/api/funds', $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(['success' => true]);
    }

    public function testFundsCanBeListed()
    {
        $response = $this->get('/api/funds');
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'success',
                'data',
            ]);

        $responseData = $response->json();
        $this->assertArrayHasKey('data', $responseData);
    }

    public function testFundCanBeUpdated()
    {
        $fund = Fund::inRandomOrder()->first();

        $newData = [
            'fund' => 'Updated Fund',
            'manager' => 'Manager Test 1',
            'year' => 2025,
            'aliases' => ['Alias4', 'Alias5'],
        ];

        $response = $this->put('/api/funds/' . $fund->id, $newData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'success' => true,
                'message' => 'Fund has been updated successfully.',
                'data' => $newData,
            ]);
    }
}
