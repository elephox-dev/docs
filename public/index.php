<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/vendor/autoload.php';

use Elephox\Docs\ContentFiles;
use Elephox\Docs\Routes;
use Elephox\Web\Routing\RequestRouter;
use Elephox\Web\WebApplicationBuilder;

$builder = WebApplicationBuilder::create();
$builder->addWhoops();
$builder->setRequestRouterEndpoint();
$builder->service(RequestRouter::class)->loadFromClass(Routes::class);
$builder->services->addSingleton(ContentFiles::class);
$app = $builder->build();
$app->run();
