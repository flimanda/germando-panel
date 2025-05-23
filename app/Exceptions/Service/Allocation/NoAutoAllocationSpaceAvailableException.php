<?php

namespace App\Exceptions\Service\Allocation;

use App\Exceptions\DisplayException;

class NoAutoAllocationSpaceAvailableException extends DisplayException
{
    /**
     * NoAutoAllocationSpaceAvailableException constructor.
     */
    public function __construct()
    {
        parent::__construct(
            'Zusätzliche Zuweisung nicht möglich: kein Platz mehr auf dem Knoten verfügbar.'
        );
    }
}
