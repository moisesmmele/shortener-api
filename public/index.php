<?php declare(strict_types=1);

use Moises\ShortenerApi\Infrastructure\Bootstrap\AppFactory;

define("BASE_PATH", dirname(__DIR__, 1));
require BASE_PATH . "/vendor/autoload.php";

$app = AppFactory::create();
$app->handle();