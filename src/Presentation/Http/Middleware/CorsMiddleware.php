<?php

namespace Moises\ShortenerApi\Presentation\Http\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

/* @description very simple CORS middleware to append Access-Control headers globally and respond
 * to preflight requests. Actual policies can be defined in policy headers array, which could be
 * read from a config file instead of directly declared here.
 *
 * TODO: MAKE IT WORK :P
 * atm it's not working because Route Matching is running before middleware calling.
 * I don't know if this is a problem with how i implemented the League Router Adapter or if
 * it's supposed to work that way with PSR-15.
 *
 * I believe that the best way to handle CORS isn't even globally tho, It's on a route basis
 * (specially for preflight), so... yeah, might have to pivot this idea. Atm it doesn't really
 * matter.
*/

class CorsMiddleware implements MiddlewareInterface
{

    /**
     * @inheritDoc
     */
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
    ){}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $policyHeaders = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => '*',
            'Access-Control-Allow-Headers' => '*',
            'Access-Control-Max-Age' => '86400',
        ];

        $method = $request->getMethod();
        if ($method === 'OPTIONS') {
            error_log('responding a preflight request');
            return $this->preflightResponse($policyHeaders);
        }

        $response = $handler->handle($request);
        return $this->applyPolicies($policyHeaders, $response);
    }

    public function preflightResponse(array $policies): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(204);
        return $this->applyPolicies($policies, $response);
    }

    public function applyPolicies(array $policyHeaders, ResponseInterface $response): ResponseInterface
    {
        foreach ($policyHeaders as $header => $value) {
            $response = $response->withHeader($header, $value);
        }
        return $response;
    }
}