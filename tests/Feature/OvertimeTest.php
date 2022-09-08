<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OvertimeTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    // public function test_example()
    // {
    //     $response = $this->get('/');

    //     $response->assertStatus(200);
    // }

    public function test_setting()
    {
        // $this->withoutExceptionHandling();
        $response = $this->patch('/api/setting', [
            'key' => 'overtime_method',
            'value' => 1
        ]);

        $response->assertStatus(200);
    }

    public function test_create_employee()
    {
        $response = $this->post('/api/employee', [
            'name' => 'Chandrika Eka Kurniawan',
            'salary' => 2500000
        ]);

        $response->AssertStatus(201);
    }

    public function test_create_overtime()
    {
        $response = $this->post('/api/overtime', [
            'employee_id' => 1,
            'date' => '2022-09-16',
            'time_started' => '18:00',
            'time_ended' => '20:00'
        ]);

        $response->assertStatus(201);
    }

    public function test_overtime_calculate()
    {
        $response = $this->get('/api/overtime/calculate', [
            'date' => '2022-09',
        ]);

        $response->assertStatus(200);
    }
}
