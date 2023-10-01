<?php

namespace Tests\Unit\Database;

use App\Models\Company;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;  // This will ensure the database changes are rolled back after the test

    /** @test */
    public function a_company_can_be_created_in_the_database()
    {
        $companyData = [
            'name' => 'Test Company',
        ];

        $company = Company::create($companyData);

        $this->assertInstanceOf(Company::class, $company);
        $this->assertEquals('Test Company', $company->name);
        $this->assertNotNull($company->created_at);
        $this->assertNotNull($company->updated_at);
    }
}
