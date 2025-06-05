<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Presentation\Http\Controllers;

use Laminas\Diactoros\Response\JsonResponse;
use Moises\ShortenerApi\Application\Dtos\LinkDto;
use Moises\ShortenerApi\Application\UseCases\CollectClicksByLinkUseCase;
use Moises\ShortenerApi\Application\UseCases\RegisterNewLinkUseCase;
use Moises\ShortenerApi\Application\UseCases\ResolveShortenedLinkUseCase;
use Moises\ShortenerApi\Application\UseCases\UseCaseFactoryInterface;
use Moises\ShortenerApi\Presentation\Http\Factories\ResponseDecoratorFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class LinkController
{
    public function __construct(
        private UseCaseFactoryInterface $useCaseFactory,
        private ResponseDecoratorFactory $responseFactory,
        private LoggerInterface $logger
    ){}

    public function create(ServerRequestInterface $request): ResponseInterface
    {
        //get basic request info
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        //initiate logContext array with basic info
        $logContext = [
            "class_method" => __METHOD__,
            'request' => [
                'method' => $method,
                'path' => $path,
            ]
        ];

        //try to register new link
        try {
            //get necessary variables
            $url = $this->getRequestPayload($request, 'url');
            //validate if required variables are not null
            //TODO: EXTRACT TO REQUEST VALIDATOR
            $valid = $this->validate($url);
            if (!$valid) {
                //log response
                $this->logger->info('400 Bad Request', $logContext);
                //get a new JsonResponseDecorator
                $response = $this->responseFactory->json();
                //write message through payload
                $payload = ['details' => 'No URL was provided for registration',];
                //return a BadRequest Json Response with payload
                return $response->badRequest(data: $payload);
            }

            //register the new link using UseCase
            $linkDto = $this->registerNewLink($url);
            //no need to validate linkDto because if it fails we should throw exception

            //write payload with new link data in linkDto
            $payload = [
                'id' => $linkDto->getId(),
                'url' => $linkDto->getLongUrl(),
                'short_code' => $linkDto->getShortCode(),
                'created_at' => $linkDto->getCreatedAt(),
            ];

            //log success
            $this->logger->info("[$method] [$path] 201 Created", $logContext);

            //return success json response
            $response = $this->responseFactory->json();
            return $response->success(data: $payload, message: "201 Created", statusCode: 201);

            //catch domain Exceptions (probably Bad Requests)
        } catch (\DomainException $domainException) {

            $traceString = $domainException->getTraceAsString();
            $traceMessage = $domainException->getMessage();
            $logContext['exception'] = [
                'message' => $traceMessage,
                'trace' => $traceString,
            ];

            //if appdebug is set to true, then we can dump a stacktrace to the console
            //otherwise, we just log it and return response
            if (APP_DEBUG) {
                error_log('stacktrace: ' . PHP_EOL . $traceString);
            }

            $this->logger->info('400 Bad Request', $logContext);
            $message = "400 Bad Request ($traceMessage)";
            $responseFactory = $this->responseFactory->json();
            return $responseFactory->error(message: $message);

            //catch other exceptions (probably 500s Internal server Error)
        } catch (\Exception $exception) {
            $traceString = $exception->getTraceAsString();
            $traceMessage = $exception->getMessage();
            $logContext['exception'] = [
                'message' => $traceMessage,
                'trace' => $traceString,
            ];

            //if appdebug, we can formulate a more detailed message to output to console
            //otherwise, just log it
            if (APP_DEBUG) {
                $message = "500 Internal Server Error".PHP_EOL.$traceMessage.PHP_EOL.$traceString;
            } else {
                $message = "500 Internal Server Error";
            }
            $this->logger->error($message, $logContext);
            $responseFactory = $this->responseFactory->json();
            return $responseFactory->error();
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

    private function registerNewLink(string $url): ?LinkDto
    {
        /** @var RegisterNewLinkUseCase $registerNewLinkUseCase */
        $registerNewLinkUseCase = $this->useCaseFactory
            ->create(RegisterNewLinkUseCase::class);
        return $registerNewLinkUseCase->execute($url) ?? null;
    }

    private function getRequestPayload(RequestInterface $request, string $key): ?string
    {
        $requestBody = $request->getBody();
        $contents = $requestBody->getContents();
        $array = json_decode($contents, associative: true);
        return $array[$key] ?? null;
    }

    private function validate(?string $url): bool
    {
        if (empty($url)) {
            $valid = false;
        }

        return $valid ?? true;
    }


}
