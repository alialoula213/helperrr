<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
        $id = $this->user;

        switch($this->method())
        {
            case 'GET':
            case 'DELETE':
            {
                return [];
            }
            case 'POST':
            {
                return [
                    'wallet' => 'required|string|unique:users,wallet',
                    'ref_id' => 'nullable|integer|exists:users,id',
                    'allow_withdrawal' => 'required|boolean',
                    'status' => 'required|alpha_dash',
                    'banned_reason' => 'nullable|string',
                    'comments' => 'nullable|string',
                ];
            }
            case 'PUT':
            case 'PATCH':
            {
                return [
                    'wallet' => 'required|string|unique:users,wallet,'.$id->id,
                    'ref_id' => 'nullable|integer|exists:users,id',
                    'allow_withdrawal' => 'required|boolean',
                    'status' => 'required|alpha_dash',
                    'banned_reason' => 'nullable|string',
                    'comments' => 'nullable|string',
                ];
            }
            default:
                break;
        }
    }
}
