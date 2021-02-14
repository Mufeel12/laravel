<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'billing_country' => 'string|max:100',
            'billing_city' => 'string|max:255',
            'billing_zip' => 'string|max:20',
            'billing_address' => 'string|max:255',
            'billing_address_line_2' => 'string|max:255',
            'phone' => 'string|max:255',
            'payment_method' => 'string|max:10',
            'payment_token' => 'string',
            'card_number' => 'string',
            'exp_month' => 'string',
            'exp_year' => 'string'
        ];
    }
}
