<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // L'autorisation sera gérée par le middleware IsAdmin
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'compte_id' => 'required|uuid|exists:comptes,id',
            'type' => ['required', Rule::in(['DEPOT', 'RETRAIT'])],
            'montant' => 'required|numeric|min:0.01',
            'description' => 'required|string|min:5|max:255',
            'validation_automatique' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'compte_id.required' => 'L\'ID du compte est obligatoire.',
            'compte_id.uuid' => 'L\'ID du compte doit être un UUID valide.',
            'compte_id.exists' => 'Le compte spécifié n\'existe pas.',
            'type.required' => 'Le type de transaction est obligatoire.',
            'type.in' => 'Le type de transaction doit être DEPOT ou RETRAIT.',
            'montant.required' => 'Le montant est obligatoire.',
            'montant.numeric' => 'Le montant doit être un nombre.',
            'montant.min' => 'Le montant doit être supérieur à 0.',
            'description.required' => 'La description est obligatoire.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'description.min' => 'La description doit contenir au moins 5 caractères.',
            'description.max' => 'La description ne peut pas dépasser 255 caractères.',
            'validation_automatique.boolean' => 'Le champ validation_automatique doit être un booléen.',
        ];
    }
}
