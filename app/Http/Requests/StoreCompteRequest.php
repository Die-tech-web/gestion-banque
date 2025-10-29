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
        $isAuthenticated = auth()->check();
        $user = auth()->user();
        $isAdmin = false;

        if ($isAuthenticated && $user) {
            $isAdmin = $user->admin()->exists();
            \Log::info('StoreCompteRequest authorize: User ID: ' . $user->id . ', Is Authenticated: ' . ($isAuthenticated ? 'true' : 'false') . ', Is Admin: ' . ($isAdmin ? 'true' : 'false'));
        } else {
            \Log::info('StoreCompteRequest authorize: Not authenticated or user is null.');
        }

        return $isAuthenticated && $user && $isAdmin;
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
