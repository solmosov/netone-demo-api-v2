<?php

namespace App\Http\Requests;

use App\Exceptions\InvalidDataException;
use App\Models\Polka;
use App\Models\ProductionProcess;
use App\Models\ProductStock;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class FinishProductionProcessRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'polka_id' => 'required|integer|exists:polkas,id',
            'total_amount' => 'required|numeric|min:0'
        ];
    }

    public function messages(): array
    {
        return [
            'total_amount.min' => "Ishlab chiqarilgan jami miqdor 0 dan kam bo'lmasin"
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $processId = $this->route('id');
            $polkaId = $this->input('polka_id');

            $process = ProductionProcess::with(['productionRecipe'])->findOrFail($processId);

            $accessExisting = DB::table('depart_user')
                ->where('user_id', Auth::id())
                ->where('depart_id', $process->depart_id)
                ->exists();
            if (!$accessExisting) {
                throw new InvalidDataException("Ishlab chiqarish jarayoni mavjud emas",404);
            }

            $polka = Polka::findOrfail($polkaId);

            if($polka->owner_id !== $process->depart_id){
                throw new InvalidDataException('Bu polkaga saqlash uchun sizda ruxsat mavjud emas', 403);
            }

            $stock = ProductStock::where('polka_id', $polkaId)
                ->where('product_id', $process->productionRecipe->out_product_id)
                ->first();
            if (!$stock) {
                throw new InvalidDataException("Bu polga ishlab chiqariladigan mahsulotga tegishli emas");
            }

            if($stock->amount_type_id !== $process->productionRecipe->out_amount_type_id){
                throw new InvalidDataException("Zaxira polkaning o'lchov birligi ishlab chiqariladigan mahsulotga mos emas");
            }
        });
    }
}
