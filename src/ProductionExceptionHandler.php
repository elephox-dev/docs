<?php
declare(strict_types=1);

namespace Elephox\Docs;

use Elephox\DI\Contract\ServiceCollection;
use Elephox\Files\Path;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Http\ResponseCode;
use Elephox\Web\Middleware\DefaultExceptionHandler;

class ProductionExceptionHandler extends DefaultExceptionHandler
{
	public function __construct(
		private readonly ServiceCollection $services,
	) {
	}

	protected function setResponseBody(ResponseBuilder $response): ResponseBuilder
	{
		$internalServerErrorFile = Path::join(APP_ROOT, "content", "internal-server-error.md");
		$body = $this->services
			->requireService(PageRenderer::class)
			->render($internalServerErrorFile, ['title' => 'Internal Server Error'])
		;

		return $response
			->responseCode(ResponseCode::InternalServerError)
			->htmlBody($body)
		;
	}
}
