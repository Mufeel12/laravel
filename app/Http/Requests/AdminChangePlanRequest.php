<?php


namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Laravel\Spark\Spark;

class AdminChangePlanRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $plans = Spark::teamPlans()->all();
        $cycle_options = config('app.cycle_options');
        $payment_options = config('app.payment_options');

        return [
            'newPlan' => [
                'string',
                'required',
                Rule::in(collect($plans)->pluck('id')->toArray())
            ],
            'cycleOption' => [
                'string',
                'required',
                Rule::in(array_keys($cycle_options)),
            ],
            'paymentOption' => [
                'string',
                'required',
                Rule::in(array_keys($payment_options)),
            ],
            'notify' => 'boolean:required',
            'mailText' => 'string',
            'keyWord' => [
                'string',
                'required',
                Rule::in([ 'DOWNGRADE', 'UPGRADE' ])
            ],
            'date' => 'date'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();

        throw new HttpResponseException(
            response()->json(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}