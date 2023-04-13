<?php

namespace Elephox\Docs;

use Elephox\Files\File;
use JsonException;

readonly class PageRenderer
{
	public function __construct(
		private ContentFiles $contentFileRenderer,
		private TemplateRenderer $templateRenderer,
	)
	{
	}

	/**
	 * @throws JsonException
	 */
	public function render(File $contentFile, array $data, string $template = "default"): string
	{
		$content = $this->contentFileRenderer->render($contentFile, $data);
		$data = array_merge($data, ['content' => $content]);

		return $this->templateRenderer->render($template, $data);
	}
}
