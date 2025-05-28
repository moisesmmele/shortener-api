<?php declare(strict_types=1);

namespace Moises\ShortenerApi\Infrastructure\Controllers;

use Moises\ShortenerApi\Application\Contracts\UseCaseFactoryInterface;
use Moises\ShortenerApi\Application\UseCases\RegisterNewLinkUseCase;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Log\LoggerInterface;

class LinkController
{
    private UseCaseFactoryInterface $useCaseFactory;
    private LoggerInterface $logger;

    public function __construct(UseCaseFactoryInterface $useCaseFactory, LoggerInterface $logger)
    {
        $this->useCaseFactory = $useCaseFactory;
        $this->logger = $logger;
    }

    public function create(ServerRequestInterface $request): JsonResponse
    {
        $logContext = [
            "class" => get_class($this),
            "class_method" => __METHOD__,
            'request' => [
                'method' => $request->getMethod(),
                'uri' => (string) $request->getUri(),
            ]
        ];

        try {
            /** @var RegisterNewLinkUseCase $registerNewLinkUseCase */
            $registerNewLinkUseCase = $this->useCaseFactory
                ->create(RegisterNewLinkUseCase::class);

            $body = $request->getBody();
            $contents = $body->getContents();
            $data = json_decode($contents, associative: true);

            $url = $data['url'];
            if (!$url) {
                $logContext['outcome'] = 'failure';
                $this->logger->info('user provided no URL for registration', $logContext);
                return new JsonResponse([
                   'message' => 'No url provided'
                ], 422);
            }

            $linkDto = $registerNewLinkUseCase->execute($url);
            $responseBody = [
                'message' => 'success',
                'link' => [
                    'id' => $linkDto->getId(),
                    'url' => $linkDto->getLongUrl(),
                    'shortCode' => $linkDto->getShortCode(),
                ]
            ];

            $this->logger->info('created a new link.', $logContext);
            return new JsonResponse($responseBody);

        } catch (\Throwable $exception) {

            $logContext['outcome'] = 'exception';
            $logContext['exception'] = [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'trace' => $exception->getTrace(),
            ];

            $this->logger->critical('Could not register link', $logContext);
            return new JsonResponse(['message' => 'Bad Server Request'], 500);
        }
    }
}