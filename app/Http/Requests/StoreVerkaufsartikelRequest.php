<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVerkaufsartikelRequest extends FormRequest
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
            'beschreibung' => 'required|string|max:255',
            'kategorie' => 'nullable|string|max:100',
            'groesse' => 'nullable|string|max:50',
            'preis' => 'required|numeric|min:0.5|max:9999',
        ];
    }
}
