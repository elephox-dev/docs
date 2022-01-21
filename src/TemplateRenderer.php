<?php

namespace Elephox\Docs;

use Elephox\Files\Path;
use RuntimeException;

class TemplateRenderer
{
    public function loadData(string $path): array
    {
        if (!file_exists($path)) {
            throw new RuntimeException('File not found: ' . $path);
        }

        return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    }

    public function render(string $name, array $data): string
    {
        $templateFile = Path::join(__DIR__, '..', 'templates', $name . '.html');
        if (!file_exists($templateFile)) {
            throw new RuntimeException('Template not found: ' . $templateFile);
        }

        return $this->renderFile($templateFile, $data);
    }

    public function renderFile(string $templatePath, array $data): string
    {
        $template = file_get_contents($templatePath);

        preg_match_all('/{{\s*(.*?)\s*}}/', $template, $matches);
        foreach ($matches[0] as $index => $wrapper) {
            $content = $matches[1][$index];

            if (str_starts_with($content, '$')) {
                $varName = substr($content, 1);

                if (array_key_exists($varName, $data)) {
                    $template = str_replace($wrapper, $data[$varName], $template);
                } else {
                    $template = str_replace($wrapper, '', $template);
                }
            } else if (str_starts_with($content, 'include ')) {
                $includePath = substr($content, 8);
                if (file_exists($includePath)) {
                    require_once $includePath;
                }
            } else if (str_starts_with($content, 'import ')) {
                $importedPath = Path::join(dirname($templatePath), substr($content, 7));

                $template = str_replace($wrapper, $this->renderFile($importedPath, $data), $template);
            } else if (str_starts_with($content, 'load ')) {
                $loadedPath = Path::join(dirname($templatePath), substr($content, 5));

                array_push($data, ...$this->loadData($loadedPath));
            } else {
                throw new RuntimeException('Unknown template directive: ' . $content);
            }
        }

        return $template;
    }
}
