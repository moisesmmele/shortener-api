<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Presentation\Http\Controllers;

use Laminas\Diactoros\Response\JsonResponse;
use Moises\ShortenerApi\Application\Dtos\LinkDto;
use Moises\ShortenerApi\Application\UseCases\CollectClicksByLinkUseCase;
use Moises\ShortenerApi\Application\UseCases\RegisterNewLinkUseCase;
use Moises\ShortenerApi\Application\UseCases\ResolveShortenedLinkUseCase;
use Moises\ShortenerApi\Application\UseCases\UseCaseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class LinkController
{
    public function __construct(
        private UseCaseFactoryInterface $useCaseFactory,
        private LoggerInterface $logger
    ){}

    public function create(ServerRequestInterface $request): ResponseInterface
    {
        //get basic request info
        $method = $request->getMethod();
        $uri = $request->getUri();
        $path = $uri->getPath();

        //initiate logContext array with basic info
        $logContext = [
            "class_method" => __METHOD__,
            'request' => [
                'method' => $method,
                'path' => $path,
            ]
        ];

        try {
            //get the appropriate UseCase
            /** @var RegisterNewLinkUseCase $registerNewLinkUseCase */
            $registerNewLinkUseCase = $this->useCaseFactory
                ->create(RegisterNewLinkUseCase::class);

            //get aditional request info
            $body = $request->getBody();
            $contents = $body->getContents();
            //decode json request to assoc array
            $data = json_decode($contents, associative: true);

            //checks if a url was provided, if not, return Bad Request
            $url = $data['url'];
            if (!$url) {
                //add outcome to logContext array
                $logContext['outcome'] = 'resolved, but no URL was provided for registration';
                $this->logger->info('user provided no URL for registration', $logContext);
                return new JsonResponse([
                   'message' => 'Bad Request',
                    'details' => 'No URL was provided for registration',
                ], 400);
            }

            //execute de UseCase with collected request data
            $linkDto = $registerNewLinkUseCase->execute($url);
            $responseBody = [
                'message' => 'OK',
                'link' => [
                    'id' => $linkDto->getId(),
                    'url' => $linkDto->getLongUrl(),
                    'shortCode' => $linkDto->getShortCode(),
                ]
            ];

            $this->logger->info("[$method] [$path] 201 Created", $logContext);
            //returns Created Response
            return new JsonResponse($responseBody, 201);

            //catch domain Exceptions (probably Bad Requests)
        } catch (\DomainException $domainException) {

            $message =  $domainException->getMessage();
            $code = $domainException->getCode();
            $trace  = $domainException->getTrace();

            $logContext['outcome'] = 'Domain Exception';
            $logContext['exception'] = [
                'message' => $message,
                'code' => $code,
                'trace' => $trace,
            ];

            $responseBody = [
                'message' => 'Bad Request',
                'details' => "$message",
            ];
            $this->logger->critical("[$method] [$path] 400 Bad Request ($message)", $logContext);
            return new JsonResponse($responseBody, 400);
            //catch other exceptions (probably 500s Internal server Error)
        } catch (\Exception $exception) {
            $message =  $exception->getMessage();
            $code = $exception->getCode();
            $trace  = $exception->getTrace();

            $logContext['outcome'] = 'Exception';
            $logContext['exception'] = [
                'message' => $message,
                'code' => $code,
                'trace' => $trace,
            ];

            $responseBody = [
                'message' => 'Internal Server Error',
                'details' => "$message",
            ];
            $this->logger->critical("[$method] [$path] 500 Internal Server Error ($message)", $logContext);
            return new JsonResponse($responseBody, 500);
        }
    }
    public function show(ServerRequestInterface $request, $params): ResponseInterface
    {
        $shortcode = $params['shortcode'];
        $method = $request->getMethod();
        $uri = $request->getUri();
        $path = $uri->getPath();
        $logContext = [
            "class_method" => __METHOD__,
            'request' => [
                'method' => $method,
                'path' => $path,
            ]
        ];
        try {
            $resolveShortenedLinkUseCase = $this->useCaseFactory->create(ResolveShortenedLinkUseCase::class);
            $collectClicksByLinkUseCase = $this->useCaseFactory->create(CollectClicksByLinkUseCase::class);
            $linkDto = $resolveShortenedLinkUseCase->execute($shortcode);
            $clicks = $collectClicksByLinkUseCase->execute($linkDto);
            $clicksArray = [];
            foreach ($clicks as $click) {
                $clicksArray[] = [
                    'id' => $click->getId(),
                    'sourceAddress' => $click->getSourceIp(),
                    'referrerAddress' => $click->getReferrer(),
                    'timestamp' => $click->getUtcTimestampString(),
                ];
            }
            $body = [
                'status' => 'OK',
                'link' => [
                    'id' => $linkDto->getId(),
                    'shortCode' => $linkDto->getShortCode(),
                    'longUrl' => $linkDto->getLongUrl(),
                ],
                'clicks' => $clicksArray,
            ];
        } catch (\Throwable $exception) {
            $message =  $exception->getMessage();
            $code = $exception->getCode();
            $trace  = $exception->getTrace();
            $traceString = $exception->getTraceAsString();
            $logContext['outcome'] = 'Exception';
            $logContext['exception'] = [
                'message' => $message,
                'code' => $code,
                'trace' => $trace,
                'traceString' => $traceString,
            ];
            $this->logger->error("[$method] [$path] 500 Internal Server Error ($message)", $logContext);
            $responseBody = [
                'message' => 'Internal Server Error',
                'details' => "$message",
            ];
            if (APP_DEBUG) {
                error_log("trace:" . PHP_EOL . "$traceString");
            }
            return new JsonResponse($responseBody, 500);
        }
        return new JsonResponse($body, 200);
    }

    private function resolveShortcode(string $shortcode): string
    {

    }

    private function collectClicks(LinkDto $linkDto): array
    {

    }

    private function registerNewLink(string $url): LinkDto
    {

    }
}
