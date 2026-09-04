<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReserviereNummerRequest extends FormRequest
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
            'interessent'   => 'integer|min:1|required',
            'NummernID'   => 'integer|required',
        ];
    }
}
