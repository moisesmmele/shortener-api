<?php

namespace Moises\ShortenerApi\Presentation\Http\Controllers\Traits;

trait LogThrowable
{
 /** helper method to log Throwable instance
  * @param array<string, mixed> $logContext*/
    private function logThrowable(string $level, \Throwable $throwable, array &$logContext): string
    {
        //get throwable stack as string and throwable message
        $traceString = $throwable->getTraceAsString();
        $message = $throwable->getMessage();

        //append 'exception' key to logContext
        if ($throwable instanceof \Exception) {
            $logContext['exception'] = [
                'message' => $message,
                'trace' => $traceString,
            ];
        }

        //append 'error' key to logContext
        if ($throwable instanceof \Error) {
            $logContext['error'] = [
                'message' => $message,
                'trace' => $traceString,
            ];
            //overwrites error severity
            $level = 'critical';
        }

        //logs accordingly to signature provided level
        $this->logger->$level($message, $logContext);

        //if APP_DEBUG is set to true, dump stacktrace to the console
        if (APP_DEBUG) {
            error_log('stacktrace: ' . PHP_EOL . $traceString);
        }

        //return throwable message
        return $message;
    }
}