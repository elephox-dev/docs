<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Elephox\Core\Core;
use Elephox\Docs\App;

$core = Core::create();

$core->registerApp(App::class);

return $core;
