<?php
declare(strict_types=1);

namespace Elephox\Docs;

use Elephox\Files\Path;
use Parsedown;

class ContentFiles
{
    public function __construct(
        private Parsedown $parsedown,
    ) {
    }

    public static function findBestFit(string $version, string $path): null|string
    {
        foreach ([
            $version,
            'main',
            'develop'
         ] as $versionDir) {
            $contentFile = Path::join(__DIR__, "..", "content", "v", $versionDir, empty($path) ? "index.md" : $path);
            if (file_exists($contentFile)) {
                return $contentFile;
            }
        }

        return null;
    }

    public function render(string $contentFilePath): string
    {
        $contentFileContent = file_get_contents($contentFilePath);

        return $this->parsedown->text($contentFileContent);
    }
}
