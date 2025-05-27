<?php declare(strict_types=1);

namespace Moises\ShortenerApi\Infrastructure\Controllers;

use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Response\TextResponse;
use Moises\ShortenerApi\Application\Contracts\UseCaseFactoryInterface;
use Moises\ShortenerApi\Application\UseCases\RegisterNewClickUseCase;
use Moises\ShortenerApi\Application\UseCases\ResolveShortenedLinkUseCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
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
        $logContext = [
            "class" => get_class($this),
            "class_method" => __METHOD__,
            "request" => [
                'method' => $request->getMethod(),
                'uri' => (string) $request->getUri(),
            ],
        ];

        try {
            /** @var ResolveShortenedLinkUseCase $resolveShortenedLinkUseCase */
            $resolveShortenedLinkUseCase = $this->useCaseFactory
                ->create(ResolveShortenedLinkUseCase::class);

            /** @var RegisterNewClickUseCase $registerNewClickUseCase */
            $registerNewClickUseCase = $this->useCaseFactory
                ->create(RegisterNewClickUseCase::class);

            $shortcode = str_replace('/', '', $request->getUri()->getPath());
            $sourceAddress = $request->getServerParams()['REMOTE_ADDR'];
            $referrerAddress = $request->getHeaderLine('Referer');

            $linkDto = $resolveShortenedLinkUseCase->execute($shortcode);

            if (is_null($linkDto)) {
                $logContext['link_info'] = [
                    'shortcode' => $shortcode,
                ];

                $message = 'Could not resolve shortened link (not found)';
                $this->logger->info($message, $logContext);
                return new TextResponse('404 Not found.', 404);
            }

            $registerNewClickUseCase
                ->execute($linkDto, $sourceAddress, $referrerAddress);

            $this->logger->info('link resolved.', $logContext);
            return new RedirectResponse($linkDto->getLongUrl());

        } catch (\Throwable $exception) {

            $logContext['exception'] = [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'trace' => $exception->getTrace(),
            ];

            $this->logger->critical('Could not resolve link.', $logContext);
            return new TextResponse('Bad Server Request', 500);
        }
    }
}