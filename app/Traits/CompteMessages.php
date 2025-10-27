<?php

namespace App\Traits;

use App\Rules\CompteValide;

trait CompteMessages
{
    protected function comptesRetrievedSuccessfully(): string
    {
        return CompteValide::successMessages()['comptes_retrieved'];
    }

    protected function nonArchivedComptesRetrievedSuccessfully(): string
    {
        return CompteValide::successMessages()['non_archived_comptes_retrieved'];
    }

    protected function archivedComptesRetrievedSuccessfully(): string
    {
        return CompteValide::successMessages()['archived_comptes_retrieved'];
    }

    protected function compteDeletedSuccessfully(): string
    {
        return CompteValide::successMessages()['compte_deleted'];
    }

    protected function failedToDeleteCompte(): string
    {
        return CompteValide::errorMessages()['failed_to_delete_compte'];
    }

    protected function compteAlreadyDeleted(): string
    {
        return CompteValide::errorMessages()['compte_already_deleted'];
    }

    protected function compteNotFound(): string
    {
        return CompteValide::errorMessages()['compte_not_found'];
    }

    protected function compteArchivedSuccessfully(): string
    {
        return CompteValide::successMessages()['compte_archived'];
    }

    protected function failedToArchiveCompte(): string
    {
        return CompteValide::errorMessages()['failed_to_archive_compte'];
    }

    protected function compteBlockedSuccessfully(): string
    {
        return CompteValide::successMessages()['compte_blocked'];
    }

    protected function failedToBlockCompte(): string
    {
        return CompteValide::errorMessages()['failed_to_block_compte'];
    }

    protected function compteAlreadyBlocked(): string
    {
        return CompteValide::errorMessages()['compte_already_blocked'];
    }

    protected function compteDetailsRetrievedSuccessfully(): string
    {
        return CompteValide::successMessages()['compte_details_retrieved'];
    }

    protected function unauthorizedCompteAccess(): string
    {
        return CompteValide::errorMessages()['unauthorized_compte_access'];
    }

    protected function compteUnblockedSuccessfully(): string
    {
        return CompteValide::successMessages()['compte_unblocked'];
    }

    protected function failedToUnblockCompte(): string
    {
        return CompteValide::errorMessages()['failed_to_unblock_compte'];
    }

    protected function compteNotBlocked(): string
    {
        return CompteValide::errorMessages()['compte_not_blocked'];
    }
}
