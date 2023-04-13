<?php
declare(strict_types=1);

namespace Elephox\Docs;

use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\Contract\GenericOrderedEnumerable;
use Elephox\Files\Directory;
use Elephox\Files\File;
use Elephox\Files\Path;
use JsonException;
use Parsedown;

readonly class ContentFiles
{
	public function __construct(
		private Parsedown $parsedown,
		private TemplateRenderer $templateRenderer,
	)
	{
		$this->parsedown->setSafeMode(false);
	}

	public static function availableVersions(): GenericOrderedEnumerable
	{
		return (new Directory(Path::join(__DIR__, "..", "content", "v")))
			->directories()
			->select(fn (Directory $d) => $d->name())
			->orderByDescending(fn (string $name) => $name);
	}

	public static function findBestFit(string $version, string $path): ?File
	{
		$path = rtrim($path, '/');

		foreach (
			[
				$path . '.md',
				Path::join($path, 'index.md'),
				$path,
			] as $tryFile
		) {
			$contentFile = new File(Path::join(__DIR__, "..", "content", "v", $version, $tryFile));
			if ($contentFile->exists()) {
				return $contentFile;
			}
		}

		return null;
	}

	/**
	 * @throws JsonException
	 */
	public function render(File $contentFile, array &$data): string
	{
		$basePath = $contentFile->parent();

		$content = $contentFile->contents();
		$content = $this->templateRenderer->evaluate($basePath, $content, $data);
		return $this->parsedown->text($content);
	}
}
