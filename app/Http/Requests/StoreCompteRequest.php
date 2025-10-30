<?php

namespace App\Http\Requests;

use App\Rules\NciRule;
use App\Rules\SenegalPhoneRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCompteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // La vérification d'authentification et de rôle est maintenant gérée par les middlewares
        // Cette méthode retourne true car l'autorisation est déléguée aux middlewares
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|in:cheque,epargne',
            'soldeInitial' => 'required|numeric|min:10000',
            'devise' => 'required|in:FCFA,USD,EUR',
            'client' => 'required|array',
            'client.id' => 'nullable|string|exists:clients,id', // Changé en string pour UUID
            'client.titulaire' => 'required_if:client.id,null|string|max:255',
            'client.nci' => ['required_if:client.id,null', new NciRule()],
            'client.email' => 'required_if:client.id,null|email',
            'client.telephone' => ['required_if:client.id,null', new SenegalPhoneRule()],
            'client.adresse' => 'required_if:client.id,null|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return \App\Rules\ApiMessages::compteValidationMessages();
    }
}
