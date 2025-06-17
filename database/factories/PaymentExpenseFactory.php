<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\Status;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

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
        $totalAmount = fake()->numberBetween(100000, 10000000);
        $date = fake()->dateTimeBetween('-2 years', 'now');
        
        return [
            'user_id' => 2,
            'status_id' => Status::select('id')->where('code','paymentExpense')->firstOrFail()->id,
            'comment' => "{$expenseName} - xarajat to'lov",
            'paymentable_id' => $expense->value('id'),
            'paymentable_type' => "App\Models\Expense",
            'total_amount' => $totalAmount,
            'created_at' => $date,
            'updated_at' => $date
        ];
    }

    /**
     * Configure the factory to create payment_wallet record after creating payment.
     */
    public function configure()
    {
        return $this->afterCreating(function (Payment $payment) {
            DB::table('payment_wallet')->insert([
                'payment_id' => $payment->id,
                'wallet_id' => 1, // Default wallet ID
                'amount' => $payment->total_amount,
                'rate_amount' => $payment->total_amount,
                'sum_price' => $payment->total_amount,
                'created_at' => $payment->created_at,
                'updated_at' => $payment->updated_at,
            ]);
        });
    }
}
