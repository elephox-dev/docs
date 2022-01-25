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
        $this->parsedown->setSafeMode(false);
    }

    public static function findBestFit(string $version, string $path): null|string
    {
        $path = rtrim($path, '/');

        foreach ([
            $version,
            'main',
            'develop'
         ] as $versionDir) {
            foreach ([
                $path,
                $path . '.md',
                Path::join($path, 'index.md')
             ] as $tryFile) {
                $contentFile = Path::join(__DIR__, "..", "content", "v", $versionDir, $tryFile);
                if (is_file($contentFile)) {
                    return $contentFile;
                }
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
