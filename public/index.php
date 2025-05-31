<?php

define("BASE_PATH", dirname(__DIR__, 1));
require BASE_PATH . "/vendor/autoload.php";

(Moises\ShortenerApi\Infrastructure\Bootstrap\AppFactory::create())->run();