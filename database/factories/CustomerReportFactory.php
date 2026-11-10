<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerReport>
 */
class CustomerReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'transaction_id' => null,
            'description' => 'أنا ما نفّذت هالعملية، الرجاء المراجعة.',
            'status' => 'new', // new, under_review, confirmed, rejected
            'handled_by' => null,
            'handled_at' => null,
        ];
    }
}