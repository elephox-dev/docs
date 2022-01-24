<?php

namespace Elephox\Docs;

use Elephox\Core\Handler\Contract\HandledRequest;
use Elephox\Files\Path;
use RuntimeException;

class TemplateRenderer
{
    public function __construct(private HandledRequest $request)
    {
    }

    public function loadData(string $path): array
    {
        if (!file_exists($path)) {
            throw new RuntimeException('File not found: ' . $path);
        }

        return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    }

    public function render(string $name, array &$data): string
    {
        $templateFile = Path::join(__DIR__, '..', 'templates', $name . '.html');
        if (!file_exists($templateFile)) {
            throw new RuntimeException('Template not found: ' . $templateFile);
        }

        return $this->renderFile($templateFile, $data);
    }

    public function renderFile(string $templatePath, array &$data): string
    {
        $template = file_get_contents($templatePath);
        $basePath = dirname($templatePath);

        return $this->evaluate($basePath, $template, $data);
    }

    public function evaluate(string $basePath, string $template, array &$data): string
    {
        $loopVars = [
            'nestLevel' => 0,
            'loopVarNames' => [],
            'loopVarValues' => [],
        ];

        $result = $this->evaluateInternal($basePath, $template, $data, $loopVars);

        if ($loopVars['nestLevel'] !== 0) {
            throw new RuntimeException("Not all loops were closed!");
        }

        return $result;
    }

    private function evaluateInternal(string $basePath, string $template, array &$data, array &$loopVars): string
    {
        preg_match_all('/{\?\s*(.*?)\s*}/', $template, $matches);
        foreach ($matches[0] as $index => $wrapper) {
            /** @var string $wrapper */

            $templateDirective = $matches[1][$index];

            if (str_starts_with($templateDirective, '$')) {
                $varPath = substr($templateDirective, 1);
                $value = $this->getDotPathValue($varPath, $data, $loopVars);

                $template = str_replace($wrapper, $value ?? '', $template);
            } else if (str_starts_with($templateDirective, 'include')) {
                $includePath = substr($templateDirective, 8);
                if (file_exists($includePath)) {
                    require_once $includePath;
                }
                $template = str_replace($wrapper, '', $template);
            } else if (str_starts_with($templateDirective, 'import')) {
                $importedPath = Path::join($basePath, substr($templateDirective, 7));

                $template = str_replace($wrapper, $this->renderFile($importedPath, $data), $template);
            } else if (str_starts_with($templateDirective, 'load')) {
                $loadedPath = Path::join($basePath, substr($templateDirective, 5));

                foreach ($this->loadData($loadedPath) as $key => $value) {
                    $data[$key] = $value;
                }

                $template = str_replace($wrapper, '', $template);
            } else if (str_starts_with($templateDirective, 'qualify')) {
                $urlToQualify = substr($templateDirective, 8);
                $matchedTemplate = $this->request->getMatchedTemplate();
                if ($matchedTemplate->has('version')) {
                    $urlToQualify = '/' . $matchedTemplate->get('version') . '/' . ltrim($urlToQualify, '/');
                }

                $template = str_replace($wrapper, $urlToQualify, $template);
            } else if (str_starts_with($templateDirective, 'set')) {
                $varAndValue = substr($templateDirective, 4);
                [$varName, $value] = explode('=', $varAndValue, 2);
                $varName = trim($varName, '$ ');
                $value = trim($value, "\"' ");

                $this->setDotPathValue($varName, $value, $data);

                $template = str_replace($wrapper, '', $template);
            } else if (str_starts_with($templateDirective, 'foreach ')) {
                $loopVars['nestLevel']++;
                [, $dataPath, , $loopVarName ] = explode(" ", $templateDirective, 4);
                $dataPath = trim($dataPath, '$ ');
                $loopVarName = trim($loopVarName, '$ ');
                $loopVarValue = $this->getDotPathValue($dataPath, $data, $loopVars);

                $loopVars['loopVarNames'][] = $loopVarName;
                $loopVars['loopVarValues'][] = $loopVarValue;

                // TODO: get template between foreach and endforeach and loop over value

                $template = str_replace($wrapper, '', $template);
            } else if ($templateDirective === 'endforeach') {
                $loopVars['nestLevel']--;
                array_pop($loopVars['loopVarNames']);
                array_pop($loopVars['loopVarValues']);

                $template = str_replace($wrapper, '', $template);
            } else {
                throw new RuntimeException('Unknown template directive: ' . $templateDirective);
            }
        }

        return $template;
    }

    private function getDotPathValue(string $path, array $data, array $loopVars): mixed
    {
        $keys = explode('.', $path);
        $nestedData = $data;
        $key = array_shift($keys);

        do {
            $nestedData = $nestedData[$key] ?? null;

            $key = array_shift($keys);
        } while (is_array($nestedData) && $key !== null);

        if ($key === null) {
            return $nestedData;
        }

        $keys = explode('.', $path);
        $loopVarName = array_shift($keys);
        $loopVarValueIndex = array_search($loopVarName, $loopVars['loopVarNames'], true);
        if (false !== $loopVarValueIndex) {
            $loopVarValues = $loopVars['loopVarValues'][$loopVarValueIndex];
            $loopVarValue = array_shift($loopVarValues);

            if (!empty($keys)) {
                return $this->getDotPathValue(implode('.', $keys), $loopVarValue, []);
            }

            return $loopVarValue;
        }

        return null;
    }

    private function setDotPathValue(string $path, mixed $value, array &$data): void
    {
        $nestedData = &$data;
        $keys = explode('.', $path);

        foreach ($keys as $key) {
            if (array_key_exists($key, $nestedData) && is_array($nestedData[$key])) {
                $nestedData = &$nestedData[$key];
            } else {
                $nestedData[$key] = $value;
            }
        }
    }
}
