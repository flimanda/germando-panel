<?php

namespace App\Console\Commands\Node;

use App\Models\Node;
use Illuminate\Console\Command;

class NodeConfigurationCommand extends Command
{
    protected $signature = 'p:node:configuration
                            {node : Die ID oder die UUID des Netzknoten, dessen Konfiguration zurückgegeben werden soll.}
                            {--format=yaml : Das Ausgabeformat. Optionen sind "yaml" und "json".}';

    protected $description = 'Zeigt die Konfiguration für den angegebenen Netzknoten an.';

    public function handle(): int
    {
        $column = ctype_digit((string) $this->argument('node')) ? 'id' : 'uuid';

        /** @var \App\Models\Node $node */
        $node = Node::query()->where($column, $this->argument('node'))->firstOr(function () {
            $this->error(trans('commands.node_config.error_not_exist'));

            exit(1);
        });

        $format = $this->option('format');
        if (!in_array($format, ['yaml', 'yml', 'json'])) {
            $this->error(trans('commands.node_config.error_invalid_format'));

            return 1;
        }

        if ($format === 'json') {
            $this->output->write($node->getJsonConfiguration(true));
        } else {
            $this->output->write($node->getYamlConfiguration());
        }

        $this->output->newLine();

        return 0;
    }
}
