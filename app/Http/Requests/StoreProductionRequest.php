<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('ingenieur_terrain') === true;
    }

    public function rules(): array
    {
        return [
            'puits_id' => ['required', 'integer', 'exists:puits,id'],
            'date_production' => ['required', 'date', 'before_or_equal:today'],
            'volume' => ['required', 'numeric', 'min:0'],
            'pression' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'puits_id.exists' => "Le puits sélectionné n'existe pas.",
            'date_production.before_or_equal' => "La date de production ne peut pas être future.",
        ];
    }
}
