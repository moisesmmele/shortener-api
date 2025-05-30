<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Infrastructure\Services\Logger;

use Moises\ShortenerApi\Application\Contracts\DatabaseInterface;
use Moises\ShortenerApi\Infrastructure\Database\MongoAdapter;
use MongoDB\BSON\UTCDateTime;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class MongoLogger implements LoggerInterface
{
    private DatabaseInterface $database;
    private Logger $logger;

    public function __construct(MongoAdapter $mongoAdapter, Logger $logger)
    {
        $this->database = $mongoAdapter;
        $this->logger = $logger;
    }

    public function emergency(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug(\Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
        $log = $this->logger->getLastLog();

        $data = [
            'utc_timestamp' => new UTCDateTime(),
            'level' => $log->getLevel(),
            'message' => $log->getMessage(),
            'context' => $log->getContext(),
        ];


        try {
            $client = $this->database->getClient();
            $collection = $client->getCollection($_ENV['DB_NAME'], 'logs');
            $collection->insertOne($data);
        } catch (\Exception $e) {
            error_log('[warning]: Logger could not connect to database. No logs are being persisted.');
            error_log('message: ' . $e->getMessage());
            #error_log('stacktrace: '. PHP_EOL . $e->getTraceAsString());
        }
    }
}
