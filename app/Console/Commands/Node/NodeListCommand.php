<?php

namespace App\Console\Commands\Node;

use App\Models\Node;
use Illuminate\Console\Command;

class NodeListCommand extends Command
{
    protected $signature = 'p:node:list {--format=text : Das Ausgabeformat: "text" oder "json".}';

    public function handle(): int
    {
        $nodes = Node::query()->get()->map(function (Node $node) {
            return [
                'id' => $node->id,
                'uuid' => $node->uuid,
                'name' => $node->name,
                'host' => $node->getConnectionAddress(),
            ];
        });

        if ($this->option('format') === 'json') {
            $this->output->write($nodes->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->table(['ID', 'UUID', 'Name', 'Host'], $nodes->toArray());
        }

        $this->output->newLine();

        return 0;
    }
}
