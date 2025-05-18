<?php

define("BASE_PATH", dirname(__DIR__, 1));
require BASE_PATH . "/vendor/autoload.php";

const DB = new PDO("sqlite:" . BASE_PATH . "/database/database.sqlite");

try {
    (new \Moises\ShortenerApi\Application\Router())->dispatch();

} catch (\Exception $e) {
    error_log('ERROR: ' . $e->getMessage());
    error_log("stackTrace:" . PHP_EOL . $e->getTraceAsString());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'location' => 'index.php',
        'status' => "Internal Server Error",
        'status_code' => 500,
        'message' => $e->getMessage(),
    ]);
}
