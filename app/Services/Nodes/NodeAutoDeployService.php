<?php

namespace App\Services\Nodes;

use App\Models\ApiKey;
use App\Models\Node;
use App\Services\Acl\Api\AdminAcl;
use App\Services\Api\KeyCreationService;
use Illuminate\Http\Request;

class NodeAutoDeployService
{
    /**
     * NodeAutoDeployService constructor.
     */
    public function __construct(
        private readonly KeyCreationService $keyCreationService
    ) {}

    /**
     * Generates a new API key for the logged-in user with only permission to read
     * nodes, and returns that as the deployment key for a node.
     *
     * @throws \App\Exceptions\Model\DataValidationException
     */
    public function handle(Request $request, Node $node, ?bool $docker = false): ?string
    {
        /** @var ApiKey|null $key */
        $key = ApiKey::query()
            ->where('key_type', ApiKey::TYPE_APPLICATION)
            ->whereJsonContains('permissions->' . Node::RESOURCE_NAME, AdminAcl::READ)
            ->first();

        // We couldn't find a key that exists for this user with only permission for
        // reading nodes. Go ahead and create it now.
        if (!$key) {
            $key = $this->keyCreationService->setKeyType(ApiKey::TYPE_APPLICATION)->handle([
                'memo' => 'Automatisch generierter Knoten-Bereitstellungsschlüssel.',
                'user_id' => $request->user()->id,
                'permissions' => [Node::RESOURCE_NAME => AdminAcl::READ],
            ]);
        }

        $token = $key->identifier . $key->token;

        if (!$token) {
            return null;
        }

        return sprintf(
            '%s wings configure --panel-url %s --token %s --node %d%s',
            $docker ? 'docker compose exec -it' : 'sudo',
            config('app.url'),
            $token,
            $node->id,
            $request->isSecure() ? '' : ' --allow-insecure'
        );
    }
}
