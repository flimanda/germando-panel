<?php

namespace App\Exceptions\Http;

use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class TwoFactorAuthRequiredException extends HttpException implements HttpExceptionInterface
{
    /**
     * TwoFactorAuthRequiredException constructor.
     */
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(Response::HTTP_BAD_REQUEST, 'Zwei-Faktor-Authentifizierung ist erforderlich, um auf diesen Endpunkt zuzugreifen.', $previous);
    }
}
