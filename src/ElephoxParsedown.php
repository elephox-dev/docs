<?php

namespace Elephox\Docs;

use Exception;
use Highlight\Highlighter;
use ParsedownToC;

class ElephoxParsedown extends ParsedownToC
{
	/**
	 * @throws Exception
	 */
	public function __construct(private readonly Highlighter $highlighter)
	{
		parent::__construct();
	}

	protected function blockFencedCodeComplete($Block): array
	{
		if (!array_key_exists('element', $Block) ||
			!array_key_exists('text', $Block['element']) ||
			!array_key_exists('text', $Block['element']['text']) ||
			!array_key_exists('attributes', $Block['element']['text']) ||
			!array_key_exists('class', $Block['element']['text']['attributes'])
		) {
			return $Block;
		}

		$language = $Block['element']['text']['attributes']['class'];
		if (str_starts_with($language, 'language-')) {
			$language = substr($language, 9);
		}

		$text = $Block['element']['text']['text'];

		try {
			$highlighted = $this->highlighter->highlight($language, $text);
			$Block['element']['text']['rawHtml'] = $highlighted->value;
			unset($Block['element']['text']['text']);
		} catch (Exception) {
			// ignore
		}

		return $Block;
	}
}
