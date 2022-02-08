<?php
declare(strict_types=1);

namespace Elephox\Docs;

use Elephox\Core\Context\Contract\ExceptionContext;
use Elephox\Core\Handler\Attribute\ExceptionHandler;
use Elephox\Core\Handler\Attribute\Http\Any;
use Elephox\Core\Handler\Attribute\Http\Get;
use Elephox\Core\Registrar;
use Elephox\Files\Path;
use Elephox\Http\Contract\Message;
use Elephox\Http\Contract\Request;
use Elephox\Http\Response;
use Elephox\Http\ResponseCode;
use Elephox\Stream\ResourceStream;
use Elephox\Stream\StringStream;
use Elephox\Support\MimeType;
use Highlight\Highlighter;
use Parsedown;
use ParsedownExtra;
use ParsedownToC;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as Whoops;

class App implements \Elephox\Core\Contract\App
{
    use Registrar;

    public $classes = [
        ElephoxParsedown::class,
        ContentFiles::class,
        PageRenderer::class,
        TemplateRenderer::class,
        Highlighter::class,
        Whoops::class,
    ];

    public $aliases = [
        Parsedown::class => ElephoxParsedown::class,
        ParsedownExtra::class => ElephoxParsedown::class,
        ParsedownToC::class => ElephoxParsedown::class,
    ];

    #[Any('(?<url>.*)')]
    public function handleAny(string $url, PageRenderer $pageRenderer): Message
    {
        $url = ltrim($url, '/');
        $contentFile = ContentFiles::findBestFit('develop', $url);
        if ($contentFile === null) {
            return $this->handleResource("public", $url, $pageRenderer);
        }

        return $this->handleContent($contentFile, ['version' => 'develop', $url], $pageRenderer);
    }

    #[Get('(?<version>\d+\.\d+(?:\.\d+)?|main|develop)(?:\/(?<path>.*))?', 10)]
    public function handleGetVersionContent(Request $request, array $templateValues, PageRenderer $pageRenderer): Message
    {
        $contentFile = ContentFiles::findBestFit($templateValues['version'], $templateValues['path'] ?? '');
        if ($contentFile === null) {
            return $this->handleResource("public", (string)$request->getUrl(), $pageRenderer);
        }

        return $this->handleContent($contentFile, $templateValues, $pageRenderer);
    }

    #[Get('\/(?<url>vendor\/.*)', 10)]
    public function handleVendor(string $url, PageRenderer $pageRenderer): Message
    {
        return $this->handleResource("", $url, $pageRenderer);
    }

    private function handleContent(string $contentFile, array $templateValue, PageRenderer $pageRenderer): Message
    {
        $body = $pageRenderer->stream($contentFile, $templateValue);

        return Response::build()
            ->responseCode(ResponseCode::OK)
            ->body($body)
            ->get();
    }

    private function handleResource(string $parent, string $url, PageRenderer $pageRenderer): Message
    {
        $resourcePath = Path::join(__DIR__, "..", $parent, ltrim($url, '/'));
        if (is_file($resourcePath)) {
            return $this->wrapResource($resourcePath);
        }

        return $this->notFound($url, $pageRenderer);
    }

    private function notFound(string $requestedUrl, PageRenderer $pageRenderer): Message
    {
        $notFoundFile = Path::join(__DIR__, "..", "content", "not-found.md");
        $body = $pageRenderer->stream($notFoundFile, ['url' => $requestedUrl, 'title' => 'Not Found']);

        return Response::build()
            ->responseCode(ResponseCode::NotFound)
            ->body($body)
            ->get();
    }

    private function wrapResource(string $path): Message
    {
        $resource = fopen($path, 'rb');
        $mime = match (pathinfo($path, PATHINFO_EXTENSION)) {
            'js' => MimeType::Applicationjavascript,
            'css' => MimeType::Textcss,
            default => MimeType::fromFile($path),
        };

        return Response::build()
            ->responseCode(ResponseCode::OK)
            ->contentType($mime)
            ->body(new ResourceStream($resource))
            ->get();
    }

    #[ExceptionHandler]
    public function handleException(ExceptionContext $exceptionContext, Whoops $whoops): Message
    {
        $whoops->pushHandler(new PrettyPageHandler());
        $body = $whoops->handleException($exceptionContext->getException());

        return Response::build()
            ->responseCode(ResponseCode::InternalServerError)
            ->body(new StringStream($body))
            ->get();
    }
}
