<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\SenegalPhoneRule;
use Illuminate\Validation\Rule;

class UpdateCompteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // L'autorisation est gérée dans le controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $compteId = $this->route('id');

        return [
            'titulaire' => 'sometimes|string|max:255',
            'informationsClient' => 'sometimes|array',
            'informationsClient.telephone' => [
                'sometimes',
                'string',
                new SenegalPhoneRule(),
                Rule::unique('clients', 'telephone')->ignore($this->route('id'), 'id')
            ],
            'informationsClient.email' => [
                'sometimes',
                'email',
                Rule::unique('clients', 'email')->ignore($this->route('id'), 'id')
            ],
            'informationsClient.password' => 'sometimes|string|min:8',
            'informationsClient.nci' => 'sometimes|string|max:20',
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
            'titulaire.string' => 'Le titulaire doit être une chaîne de caractères.',
            'titulaire.max' => 'Le titulaire ne peut pas dépasser 255 caractères.',
            'informationsClient.array' => 'Les informations client doivent être un tableau.',
            'informationsClient.telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'informationsClient.email.email' => 'L\'email doit être une adresse email valide.',
            'informationsClient.email.unique' => 'Cet email est déjà utilisé.',
            'informationsClient.password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'informationsClient.nci.string' => 'Le NCI doit être une chaîne de caractères.',
            'informationsClient.nci.max' => 'Le NCI ne peut pas dépasser 20 caractères.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->all();

            // Vérifier qu'au moins un champ est fourni
            $hasTitulaire = isset($data['titulaire']);
            $hasClientInfo = isset($data['informationsClient']) && is_array($data['informationsClient']) && !empty($data['informationsClient']);

            if (!$hasTitulaire && !$hasClientInfo) {
                $validator->errors()->add('general', 'Au moins un champ doit être fourni pour la mise à jour.');
            }
        });
    }
}