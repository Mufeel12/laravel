<?php


namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Laravel\Spark\Spark;

class RecalcSubscriptionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $plans = Spark::teamPlans()->all();
        $cycle_options = config('app.cycle_options');

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