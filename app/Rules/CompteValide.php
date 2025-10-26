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
            'limit.max' => 'La limite ne peut pas dépasser 100 caractere.',
            'motif.required' => 'Le motif est obligatoire.',
            'motif.string' => 'Le motif doit être une chaîne de caractères.',
            'duree.required' => 'La durée est obligatoire.',
            'duree.integer' => 'La durée doit être un entier.',
            'duree.min' => 'La durée doit être au moins 1.',
            'unite.required' => 'L\'unité est obligatoire.',
            'unite.in' => 'L\'unité doit être "jour", "jours", "semaine", "semaines", "mois", "annee" ou "annees".',
        ];
    }

    /**
     * Get success messages for operations.
     *
     * @return array
     */
    public static function successMessages(): array
    {
        return [
            'comptes_retrieved' => 'Comptes récupérés avec succès',
            'non_archived_comptes_retrieved' => 'Comptes non archivés récupérés avec succès',
            'archived_comptes_retrieved' => 'Comptes archivés récupérés avec succès',
            'compte_deleted' => 'Compte supprimé avec succès',
            'compte_archived' => 'Compte archivé avec succès',
            'compte_blocked' => 'Compte bloqué avec succès',
            'compte_unblocked' => 'Compte débloqué avec succès',
        ];
    }

    /**
     * Get error messages for operations.
     *
     * @return array
     */
    public static function errorMessages(): array
    {
        return [
            'compte_not_found' => 'Compte non trouvé.',
            'compte_already_deleted' => 'Le compte est déjà supprimé.',
            'failed_to_delete_compte' => 'Échec de la suppression du compte.',
            'failed_to_archive_compte' => 'Échec de l\'archivage du compte.',
            'compte_already_blocked' => 'Le compte est déjà bloqué.',
            'failed_to_block_compte' => 'Échec du blocage du compte.',
            'compte_not_blocked' => 'Le compte n\'est pas bloqué.',
            'failed_to_unblock_compte' => 'Échec du déblocage du compte.',
        ];
    }

    /**
     * Get HTTP status codes for operations.
     *
     * @return array
     */
    public static function httpStatusCodes(): array
    {
        return [
            'success' => 200,
            'not_found' => 404,
            'conflict' => 409,
            'internal_server_error' => 500,
        ];
    }
}
