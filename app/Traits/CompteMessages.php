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
}
