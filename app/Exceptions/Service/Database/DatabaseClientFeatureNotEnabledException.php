<?php

namespace App\Exceptions\Service\Database;

use App\Exceptions\PanelException;

class DatabaseClientFeatureNotEnabledException extends PanelException
{
    public function __construct()
    {
        parent::__construct('Client-Datenbank-Erstellung ist in diesem Panel nicht aktiviert.');
    }
}
