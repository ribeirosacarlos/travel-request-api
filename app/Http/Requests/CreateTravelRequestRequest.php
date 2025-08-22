<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTravelRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'requester_name' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'departure_date' => 'required|date|after:today',
            'return_date' => 'required|date|after:departure_date',
        ];
    }

    public function messages(): array
    {
        return [
            'requester_name.required' => 'O nome do solicitante é obrigatório',
            'destination.required' => 'O destino é obrigatório',
            'departure_date.required' => 'A data de ida é obrigatória',
            'departure_date.after' => 'A data de ida deve ser posterior a hoje',
            'return_date.required' => 'A data de volta é obrigatória',
            'return_date.after' => 'A data de volta deve ser posterior à data de ida',
        ];
    }
}