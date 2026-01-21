<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminProfileRequest extends FormRequest
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
        $user = $this->user();
        return [
            'username' => 'required|alpha_dash|min:4|unique:admins,username,'.$user->id,
            'name' => 'required|min:4',
            'email' => 'nullable|confirmed|email|unique:admins,email,'.$user->id,
            'password' => 'nullable|confirmed|min:6',
            'avatar' => 'nullable|image|mimes:jpeg,png|max:500',
        ];
    }
}
