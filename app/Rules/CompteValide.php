<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CompteValide implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Logique de validation personnalisée pour les comptes
        // Par exemple, vérifier si le compte existe ou est valide
        // Ici, on peut ajouter des règles spécifiques

        // Pour l'instant, on passe toujours la validation
        // Vous pouvez implémenter la logique selon vos besoins
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Le compte n\'est pas valide.';
    }

    /**
     * Get custom validation messages for specific rules.
     *
     * @return array
     */
    public static function messages(): array
    {
        return [
            'type.in' => 'Le type de compte doit être "courant", "epargne" ou "cheque".',
            'statut.in' => 'Le statut doit être "actif", "ferme", "suspendu" ou "bloque".',
            'search.string' => 'Le terme de recherche doit être une chaîne de caractères.',
            'sort.in' => 'Le tri doit être par "dateCreation", "titulaire" ou "solde".',
            'order.in' => 'L\'ordre de tri doit être "asc" ou "desc".',
            'limit.integer' => 'La limite doit être un entier.',
            'limit.min' => 'La limite doit être au moins 1.',
            'limit.max' => 'La limite ne peut pas dépasser 100.',
        ];
    }
}
