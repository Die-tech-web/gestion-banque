<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompteListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'type' => ['nullable', 'string', 'in:courant,epargne,cheque'],
            'statut' => ['nullable', 'string', 'in:actif,ferme,suspendu,bloque'],
            'search' => ['nullable', 'string'],
            'sort' => ['nullable', 'string', 'in:dateCreation,titulaire,solde'],
            'order' => ['nullable', 'string', 'in:asc,desc'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
