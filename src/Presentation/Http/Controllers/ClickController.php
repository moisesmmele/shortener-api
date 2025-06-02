<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Presentation\Http\Controllers;

use Moises\ShortenerApi\Application\Contracts\UseCaseFactoryInterface;
use Moises\ShortenerApi\Application\UseCases\RegisterNewClickUseCase;
use Moises\ShortenerApi\Application\UseCases\ResolveShortenedLinkUseCase;
use Moises\ShortenerApi\Presentation\Http\Factories\ResponseDecoratorFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class ClickController
{
    private UseCaseFactoryInterface $useCaseFactory;
    private ResponseDecoratorFactory $responseDecoratorFactory;
    private LoggerInterface $logger;

    public function __construct(UseCaseFactoryInterface $useCaseFactory,
                                ResponseDecoratorFactory $responseDecoratorFactory,
                                LoggerInterface $logger)
    {
        $this->useCaseFactory = $useCaseFactory;
        $this->responseDecoratorFactory = $responseDecoratorFactory;
        $this->logger = $logger;
    }
    public function click(RequestInterface $request, array $params): ResponseInterface
    {
        $shortcode = $this->getShortcode($params);
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        $logContext = [
            'class_method' => __METHOD__,
            'request' => [
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

            $sourceAddress = $this->getSourceAddress($request);
            $referrerAddress = $this->getReferrer($request);

            $linkDto = $resolveShortenedLinkUseCase->execute($shortcode);

            if (is_null($linkDto)) {
                $response = $this->responseDecoratorFactory->text();
                $this->logger->info('404 Not Found', $logContext);
                return $response->notFound();
            }

            $registerNewClickUseCase
                ->execute($linkDto, $sourceAddress, $referrerAddress);

            $responseFactory = $this->responseDecoratorFactory->getResponse();
            $this->logger->info('200 OK', $logContext);
            return $responseFactory->withHeader('Location', $linkDto->getLongUrl())->withStatus(302);

        } catch (\DomainException $domainException) {

            $traceString = $domainException->getTraceAsString();

            if (APP_DEBUG) {
                error_log('stacktrace: ' . PHP_EOL . $traceString);
            }

            $responseFactory = $this->responseDecoratorFactory->text();
            $this->logger->info('400 Bad Request', $logContext);
            return $responseFactory->error(message: $domainException->getMessage(), statusCode: 400);

        } catch (\Throwable $exception) {

            $traceString = $exception->getTraceAsString();
            $message = $exception->getMessage();
            $logContext['exception'] = [
                'message' => $message,
                'trace' => $traceString,
            ];

            if (APP_DEBUG) {
                $message = "500 Internal Server Error".PHP_EOL.$message.PHP_EOL.$traceString;
            } else {
                $message = "500 Internal Server Error";
            }
            $this->logger->error($message, $logContext);
            $responseFactory = $this->responseDecoratorFactory->text();
            return $responseFactory->error();
        }
    }
    public function getReferrer(RequestInterface $request): string
    {
        $referrer = $request->getHeaderLine('Referer');
        if (empty($referrer)) {
            $referrer = 'Not Provided';
        }

        return $referrer;
    }

    public function getShortcode(?array $params = null, ?string $path = null): string
    {
        if (!is_null($path)) {
            return str_replace('/', '', $path);
        }
        return $params['shortcode'] ?? 'unknown';
    }

    public function getSourceAddress(RequestInterface $request): string
    {
        $addr = $request->getServerParams()['REMOTE_ADDR'];
        if (empty($addr)) {
            throw new \Exception('Source Address is not valid');
        }
        return $addr;
    }

}
