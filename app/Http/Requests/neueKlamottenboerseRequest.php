<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class neueKlamottenboerseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (Auth::user()) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'datum' => 'required|date|after:anmeldung|unique:klamottenboerse',
            'anmeldung' => 'required|date|after:anmeldungKinderhaus',
            'anmeldungKinderhaus'   => 'required|date',
            'abholung_von'  => 'required|date_format:H:i',
            'abholung_bis'  => 'required|date_format:H:i',
            'anlieferung_von'   => 'required|date_format:H:i',
            'anlieferung_bis'   => 'required|date_format:H:i',
            'maxTeile'  => 'required|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'maxTeile.required'     => 'Maximale Teileanzahl muss angegeben werden.',
            'maxTeile.min'     => 'Maximale Teileanzahl muss mindestens 1 betragen.',
        ];
    }
}
