<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepositRequest extends FormRequest
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
            'power' => 'required|numeric',
            'amount' => 'required|numeric',
            'paid_amount' => 'nullable|numeric|required_if:status,paid|gte:amount|required_if:status,paid',
            'status' => 'required|alpha_dash',
            'cancel_reason' => 'nullable|string|required_if:status,canceled',
            'tx_id' => 'nullable|string|required_if:status,paid',
            'response' => 'nullable|string',
            'comments' => 'nullable|string',
        ];
    }
}
