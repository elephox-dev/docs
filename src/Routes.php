<?php
declare(strict_types=1);

namespace Elephox\Docs;

use Elephox\Configuration\Contract\Environment;
use Elephox\Files\File;
use Elephox\Files\Path;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Http\Response;
use Elephox\Http\ResponseCode;
use Elephox\Web\Routing\Attribute\Controller;
use Elephox\Web\Routing\Attribute\Http\Get;
use JsonException;

#[Controller]
readonly class Routes
{
	public function __construct(
		private Environment $environment,
		private PageRenderer $pageRenderer,
	) {}

//	/**
//	 * @throws JsonException
//	 */
//	#[Any('{url:*}')]
//	public function handleAny(string $url, PageRenderer $pageRenderer): ResponseBuilder
//	{
//		$url = ltrim($url, '/');
//		$contentFile = ContentFiles::findBestFit('develop', $url);
//		if ($contentFile === null) {
//			return $this->handleResource("public", $url, $pageRenderer);
//		}
//
//		return $this->handleContent($contentFile, ['version' => 'develop', 'branch' => 'develop', 'path' => $url], $pageRenderer);
//	}
	/**
	 * @throws JsonException
	 */
	#[Get]
	public function handleIndex(): ResponseBuilder
	{
		return $this->handleContent(ContentFiles::findBestFit("develop", "index"), ['version' => "develop", 'branch' => 'develop', 'path' => '']);
	}

	/**
	 * @throws JsonException
	 */
	#[Get('v/{version}/?{path:*}')]
	public function handleGetVersionContent(Request $request, string $version, ?string $path = null): ResponseBuilder
	{
		if (!ContentFiles::availableVersions()->contains($version)) {
			return $this->notFound((string)$request->getUrl());
		}

		$contentFile = ContentFiles::findBestFit($version, $path ?? '');
		if ($contentFile === null) {
			return $this->notFound((string)$request->getUrl());
		}

		return $this->handleContent($contentFile, ['version' => $version, 'branch' => $version === 'develop' ? 'develop' : ('release/' . $version), 'path' => $path ?? '']);
	}

	/**
	 * @throws JsonException
	 */
	#[Get('vendor/{url:*}')]
	public function handleVendor(string $url): ResponseBuilder
	{
		return $this->handleResource("vendor", $url);
	}

	/**
	 * @throws JsonException
	 */
	private function handleContent(File $contentFile, array $templateValue): ResponseBuilder
	{
		$body = $this->pageRenderer->render($contentFile, $templateValue);

		return Response::build()
			->responseCode(ResponseCode::OK)
			->htmlBody($body);
	}

	/**
	 * @throws JsonException
	 */
	private function handleResource(string $parent, string $url): ResponseBuilder
	{
		$resource = new File(Path::join($this->environment->root()->path(), $parent, ltrim($url, '/')));
		if ($resource->exists()) {
			return Response::build()->ok()->fileBody($resource);
		}

		return $this->notFound($url);
	}

	/**
	 * @throws JsonException
	 */
	private function notFound(string $requestedUrl): ResponseBuilder
	{
		$notFoundFile = new File(Path::join(__DIR__, "..", "content", "not-found.md"));
		$body = $this->pageRenderer->render($notFoundFile, ['url' => $requestedUrl, 'title' => 'Not Found', 'version' => 'develop']);

		return Response::build()->notFound()->htmlBody($body);
	}
}
