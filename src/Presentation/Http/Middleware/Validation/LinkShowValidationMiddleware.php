<?php

namespace Moises\ShortenerApi\Presentation\Http\Middleware\Validation;

use Moises\ShortenerApi\Presentation\Http\Middleware\Validation\Traits\getShortcode;
use Moises\ShortenerApi\Presentation\Http\Response\Factories\ResponseDecoratorFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class LinkShowValidationMiddleware implements ValidationMiddlewareInterface
{
    use getShortcode;

    public function __construct(
        private ResponseDecoratorFactory $responseFactory,
        private LoggerInterface $logger,
    ){}

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        //initiate logContext array with basic info
        $logContext = [
            'class_method' => __METHOD__,
            'request' => [
                'method' => $request->getMethod(),
                'path' => $request->getUri()->getPath(),
            ],
        ];

        //get data
        $shortcode =  $this->getShortcode($request);

        $validated = $this->validate($request);
        if (!$validated) {
            $rf = $this->responseFactory->json();
            return $rf->badRequest();
        }

        //append validated results to request
        foreach ($validated as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        // delegate request to handler (router?) and receive processed response
        $response = $handler->handle($request);

        //return response to be emitted
        return $response->withHeader('X-Processed', '1'); //adding header as example
    }

    public function validate(ServerRequestInterface $request): ?array
    {
        $shortcode = $this->getShortcode($request);
        if (empty($shortcode)) {
            return null;
        }
        return [$shortcode => $shortcode];
    }
}