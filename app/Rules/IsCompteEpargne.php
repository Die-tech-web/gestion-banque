<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Compte;

class IsCompteEpargne implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $compte = Compte::find($value);

        if (!$compte) {
            $fail('Le compte spécifié n\'existe pas.');
            return;
        }

        if ($compte->type !== 'epargne') {
            $fail('Un compte chèque ne peut pas être bloqué.');
        }
    }
}
