<?php

namespace Moises\ShortenerApi\Infrastructure\Services;

use Moises\ShortenerApi\Application\Contracts\DatabaseInterface;
use Moises\ShortenerApi\Infrastructure\Database\MongoAdapter;
use MongoDB\BSON\UTCDateTime;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class MongoLogger implements LoggerInterface
{
    private DatabaseInterface $database;

    public function __construct(MongoAdapter $mongoAdapter)
    {
        $this->database = $mongoAdapter;
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
        $data = [
            'utc_timestamp' => new UTCDateTime(),
            'level' => $level,
            'message' => $this->interpolate($message, $context),
            'context' => $context,
        ];
        try {
            $client = $this->database->getClient();
            $collection = $client->getCollection('links', 'logs');
            $collection->insertOne($data);
        } catch (\Exception $e) {
            error_log('CRITICAL: Logger could not connect to database. No logs are being persisted.');
            error_log("stacktrace:" . PHP_EOL . $e->getTraceAsString());
            error_log("last log:" . PHP_EOL . implode(", ", $data));
        }
    }
    private function interpolate(string $message, array $context = []): string
    {
        $replace = [];
        foreach ($context as $key => $val) {
            if (is_null($val) || is_scalar($val) || $val instanceof \Stringable) {
                $replace['{' . $key . '}'] = (string) $val;
            } elseif (is_object($val)) {
                $replace['{' . $key . '}'] = '[object ' . get_class($val) . ']';
            } else {
                $replace['{' . $key . '}'] = '[' . gettype($val) . ']';
            }
        }
        return strtr($message, $replace);
    }

}