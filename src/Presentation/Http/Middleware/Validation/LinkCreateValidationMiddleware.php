<?php

namespace Moises\ShortenerApi\Presentation\Http\Middleware\Validation;

use Moises\ShortenerApi\Presentation\Http\Response\Factories\ResponseDecoratorFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class LinkCreateValidationMiddleware implements ValidationMiddlewareInterface
{

    /**
     * @inheritDoc
     */
    public function __construct(
        private LoggerInterface $logger,
        private ResponseDecoratorFactory $responseFactory,
    ){}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        //initiate logContext array with basic info
        $logContext = [
            'class_method' => __METHOD__,
            'request' => [
                'method' => $request->getMethod(),
                'path' => $request->getUri()->getPath(),
            ]
        ];

        $validated = $this->validate($request);
        if (!$validated) {
            //log response
            $this->logger->info('400 Bad Request', $logContext);
            //get a new JsonResponseDecorator
            $rf = $this->responseFactory->json();
            //write message through payload
            $payload = ['details' => 'Malformed Url',];
            //return a BadRequest Json Response with payload
            return $rf->badRequest(data: $payload);
        }

        foreach ($validated as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        return $handler->handle($request);
    }

    private function getRequestPayload(ServerRequestInterface $request, string $key): ?string
    {
        $requestBody = $request->getBody();
        $contents = $requestBody->getContents();
        $array = json_decode($contents, associative: true);
        return $array[$key] ?? null;
    }

    public function validate(ServerRequestInterface $request): ?array
    {
        $url = $this->getRequestPayload($request, 'url');

        if (!$url) {
            return null;
        }

        // Basic filter validation
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $parsed = parse_url($url);

        // Check scheme
        if (!in_array(strtolower($parsed['scheme']), ['http', 'https'])) {
            return false;
        }

        // Check host format
        if (!filter_var($parsed['host'], FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            // If not a valid domain, check if it's a valid IP
            if (!filter_var($parsed['host'], FILTER_VALIDATE_IP)) {
                return false;
            }
        }

        return ['url' => $url,];
    }
}