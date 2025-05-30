<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Infrastructure\Controllers;

use Moises\ShortenerApi\Application\UseCases\ResolveShortenedLinkUseCase;
use Moises\ShortenerApi\Application\Contracts\UseCaseFactoryInterface;
use Moises\ShortenerApi\Application\UseCases\RegisterNewClickUseCase;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

class ClickController
{
    private UseCaseFactoryInterface $useCaseFactory;
    private LoggerInterface $logger;

    public function __construct(UseCaseFactoryInterface $useCaseFactory, LoggerInterface $logger)
    {
        $this->useCaseFactory = $useCaseFactory;
        $this->logger = $logger;
    }
    public function click(RequestInterface $request): ResponseInterface
    {
        $method = $request->getMethod();
        $uri = $request->getUri();
        $path = $uri->getPath();

        $logContext = [
            "class_method" => __METHOD__,
            "request" => [
                'method' => $method,
                'path' => $path,
            ],
        ];

        try {
            /** @var ResolveShortenedLinkUseCase $resolveShortenedLinkUseCase */
            $resolveShortenedLinkUseCase = $this->useCaseFactory
                ->create(ResolveShortenedLinkUseCase::class);

            /** @var RegisterNewClickUseCase $registerNewClickUseCase */
            $registerNewClickUseCase = $this->useCaseFactory
                ->create(RegisterNewClickUseCase::class);

            $shortcode = str_replace('/', '', $path);
            $sourceAddress = $request->getServerParams()['REMOTE_ADDR'];
            $referrerAddress = $request->getHeaderLine('Referer');

            $linkDto = $resolveShortenedLinkUseCase->execute($shortcode);

            if (is_null($linkDto)) {
                $logContext['link_info'] = [
                    'shortcode' => $shortcode,
                ];

                $message = "[$method] [$path] 404 Not Found";
                $this->logger->info($message, $logContext[] = ['outcome' => 'resolved, but link not found']);
                return new TextResponse('404 Not found.', 404);
            }

            $registerNewClickUseCase
                ->execute($linkDto, $sourceAddress, $referrerAddress);

            $this->logger->info("[$method] [$path] 200 OK", $logContext);
            return new RedirectResponse($linkDto->getLongUrl());

        } catch (\Throwable $exception) {

            $message = $exception->getMessage();
            $code = $exception->getCode();
            $trace = $exception->getTrace();
            $traceString = $exception->getTraceAsString();

            $logContext['exception'] = [
                'message' => $message,
                'code' => $code,
                'trace' => $trace,
            ];

            $this->logger->critical("[$method] [$path] 500 Internal Server Error ($message)", $logContext[] =
            ['outcome' => 'unable to resolve due to internal server error']);
            if (APP_DEBUG) {
                error_log('stacktrace: ' . PHP_EOL . $traceString);
            }
            return new TextResponse('500 Internal Server Error', 500);
        }
    }
}
