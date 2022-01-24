<?php
declare(strict_types=1);

namespace Elephox\Docs;

use Elephox\Core\Handler\Attribute\Http\Any;
use Elephox\Core\Handler\Attribute\Http\Get;
use Elephox\Core\Registrar;
use Elephox\Files\Path;
use Elephox\Http\Contract\Message;
use Elephox\Http\Contract\Request;
use Elephox\Http\Response;
use Elephox\Http\ResponseCode;
use Parsedown;
use ParsedownExtra;
use ParsedownToC;

class App implements \Elephox\Core\Contract\App
{
    use Registrar;

    public $classes = [
        ElephoxParsedown::class,
        ContentFiles::class,
        PageRenderer::class,
        TemplateRenderer::class,
    ];

    public $aliases = [
        Parsedown::class => ElephoxParsedown::class,
        ParsedownExtra::class => ElephoxParsedown::class,
        ParsedownToC::class => ElephoxParsedown::class,
    ];

    #[Get('.*', 2)]
    public function handleGetDefaultVersionContent(Request $request, PageRenderer $pageRenderer): Message
    {
        return $this->handleGetVersionContent($request, ['version' => 'develop', 'path' => ltrim((string)$request->getUrl(), '/')], $pageRenderer);
    }

    #[Get('(?<version>\d+\.\d+(?:\.\d+)?|main|develop)(?:\/(?<path>.*))?', 1)]
    public function handleGetVersionContent(Request $request, array $templateValues, PageRenderer $pageRenderer): Message
    {
        $contentFile = ContentFiles::findBestFit($templateValues['version'], $templateValues['path'] ?? '');
        if ($contentFile === null) {
            return $this->handleAny((string)$request->getUrl(), $pageRenderer);
        }

        $body = $pageRenderer->stream($contentFile, ['branch' => $templateValues['version']]);

        return Response::build()
            ->responseCode(ResponseCode::OK)
            ->body($body)
            ->get();
    }

    #[Any('(?<url>.*)', 10)]
    public function handleAny(string $url, PageRenderer $pageRenderer): Message
    {
        $notFoundFile = Path::join(__DIR__, "..", "content", "not-found.md");
        $body = $pageRenderer->stream($notFoundFile, ['url' => $url, 'title' => 'Not Found']);

        return Response::build()
            ->responseCode(ResponseCode::NotFound)
            ->body($body)
            ->get();
    }

//    #[ExceptionHandler]
//    public function handleException(ExceptionContext $exceptionContext): Message
//    {
//        return Response::build()
//            ->responseCode(ResponseCode::InternalServerError)
//            ->body(new StringStream("Unfortunately, an exception occurred: " . $exceptionContext->getException()->getMessage()))
//            ->get();
//    }
}
