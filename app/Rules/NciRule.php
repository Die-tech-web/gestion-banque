<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NciRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Vérifier que c'est une chaîne de caractères
        if (!is_string($value)) {
            $fail('Le :attribute doit être une chaîne de caractères.');
            return;
        }

        // Vérifier la longueur (13 caractères)
        if (strlen($value) !== 13) {
            $fail('Le :attribute doit contenir exactement 13 caractères.');
            return;
        }

        // Vérifier que tous les caractères sont des chiffres
        if (!is_numeric($value)) {
            $fail('Le :attribute doit contenir uniquement des chiffres.');
            return;
        }

        // Vérifier le genre (premier chiffre : 1 pour garçon, 2 pour fille)
        $genre = substr($value, 0, 1);
        if ($genre !== '1' && $genre !== '2') {
            $fail('Le premier chiffre du :attribute doit être 1 pour un garçon ou 2 pour une fille.');
            return;
        }
    }
}
