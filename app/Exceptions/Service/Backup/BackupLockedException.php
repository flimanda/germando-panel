<?php

namespace App\Exceptions\Service\Backup;

use App\Exceptions\DisplayException;

class BackupLockedException extends DisplayException
{
    /**
     * TooManyBackupsException constructor.
     */
    public function __construct()
    {
        parent::__construct('Kann einen Backup nicht löschen, das als gesperrt markiert ist.');
    }
}
