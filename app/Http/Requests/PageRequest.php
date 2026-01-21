<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PageRequest extends FormRequest
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
        $id = $this->page;

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
                    'slug' => 'required|alpha_dash|unique:pages,slug',
                    'content' => 'required|string',
                    'status' => 'required|alpha_dash',
                    'type' => 'required|alpha_dash',
                    'meta_keywords' => 'nullable|string',
                    'meta_description' => 'nullable|string',
                ];
            }
            case 'PUT':
            case 'PATCH':
            {
                return [
                    'name' => 'required|min:3',
                    'slug' => 'required|alpha_dash|unique:pages,slug,'.$id->id,
                    'content' => 'required|string',
                    'status' => 'required|alpha_dash',
                    'type' => 'required|alpha_dash',
                    'meta_keywords' => 'nullable|string',
                    'meta_description' => 'nullable|string',
                ];
            }
            default:
                break;
        }
    }
}
