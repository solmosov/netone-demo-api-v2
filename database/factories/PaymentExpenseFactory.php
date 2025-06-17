<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\Status;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PaymentExpenseFactory extends Factory
{
    protected $model = Payment::class;
    
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $expense = Expense::inRandomOrder();
        $expenseName = $expense->value('name');
        
        return [
            'user_id' => 2,
            'status_id' => Status::select('id')->where('code','paymentExpense')->firstOrFail()->id,
            'comment' => "{$expenseName} - xarajat to'lov",
            'paymentable_id' => $expense->value('id'),
            'paymentable_type' => "App\Models\Expense",
            'total_amount' => fake()->numberBetween(100000, 10000000),
        ];
    }
}
