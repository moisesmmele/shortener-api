<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Infrastructure\Services\Logger;

use Moises\ShortenerApi\Infrastructure\Database\DatabaseInterface;
use Moises\ShortenerApi\Infrastructure\Database\MongoAdapter;
use MongoDB\BSON\UTCDateTime;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class MongoLogger implements LoggerInterface
{
    public function __construct(
        private readonly MongoAdapter $mongoAdapter,
        private readonly Logger $logger
    ){}

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
        // uses base logger to log message to console
        $this->logger->log($level, $message, $context);

        // gets last log from base logger
        $log = $this->logger->getLastLog();

        // create a context array for to-be-logged data
        $data = [
            'utc_timestamp' => new UTCDateTime(),
            'level' => $log->getLevel(),
            'message' => $log->getMessage(),
            'context' => $log->getContext(),
        ];


        try {
            // try to get a mongoDB client from adapter
            $client = $this->mongoAdapter->getClient();

            // get collection
            $collection = $client->getCollection($_ENV['DB_NAME'], 'logs');

            // inserts array of log data in database
            $collection->insertOne($data);
        } catch (\Exception $e) {

            // if log persistence failed, return a warning message to user via base logger.
            $message = 'Could not connect to log database. Logs are NOT being persisted. Exception: {exception}';
            $context = ['exception' => $e->getMessage()];
            $this->logger->warning($message, $context);
            ;
        }
    }
}
