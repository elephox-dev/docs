<?php
declare(strict_types=1);

namespace Elephox\Docs;

use Elephox\Files\Path;
use Parsedown;

class ContentFiles
{
    public function __construct(
        private Parsedown $parsedown,
        private TemplateRenderer $templateRenderer,
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

    public function render(string $contentFilePath, array &$data): string
    {
        $basePath = dirname($contentFilePath);

        $content = file_get_contents($contentFilePath);
        $content = $this->templateRenderer->evaluate($basePath, $content, $data);
        $content = $this->parsedown->text($content);

        return $content;
    }
}
