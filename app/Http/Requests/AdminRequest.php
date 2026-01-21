<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminRequest extends FormRequest
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
        $id = $this->admin;

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
                    'name' => 'required|string',
                    'username' => 'required|alpha_dash|unique:admins,username',
                    'email' => 'required|email|unique:admins,email',
                    'password' => 'required|string|min:6|confirmed',
                    'roles' => 'required'
                ];
            }
            case 'PUT':
            case 'PATCH':
            {
                return [
                    'name' => 'required|min:3',
                    'username' => 'required|alpha_dash|unique:admins,username,'.$id->id,
                    'email' => 'required|email|unique:admins,email,'.$id->id,
                    'password' => 'nullable|string|min:6|confirmed',
                    'roles' => 'required'
                ];
            }
            default:
                break;
        }
    }
}
