<?php

namespace App\Http\Requests;

use App\Model\VerkaeuferVermerk;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVermerkRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'vknummer' => 'required_without:interessent_id|nullable|integer',
            'interessent_id' => 'required_without:vknummer|nullable|exists:interessenten,id',
            'typ' => ['required', Rule::in(VerkaeuferVermerk::typen()->keys()->all())],
            'punkte' => 'nullable|integer|min:0|max:10',
            'bemerkung' => 'nullable|string|max:1000',
            'quelle' => ['nullable', Rule::in([VerkaeuferVermerk::QUELLE_KASSE, VerkaeuferVermerk::QUELLE_KISTEN, VerkaeuferVermerk::QUELLE_VERWALTUNG])],
        ];
    }

    public function messages()
    {
        return [
            'vknummer.required_without' => 'Bitte eine Verkäufernummer angeben.',
        ];
    }
}
