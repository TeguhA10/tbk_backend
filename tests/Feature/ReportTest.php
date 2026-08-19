<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\DatabaseSeeder;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_profit_loss_matches_excel(): void
    {
        // Seed the database
        $this->seed(DatabaseSeeder::class);

        // Fetch report
        $response = $this->getJson('/api/reports/profit-loss');

        $response->assertStatus(200);

        $data = $response->json();
        
        // Print the JSON nicely for debugging
        echo "\nREPORT DATA FROM BACKEND API:\n";
        echo json_encode($data, JSON_PRETTY_PRINT) . "\n";

        // Expected totals from Excel:
        // Total Income: Jan = 17,500,000, Feb = 18,000,000, Mar = 15,500,000
        // Total Expense: Jan = 850,000, Feb = 4,050,000, Mar = 4,900,000
        // Net Income: Jan = 16,650,000, Feb = 13,950,000, Mar = 10,600,000

        $this->assertEquals(17500000, $data['total_income']['2022-01'] ?? 0);
        $this->assertEquals(18000000, $data['total_income']['2022-02'] ?? 0);
        $this->assertEquals(15500000, $data['total_income']['2022-03'] ?? 0);

        $this->assertEquals(850000, $data['total_expense']['2022-01'] ?? 0);
        $this->assertEquals(4050000, $data['total_expense']['2022-02'] ?? 0);
        $this->assertEquals(4900000, $data['total_expense']['2022-03'] ?? 0);

        $this->assertEquals(16650000, $data['net_income']['2022-01'] ?? 0);
        $this->assertEquals(13950000, $data['net_income']['2022-02'] ?? 0);
        $this->assertEquals(10600000, $data['net_income']['2022-03'] ?? 0);

        echo "\nSUCCESS: ALL MONTHLY TOTALS MATCH EXCEL VALUES PERFECTLY!\n";
    }
}
