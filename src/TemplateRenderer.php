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
        $basePath = dirname($templatePath);

        if (array_key_exists('content', $data)) {
            $data['content'] = $this->insertData($basePath, $data['content'], $data);
        }

        return $this->insertData($basePath, $template, $data);
    }

    public function insertData(string $basePath, string $template, array $data): string
    {
        preg_match_all('/{\s*(.*?)\s*}/', $template, $matches);
        foreach ($matches[0] as $index => $wrapper) {
            $templateDirective = $matches[1][$index];

            if (str_starts_with($templateDirective, '$')) {
                $varName = substr($templateDirective, 1);

                if (array_key_exists($varName, $data)) {
                    /** @var string $template */
                    $template = str_replace($wrapper, $data[$varName], $template);
                } else {
                    /** @var string $template */
                    $template = str_replace($wrapper, '', $template);
                }
            } else if (str_starts_with($templateDirective, 'include ')) {
                $includePath = substr($templateDirective, 8);
                if (file_exists($includePath)) {
                    require_once $includePath;
                }
            } else if (str_starts_with($templateDirective, 'import ')) {
                $importedPath = Path::join($basePath, substr($templateDirective, 7));

                $template = str_replace($wrapper, $this->renderFile($importedPath, $data), $template);
            } else if (str_starts_with($templateDirective, 'load ')) {
                $loadedPath = Path::join($basePath, substr($templateDirective, 5));

                array_push($data, ...$this->loadData($loadedPath));
            } else {
                throw new RuntimeException('Unknown template directive: ' . $templateDirective);
            }
        }

        return $template;
    }
}
