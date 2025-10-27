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
        // Vérifier que l'utilisateur est authentifié et est un admin
        return auth()->check() && auth()->user() && auth()->user()->admin()->exists();
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
            'client.id' => 'nullable|integer|exists:clients,id',
            'client.titulaire' => 'required_if:client.id,null|string|max:255',
            'client.nci' => ['required_if:client.id,null', new NciRule(), 'unique:clients,nci'],
            'client.email' => 'required_if:client.id,null|email|unique:users,email',
            'client.telephone' => ['required_if:client.id,null', new SenegalPhoneRule(), 'unique:clients,telephone'],
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
