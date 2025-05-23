<?php

namespace App\Exceptions\Service\Database;

use App\Exceptions\DisplayException;

class NoSuitableDatabaseHostException extends DisplayException
{
    /**
     * NoSuitableDatabaseHostException constructor.
     */
    public function __construct()
    {
        parent::__construct('Es wurde kein passender Datenbankhost gefunden, der die Anforderungen für diesen Server erfüllt.');
    }
}
