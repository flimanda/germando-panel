<?php

namespace App\Exceptions\Solutions;

use Spatie\Ignition\Contracts\Solution;

class ManifestDoesNotExistSolution implements Solution
{
    public function getSolutionTitle(): string
    {
        return "Die manifest.json-Datei wurde noch nicht generiert";
    }

    public function getSolutionDescription(): string
    {
        return 'Führen Sie yarn run build:production aus, um die Frontend-Dateien zu erstellen.';
    }

    public function getDocumentationLinks(): array
    {
        return [
            'Docs' => 'https://github.com/pelican/panel/blob/master/package.json',
        ];
    }
}
