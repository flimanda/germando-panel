<?php

namespace App\Exceptions\Service\Backup;

use App\Exceptions\DisplayException;

class TooManyBackupsException extends DisplayException
{
    /**
     * TooManyBackupsException constructor.
     */
    public function __construct(int $backupLimit)
    {
        parent::__construct(
            sprintf('K  ann ein neues Backup nicht erstellen, dieser Server hat seinen Limit von %d Backups erreicht.', $backupLimit)
        );
    }
}
