<?php

namespace App\Exceptions\Http\Server;

use App\Exceptions\DisplayException;

class FileSizeTooLargeException extends DisplayException
{
    /**
     * FileSizeTooLargeException constructor.
     */
    public function __construct()
    {
        parent::__construct('Die Datei, die Sie zu öffnen versuchen, ist zu groß, um sie mit dem Datei-Editor anzuzeigen.');
    }
}
