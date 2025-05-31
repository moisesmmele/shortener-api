<?php

namespace Moises\ShortenerApi\Infrastructure\Services\Logger;

use Psr\Log\LoggerInterface;

class Logger implements LoggerInterface
{
    private Log $lastLog;
    public function emergency(\Stringable|string $message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }
    public function alert(\Stringable|string $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }
    public function critical(\Stringable|string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }
    public function error(\Stringable|string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }
    public function warning(\Stringable|string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }
    public function notice(\Stringable|string $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }
    public function info(\Stringable|string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }
    public function debug(\Stringable|string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $this->lastLog = new Log($level, $this->interpolate($message, $context), $context);
        $this->printLastLog();
    }

    public function getLastLog(): Log
    {
        return $this->lastLog;
    }

    public function printLastLog(): void
    {
        if (!$this->lastLog) {
            throw new \Exception('No log object was specified.');
        }
        $level = $this->lastLog->getLevel();
        $message = $this->lastLog->getMessage();
        error_log("[$level]: $message");
    }
    private function interpolate(string|\Stringable $message, array $context = []): string
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
