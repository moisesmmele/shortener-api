<?php

namespace Moises\ShortenerApi\Presentation\Http\Middleware\Validation;

use Moises\ShortenerApi\Presentation\Http\Middleware\Validation\Traits\getShortcode;
use Moises\ShortenerApi\Presentation\Http\Response\Factories\ResponseDecoratorFactory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LinkDeleteValidationMiddleware implements ValidationMiddlewareInterface
{
    use getShortcode;

    public function __construct(
        private readonly ResponseDecoratorFactory $responseFactory
    ){}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $validated = $this->validate($request);
        if (!$validated) {
            $rf = $this->responseFactory->json();
            return $rf->badRequest('shortcode not provided');
        }

        $request = $request->withAttribute('shortcode', $validated['shortcode']);
        return $handler->handle($request);
    }

    function validate(ServerRequestInterface $request): ?array
    {
        $shortcode = $this->getShortcode($request);
        if (empty($shortcode)) {
            return null;
        }

        return [
            'shortcode' => $shortcode,
        ];
    }
}