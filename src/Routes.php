<?php
declare(strict_types=1);

namespace Elephox\Docs;

use Elephox\Files\Path;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Http\Response;
use Elephox\Http\ResponseCode;
use Elephox\Mimey\MimeType;
use Elephox\Web\Routing\Attribute\Controller;
use Elephox\Web\Routing\Attribute\Http\Any;
use Elephox\Web\Routing\Attribute\Http\Get;

#[Controller('/')]
class Routes
{
    #[Any('regex:(?<url>.*)')]
    public function handleAny(string $url, PageRenderer $pageRenderer): ResponseBuilder
    {
        $url = ltrim($url, '/');
        $contentFile = ContentFiles::findBestFit('develop', $url);
        if ($contentFile === null) {
            return $this->handleResource("public", $url, $pageRenderer);
        }

        return $this->handleContent($contentFile, ['version' => 'develop', 'path' => $url], $pageRenderer);
    }

    #[Get('regex:(?<version>\d+\.\d+(?:\.\d+)?|develop)(?:\/(?<path>.*))?', 10)]
    public function handleGetVersionContent(Request $request, PageRenderer $pageRenderer, string $version, ?string $path = null): ResponseBuilder
    {
        $contentFile = ContentFiles::findBestFit($version, $path ?? '');
        if ($contentFile === null) {
            return $this->handleResource("public", (string)$request->getUrl(), $pageRenderer);
        }

        return $this->handleContent($contentFile, ['version' => $version, 'path' => $path ?? ''], $pageRenderer);
    }

    #[Get('regex:\/(?<url>vendor\/.*)', 10)]
    public function handleVendor(string $url, PageRenderer $pageRenderer): ResponseBuilder
    {
        return $this->handleResource("", $url, $pageRenderer);
    }

    private function handleContent(string $contentFile, array $templateValue, PageRenderer $pageRenderer): ResponseBuilder
    {
        $body = $pageRenderer->render($contentFile, $templateValue);

        return Response::build()
            ->responseCode(ResponseCode::OK)
            ->htmlBody($body)
        ;
    }

    private function handleResource(string $parent, string $url, PageRenderer $pageRenderer): ResponseBuilder
    {
        $resourcePath = Path::join(__DIR__, "..", $parent, ltrim($url, '/'));
        if (is_file($resourcePath)) {
            return $this->wrapResource($resourcePath);
        }

        return $this->notFound($url, $pageRenderer);
    }

    private function notFound(string $requestedUrl, PageRenderer $pageRenderer): ResponseBuilder
    {
        $notFoundFile = Path::join(__DIR__, "..", "content", "not-found.md");
        $body = $pageRenderer->render($notFoundFile, ['url' => $requestedUrl, 'title' => 'Not Found']);

        return Response::build()
            ->responseCode(ResponseCode::NotFound)
            ->htmlBody($body)
        ;
    }

    private function wrapResource(string $path): ResponseBuilder
    {
        return Response::build()
            ->responseCode(ResponseCode::OK)
            ->fileBody($path, MimeType::fromExtension(pathinfo($path, PATHINFO_EXTENSION)))
        ;
    }
}
