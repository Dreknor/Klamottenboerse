<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PublicInteressentenRegistrationRequest extends FormRequest
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
            'anrede' => 'required|string|max:20',
            'vorname' => 'required|string|max:255',
            'nachname' => 'required|string|max:255',
            'mail' => 'required|email|max:255|unique:interessenten,mail',
            'telefon' => 'nullable|string|max:30',
            'handy' => 'nullable|string|max:30',
            // Honeypot field: must stay empty. Real users never see or fill it.
            'website' => 'prohibited',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @return void
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            // Simple time-trap spam protection: forms submitted faster than
            // 3 seconds after being rendered are treated as automated spam.
            $renderedAt = (int) $this->input('form_rendered_at', 0);

            if ($renderedAt > 0 && (time() - $renderedAt) < 3) {
                $validator->errors()->add('mail', 'Die Registrierung konnte nicht verarbeitet werden.');
            }
        });
    }
}
