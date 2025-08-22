<?php

namespace App\Http\Requests;

use App\Models\TravelRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTravelRequestStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in([
                    TravelRequest::STATUS_APPROVED,
                    TravelRequest::STATUS_CANCELLED,
                ])
            ],
            'cancellation_reason' => 'required_if:status,cancelado|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'O status é obrigatório',
            'status.in' => 'Status inválido',
            'cancellation_reason.required_if' => 'O motivo do cancelamento é obrigatório',
        ];
    }
}