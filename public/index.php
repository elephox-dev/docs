<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/vendor/autoload.php';

use Elephox\Builder\Whoops\AddsWhoopsMiddleware;
use Elephox\Configuration\Contract\Configuration;
use Elephox\Docs\ContentFiles;
use Elephox\Docs\ElephoxParsedown;
use Elephox\Docs\PageRenderer;
use Elephox\Docs\ProductionExceptionHandler;
use Elephox\Docs\Routes;
use Elephox\Docs\TemplateRenderer;
use Elephox\Support\Contract\ExceptionHandler;
use Elephox\Web\Routing\RequestRouter;
use Elephox\Web\WebApplicationBuilder;
use Highlight\Highlighter;
use ricardoboss\WebhookTweeter\API\BirdElephantTwitterAPI;
use ricardoboss\WebhookTweeter\Simple\SimpleWebhookTweeterRenderer;
use ricardoboss\WebhookTweeter\Simple\SimpleWebhookTweeterTemplateLocator;
use ricardoboss\WebhookTweeter\WebhookTweeterConfig;
use ricardoboss\WebhookTweeter\WebhookTweeterHandler;
use ricardoboss\WebhookTweeter\WebhookTweeterRenderer;
use ricardoboss\WebhookTweeter\WebhookTweeterTemplateLocator;
use ricardoboss\WebhookTweeter\WebhookTweeterTwitterAPI;

class WebBuilder extends WebApplicationBuilder {
	use AddsWhoopsMiddleware;
}

$builder = WebBuilder::create();
if ($builder->environment->development) {
	$builder->addWhoops();
} else {
	$handler = new ProductionExceptionHandler($builder->services);
	$builder->services->addSingleton(ExceptionHandler::class, implementation: $handler);
	$builder->pipeline->exceptionHandler($handler);
}

$builder->setRequestRouterEndpoint();
$builder->service(RequestRouter::class)->loadFromClass(Routes::class);

$builder->services->addSingleton(ContentFiles::class);
$builder->services->addSingleton(PageRenderer::class);
$builder->services->addSingleton(TemplateRenderer::class);
$builder->services->addSingleton(Highlighter::class, implementationFactory: function (): Highlighter {
	$h = new Highlighter(false);

	Highlighter::registerLanguage('php', APP_ROOT . '/vendor/scrivo/highlight.php/Highlight/languages/php.json');
	Highlighter::registerLanguage('bash', APP_ROOT . '/vendor/scrivo/highlight.php/Highlight/languages/bash.json');

	return $h;
});
$builder->services->addSingleton(Parsedown::class, ElephoxParsedown::class);

$builder->services->addSingleton(WebhookTweeterConfig::class, implementationFactory: function (Configuration $config): WebhookTweeterConfig {
	return new WebhookTweeterConfig(
		$config['webhook-tweeter:endpoint'],
		$config['webhook-tweeter:secret'],
	);
});
$builder->services->addSingleton(WebhookTweeterTwitterAPI::class, BirdElephantTwitterAPI::class, function (Configuration $config): WebhookTweeterTwitterAPI {
	$birdElephant = new BirdElephantTwitterAPI();
	$birdElephant->setCredentials([
		'consumer_key' => $config['twitter:api-key'],
		'consumer_secret' => $config['twitter:api-secret'],
		'bearer_token' => $config['twitter:bearer-token'],
		'token_identifier' => $config['twitter:token-identifier'],
		'token_secret' => $config['twitter:token-secret'],
	]);

	return $birdElephant;
});
$builder->services->addSingleton(WebhookTweeterTemplateLocator::class, SimpleWebhookTweeterTemplateLocator::class, function (Configuration $config): WebhookTweeterTemplateLocator {
	return new SimpleWebhookTweeterTemplateLocator(APP_ROOT . '/templates/webhook-tweeter');
});
$builder->services->addSingleton(WebhookTweeterRenderer::class, SimpleWebhookTweeterRenderer::class);
$builder->services->addSingleton(WebhookTweeterHandler::class, WebhookTweeterHandler::class);

$app = $builder->build();
$app->run();
