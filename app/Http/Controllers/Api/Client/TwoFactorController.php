<?php

namespace App\Http\Controllers\Api\Client;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Facades\Activity;
use App\Services\Users\TwoFactorSetupService;
use App\Services\Users\ToggleTwoFactorService;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TwoFactorController extends ClientApiController
{
    /**
     * TwoFactorController constructor.
     */
    public function __construct(
        private ToggleTwoFactorService $toggleTwoFactorService,
        private TwoFactorSetupService $setupService,
        private ValidationFactory $validation
    ) {
        parent::__construct();
    }

    /**
     * Setup 2fa
     *
     * Returns two-factor token credentials that allow a user to configure
     * it on their account. If two-factor is already enabled this endpoint
     * will return a 400 error.
     *
     * @throws \App\Exceptions\Model\DataValidationException
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->user()->use_totp) {
            throw new BadRequestHttpException('Zwei-Faktor-Authentifizierung ist bereits auf diesem Konto aktiviert.');
        }

        return new JsonResponse([
            'data' => $this->setupService->handle($request->user()),
        ]);
    }

    /**
     * Enable 2fa
     *
     * Updates a user's account to have two-factor enabled.
     *
     * @throws \Throwable
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->validation->make($request->all(), [
            'code' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string'],
        ]);

        $data = $validator->validate();
        if (!password_verify($data['password'], $request->user()->password)) {
            throw new BadRequestHttpException('Das bereitgestellte Passwort war nicht gültig.');
        }

        $tokens = $this->toggleTwoFactorService->handle($request->user(), $data['code'], true);

        Activity::event('user:two-factor.create')->log();

        return new JsonResponse([
            'object' => 'recovery_tokens',
            'attributes' => [
                'tokens' => $tokens,
            ],
        ]);
    }

    /**
     * Disable 2fa
     *
     * Disables two-factor authentication on an account if the password provided
     * is valid.
     *
     * @throws \Throwable
     */
    public function delete(Request $request): JsonResponse
    {
        if (!password_verify($request->input('password') ?? '', $request->user()->password)) {
            throw new BadRequestHttpException('Das bereitgestellte Passwort war nicht gültig.');
        }

        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->update([
            'totp_authenticated_at' => Carbon::now(),
            'use_totp' => false,
        ]);

        Activity::event('user:two-factor.delete')->log();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
