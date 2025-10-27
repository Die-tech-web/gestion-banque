<?php

namespace App\Rules;

class ApiMessages
{
    /**
     * Get validation messages for authentication.
     *
     * @return array
     */
    public static function authValidationMessages(): array
    {
        return [
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'email doit être valide.',
            'password.required_without' => 'Le mot de passe est requis pour les administrateurs.',
            'code_authentification.required_without' => 'Le code d\'authentification est requis pour les clients.',
        ];
    }

    /**
     * Get success messages for authentication.
     *
     * @return array
     */
    public static function authSuccessMessages(): array
    {
        return [
            'login_success' => 'Connexion réussie.',
        ];
    }

    /**
     * Get error messages for authentication.
     *
     * @return array
     */
    public static function authErrorMessages(): array
    {
        return [
            'invalid_credentials' => 'Identifiants invalides.',
        ];
    }

    /**
     * Get validation messages for compte creation.
     *
     * @return array
     */
    public static function compteValidationMessages(): array
    {
        return [
            'type.required' => 'Le type de compte est obligatoire.',
            'type.in' => 'Le type de compte doit être cheque ou epargne.',
            'soldeInitial.required' => 'Le solde initial est obligatoire.',
            'soldeInitial.numeric' => 'Le solde initial doit être un nombre.',
            'soldeInitial.min' => 'Le solde initial doit être d\'au moins 10 000.',
            'devise.required' => 'La devise est obligatoire.',
            'devise.in' => 'La devise doit être FCFA, USD ou EUR.',
            'client.required' => 'Les informations du client sont obligatoires.',
            'client.array' => 'Les informations du client doivent être un tableau.',
            'client.id.integer' => 'L\'ID du client doit être un entier.',
            'client.id.exists' => 'Le client spécifié n\'existe pas.',
            'client.titulaire.required_if' => 'Le nom du titulaire est requis pour un nouveau client.',
            'client.titulaire.string' => 'Le nom du titulaire doit être une chaîne de caractères.',
            'client.titulaire.max' => 'Le nom du titulaire ne peut pas dépasser 255 caractères.',
            'client.nci.required_if' => 'Le numéro national sénégalais est requis pour un nouveau client.',
            'client.nci.unique' => 'Ce numéro national sénégalais est déjà utilisé.',
            'client.email.required_if' => 'L\'email est requis pour un nouveau client.',
            'client.email.email' => 'L\'email doit être valide.',
            'client.email.unique' => 'Cet email est déjà utilisé.',
            'client.telephone.required_if' => 'Le numéro de téléphone est requis pour un nouveau client.',
            'client.telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'client.adresse.required_if' => 'L\'adresse est requise pour un nouveau client.',
            'client.adresse.string' => 'L\'adresse doit être une chaîne de caractères.',
            'client.adresse.max' => 'L\'adresse ne peut pas dépasser 500 caractères.',
        ];
    }

    /**
     * Get success messages for compte operations.
     *
     * @return array
     */
    public static function compteSuccessMessages(): array
    {
        return [
            'compte_created' => 'Compte créé avec succès. Un email avec les informations de connexion a été envoyé au client.',
            'comptes_retrieved' => 'Comptes récupérés avec succès',
            'non_archived_comptes_retrieved' => 'Comptes non archivés récupérés avec succès',
            'archived_comptes_retrieved' => 'Comptes archivés récupérés avec succès',
            'compte_deleted' => 'Compte supprimé avec succès',
            'compte_archived' => 'Compte archivé avec succès',
            'compte_blocked' => 'Compte bloqué avec succès',
            'compte_unblocked' => 'Compte débloqué avec succès',
            'compte_details_retrieved' => 'Détails du compte récupérés avec succès',
        ];
    }

    /**
     * Get error messages for compte operations.
     *
     * @return array
     */
    public static function compteErrorMessages(): array
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
            'unauthorized' => 'Accès non autorisé.',
            'client_not_found' => 'Client non trouvé.',
            'unauthorized_compte_access' => 'Accès non autorisé à ce compte.',
            'unexpected_error' => 'Une erreur inattendue s\'est produite lors de la création du compte',
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
            'created' => 201,
            'bad_request' => 400,
            'unauthorized' => 401,
            'forbidden' => 403,
            'not_found' => 404,
            'conflict' => 409,
            'internal_server_error' => 500,
        ];
    }
}