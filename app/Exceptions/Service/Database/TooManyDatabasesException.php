<?php

namespace App\Exceptions\Service\Database;

use App\Exceptions\DisplayException;

class TooManyDatabasesException extends DisplayException
{
    public function __construct()
    {
        parent::__construct('Vorgang abgebrochen: Das Erstellen einer neuen Datenbank würde diesen Server über den definierten Grenzwert hinaus treiben.');
    }
}
