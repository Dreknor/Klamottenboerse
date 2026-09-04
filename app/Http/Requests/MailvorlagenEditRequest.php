<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MailvorlagenEditRequest extends FormRequest
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
            'betreff'   => 'required|string',
            'text'      => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'text.required' => 'Es muss ein Nachrichtentext angegeben werden.',
            'betreff.required'  => 'Ein Betreff muss angegeben werden.',
        ];
    }
}
