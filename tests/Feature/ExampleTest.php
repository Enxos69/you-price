<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $this->admin = User::factory()->create(['role' => '1']);
        $this->seedTestData();
    }

    protected function tearDown(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        parent::tearDown();
    }

    private function seedTestData(): void
    {
        DB::table('products')->insert([
            'id'             => 'TESTPROD01',
            'cruise_line_id' => 'CL01',
            'ship_id'        => 'SH01',
            'area_id'        => 'AREA01',
            'port_from_id'   => 'P01',
            'port_to_id'     => 'P02',
            'cruise_name'    => 'Test Cruise Mediterraneo',
            'is_package'     => false,
            'sea'            => true,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        DB::table('departures')->insert([
            'id'         => 'TESTDEP001',
            'product_id' => 'TESTPROD01',
            'dep_date'   => now()->addMonths(2)->format('Y-m-d'),
            'arr_date'   => now()->addMonths(2)->addDays(7)->format('Y-m-d'),
            'duration'   => 7,
            'min_price'  => 850.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Prezzo 35 giorni fa
        DB::table('price_history')->insert([
            'departure_id'  => 'TESTDEP001',
            'category_code' => 'IS',
            'price'         => 1000.00,
            'currency'      => 'EUR',
            'recorded_at'   => now()->subDays(35),
            'source'        => 'catalog',
        ]);

        // Prezzo attuale
        DB::table('price_history')->insert([
            'departure_id'  => 'TESTDEP001',
            'category_code' => 'IS',
            'price'         => 850.00,
            'currency'      => 'EUR',
            'recorded_at'   => now(),
            'source'        => 'catalog',
        ]);
    }

    public function test_admin_can_view_price_history_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/price-history');
        $response->assertStatus(200);
    }

    public function test_non_admin_is_redirected_from_price_history(): void
    {
        $user = User::factory()->create(['role' => '2']);
        $response = $this->actingAs($user)->get('/admin/price-history');
        $response->assertRedirect('/home');
    }

    public function test_top_variations_returns_correct_structure(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/admin/price-history/top-variations');
        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'insufficient_data']);
    }

    public function test_top_variations_detects_price_change(): void
    {
        $response = $this->actingAs($this->admin)
                         ->getJson('/api/admin/price-history/top-variations?days=30');
        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertNotEmpty($data, 'top-variations deve restituire almeno un risultato');
        $this->assertEquals('TESTDEP001', $data[0]['departure_id']);
        $this->assertEquals(-150.0, (float) $data[0]['delta_eur']);
        $this->assertFalse($response->json('insufficient_data'));
    }
}
