<?php

namespace App\Traits;

trait CompteMessages
{
    protected function comptesRetrievedSuccessfully(): string
    {
        return 'Comptes récupérés avec succès';
    }

    protected function nonArchivedComptesRetrievedSuccessfully(): string
    {
        return 'Comptes non archivés récupérés avec succès';
    }

    protected function archivedComptesRetrievedSuccessfully(): string
    {
        return 'Comptes archivés récupérés avec succès';
    }
}
