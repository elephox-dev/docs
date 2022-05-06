<?php
declare(strict_types=1);

namespace Elephox\Docs;

use Elephox\Files\Path;
use JsonException;
use Parsedown;

class ContentFiles
{
	public function __construct(
		private readonly Parsedown $parsedown,
		private readonly TemplateRenderer $templateRenderer,
	)
	{
		$this->parsedown->setSafeMode(false);
	}

	public static function findBestFit(string $version, string $path): null|string
	{
		$path = rtrim($path, '/');

		foreach (
			[
				$version,
				'main',
				'develop'
			] as $versionDir
		) {
			foreach (
				[
					$path,
					$path . '.md',
					Path::join($path, 'index.md')
				] as $tryFile
			) {
				$contentFile = Path::join(__DIR__, "..", "content", "v", $versionDir, $tryFile);
				if (is_file($contentFile)) {
					return $contentFile;
				}
			}
		}

		return null;
	}

	/**
	 * @throws JsonException
	 */
	public function render(string $contentFilePath, array &$data): string
	{
		$basePath = dirname($contentFilePath);

		$content = file_get_contents($contentFilePath);
		$content = $this->templateRenderer->evaluate($basePath, $content, $data);
		return $this->parsedown->text($content);
	}
}
