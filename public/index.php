<?php
declare(strict_types=1);

use Elephox\Core\Core;
use Elephox\Docs\App;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$core = Core::create();
$core->registerApp(App::class);
$core->handleGlobal();
