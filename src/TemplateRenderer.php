<?php

namespace Elephox\Docs;

use ArrayIterator;
use Elephox\Collection\Iterator\WhileIterator;
use Elephox\Files\Path;
use Elephox\Http\Contract\Request;
use Elephox\Web\Routing\MatchedUrlParametersMap;
use MultipleIterator;
use NoRewindIterator;
use RuntimeException;

class TemplateRenderer
{
    public function __construct(
        private readonly Request $request,
        private readonly MatchedUrlParametersMap $urlParameters,
    ) {
    }

    /**
     * @throws \JsonException
     */
    public function loadData(string $path): array
    {
        if (!file_exists($path)) {
            throw new RuntimeException('File not found: ' . $path);
        }

        return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * @throws \JsonException
     */
    public function render(string $name, array &$data): string
    {
        $templateFile = Path::join(__DIR__, '..', 'templates', $name . '.html');
        if (!file_exists($templateFile)) {
            throw new RuntimeException('Template not found: ' . $templateFile);
        }

        return $this->renderFile($templateFile, $data);
    }

    /**
     * @throws \JsonException
     */
    public function renderFile(string $templatePath, array &$data): string
    {
        $template = file_get_contents($templatePath);
        $basePath = dirname($templatePath);

        return $this->evaluate($basePath, $template, $data);
    }

    /**
     * @throws \JsonException
     */
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

    /**
     * @throws \JsonException
     */
    private function evaluateInternal(string $basePath, string $template, array &$data, array &$loopVars): string
    {
        $lines = preg_split("/(?<=\n)(?!$)/", $template);
        $lineIterator = new ArrayIterator($lines);
        foreach ($lineIterator as $line) {
            preg_match_all('/{\?\s*(.*?)\s*}/', $line, $matches);

            $directiveIterator = new MultipleIterator(MultipleIterator::MIT_NEED_ALL | MultipleIterator::MIT_KEYS_ASSOC);
            $directiveIterator->attachIterator(new ArrayIterator($matches[0]), 'wrapper');
            $directiveIterator->attachIterator(new ArrayIterator($matches[1]), 'directive');

            foreach ($directiveIterator as $token) {
                $wrapper = (string)$token['wrapper'];
                $templateDirective = (string)$token['directive'];

                if (str_starts_with($templateDirective, 'foreach ')) {
                    $loopVars['nestLevel']++;
                    [, $dataPath, , $loopVarName] = explode(" ", $templateDirective, 4);
                    $dataPath = trim($dataPath, '$ ');
                    $loopVarName = trim($loopVarName, '$ ');
                    $loopVarValue = $this->getDotPathValue($dataPath, $data, $loopVars);

                    $loopVars['loopVarNames'][] = $loopVarName;
                    $loopVars['loopVarValues'][] = $loopVarValue;

                    $loopedLines = [];
                    // TODO: handle parsing of nested loops
                    $endforeachMatch = null;
                    $loopIterator = new NoRewindIterator(new WhileIterator($lineIterator, function (string $line) use (&$endforeachMatch): bool { return !preg_match('/{\?\s*endforeach\s*}/', $line, $endforeachMatch); }));

                    $loopWrapper = $loopIterator->current();
                    $loopIterator->next();

                    foreach ($loopIterator as $loopLine) {
                        $loopWrapper .= $loopLine;
                        $loopedLines[] = $loopLine;
                    }

                    $loopWrapper .= $lineIterator->current();

                    $replacement = "";
                    $loopVarName = $loopVars['loopVarNames'][$loopVars['nestLevel'] - 1];
                    foreach ($loopVars['loopVarValues'][$loopVars['nestLevel'] - 1] as $var) {
                        $data[$loopVarName] = $var;

                        $replacement .= $this->evaluateInternal($basePath, implode("\n", $loopedLines), $data, $loopVars) . PHP_EOL;

                        unset($data[$loopVarName]);
                    }

                    $loopVars['nestLevel']--;
                    array_pop($loopVars['loopVarNames']);
                    array_pop($loopVars['loopVarValues']);

                    $template = $this->replaceFirstMatch($template, $loopWrapper, $replacement);
                } else {
                    $replacement = $this->evaluateStatementDirectives($templateDirective, $basePath, $data, $loopVars);
                    $template = $this->replaceFirstMatch($template, $wrapper, $replacement);
                }
            }
        }

        return $template;
    }

    private function replaceFirstMatch(string $haystack, string $needle, mixed $replacement): string
    {
        return (string)substr_replace($haystack, (string)$replacement, (int)strpos($haystack, $needle), strlen($needle));
    }

    /**
     * @throws \JsonException
     */
    private function evaluateStatementDirectives(string $templateDirective, string $basePath, array &$data, array $loopVars): mixed
    {
        if (str_starts_with($templateDirective, '$')) {
            $varPath = substr($templateDirective, 1);
            return $this->getDotPathValue($varPath, $data, $loopVars);
        }

        if (str_starts_with($templateDirective, 'include')) {
            $includePath = substr($templateDirective, 8);
            if (file_exists($includePath)) {
                require_once $includePath;
            }

            return '';
        }

        if (str_starts_with($templateDirective, 'import')) {
            $expression = substr($templateDirective, 7);
            if (str_starts_with($expression, '(')) {
                $expression = $this->evaluateExpression(trim($expression), $basePath, $data, $loopVars);
            }

            $importedPath = Path::join($basePath, $expression);

            return $this->renderFile($importedPath, $data);
        }

        if (str_starts_with($templateDirective, 'load')) {
            $loadedPath = Path::join($basePath, substr($templateDirective, 5));

            foreach ($this->loadData($loadedPath) as $key => $value) {
                $data[$key] = $value;
            }

            return '';
        }

        if (str_starts_with($templateDirective, 'qualify')) {
            $urlToQualify = substr($templateDirective, 8);
            if ($this->urlParameters->has('version')) {
                $urlToQualify = '/' . $this->urlParameters->get('version') . '/' . ltrim($urlToQualify, '/');
            }

            return $urlToQualify;
        }

        if (str_starts_with($templateDirective, 'active')) {
            $activePath = substr($templateDirective, 7);
            $activePath = $this->evaluateStatementDirectives("qualify $activePath", $basePath, $data, $loopVars);
            $currentPath = $this->request->getUrl()->path;

            return $activePath === $currentPath;
        }

        if (str_starts_with($templateDirective, 'set')) {
            $varAndValue = substr($templateDirective, 4);
            [$varName, $value] = explode('=', $varAndValue, 2);
            $varName = trim($varName, '$ ');
            $value = $this->evaluateExpression(trim($value), $basePath, $data, $loopVars);

            $this->setDotPathValue($varName, $value, $data);

            return '';
        }

        if (str_starts_with($templateDirective, '(')) {
            return $this->evaluateExpression($templateDirective, $basePath, $data, $loopVars);
        }

        return null;
    }

    /**
     * @noinspection TypeUnsafeComparisonInspection
     * @throws \JsonException
     */
    private function evaluateExpression(string $expression, string $basePath, array &$data, array $loopVars): mixed
    {
        preg_match('/^\(\s*(.*)\s+(\+|-|\*|\/|%|==|!=|\|\||&&|\?|\.)\s+(.*)\s*\)$/', $expression, $matches);
        if (empty($matches)) {
            $value = $this->evaluateStatementDirectives(trim($expression, '()'), $basePath, $data, $loopVars);
            return $value ?? $this->evaluateExpressionPart($expression, $basePath, $data, $loopVars);
        }

        $left = $this->evaluateExpressionPart($matches[1], $basePath, $data, $loopVars);
        $operator = match ($matches[2]) {
            '+' => static fn ($left, $right) => $left + $right,
            '-' => static fn ($left, $right) => $left - $right,
            '*' => static fn ($left, $right) => $left * $right,
            '/' => static fn ($left, $right) => $left / $right,
            '%' => static fn ($left, $right) => $left % $right,
            '.' => static fn ($left, $right) => $left . $right,
            '==' => static fn ($left, $right) => $left == $right,
            '!=' => static fn ($left, $right) => $left != $right,
            '||' => static fn ($left, $right) => $left || $right,
            '&&' => static fn ($left, $right) => $left && $right,
            '?' => static fn ($left, $right) => $left ? $right : null,
            default => throw new RuntimeException('Unknown operator: ' . $matches[2]),
        };
        $right = $this->evaluateExpressionPart($matches[3], $basePath, $data, $loopVars);

        return $operator($left, $right);
    }

    /**
     * @throws \JsonException
     */
    private function evaluateExpressionPart(string $part, string $basePath, array $data, array $loopVars): mixed
    {
        if (str_starts_with($part, '(')) {
            return $this->evaluateExpression($part, $basePath, $data, $loopVars);
        }

        if (str_starts_with($part, '$')) {
            $varPath = substr($part, 1);
            return $this->getDotPathValue($varPath, $data, $loopVars);
        }

        if (is_numeric($part)) {
            return (int)$part;
        }

        if (str_starts_with($part, '"')) {
            return substr($part, 1, -1);
        }

        if (str_starts_with($part, "'")) {
            return substr($part, 1, -1);
        }

        throw new RuntimeException('Unknown expression part: ' . $part);
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
