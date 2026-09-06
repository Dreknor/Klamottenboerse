<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKisteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user() && $this->user()->verwaltung == 1;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'vknummer_id' => 'required|exists:vknummern,id',
            'anzahl' => 'required|integer|min:1|max:50',
            'bemerkung' => 'nullable|string|max:1000',
        ];
    }
}
