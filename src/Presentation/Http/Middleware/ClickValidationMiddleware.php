<?php

namespace Moises\ShortenerApi\Presentation\Http\Middleware;

use Moises\ShortenerApi\Presentation\Http\Factories\ResponseDecoratorFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class ClickValidationMiddleware implements MiddlewareInterface
{

    public function __construct(
        private ResponseDecoratorFactory $responseFactory,
        private LoggerInterface $logger,
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

        // get data
        $shortcode = $this->getShortcode($request);
        $referrer = $this->getReferrer($request);
        $sourceAddress = $this->getSourceAddress($request);
        $userAgent = $this->getUserAgent($request);

        // mutate request (validate and add attributes)
        $isRequestValid = $this->validate($shortcode, $sourceAddress, $referrer); // Fixed variable name
        if (!$isRequestValid) {
            $rf = $this->responseFactory->text();
            return $rf->badRequest();
        }
        $request = $request
            ->withAttribute('shortcode', $shortcode)
            ->withAttribute('referrer', $referrer)
            ->withAttribute('source', $sourceAddress)
            ->withAttribute('user_agent', $userAgent);
        // delegate request to handler (router?) and receive processed response
        $response = $handler->handle($request);

        //return response to be emitted
        return $response->withHeader('X-Processed', '1'); //adding header as example
    }
    //helper method to get shortcode from router url param or request
    private function getShortcode(ServerRequestInterface $request): string
    {
        return $request->getAttribute('shortcode') ?? '';
    }

    //helper method to get source address
    private function getSourceAddress(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();

        // Check multiple possible headers for client IP
        $possibleHeaders = [
            'SOURCE_ADDRESS', // Custom header
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];

        foreach ($possibleHeaders as $header) {
            if (!empty($serverParams[$header])) {
                // Handle comma-separated IPs (X-Forwarded-For)
                $ip = trim(explode(',', $serverParams[$header])[0]);
                if (filter_var(
                    value: $ip,
                    filter: FILTER_VALIDATE_IP,
                    options: FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $serverParams['REMOTE_ADDR'] ?? '';
    }

    //helper method to get referer
    private function getReferrer(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();
        $possibleHeaders = [
            'REFERRER',
            'HTTP_REFERER',
            'REFERRER_ADDRESS'
        ];

        $address = ''; // Initialize the variable
        foreach ($possibleHeaders as $header) {
            if (!empty($serverParams[$header])) {
                $address = $serverParams[$header];
                break; // Stop at first found header
            }
        }

        return $address;
    }

    //helper method to get UserAgent. Not implemented yet.
    //TODO: IMPLEMENT USER AGENT TRACKING
    private function getUserAgent(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();
        $possibleHeaders = [
            'HTTP_USER_AGENT',
        ];

        $userAgent = ''; // Initialize the variable
        foreach ($possibleHeaders as $header) {
            if (!empty($serverParams[$header])) {
                $userAgent = $serverParams[$header];
                break; // Stop at first found header
            }
        }

        return $userAgent;
    }

    // helper method to validate required values.
    // referrer is passed by reference so it can be manually set for debugging in development
    // environment when null
    private function validate(
        ?string $shortcode,
        ?string $sourceAddress,
        ?string &$referrer
    ): bool
    {
        if (APP_DEBUG) {
            error_log('[info]: shortcode: ' . $shortcode);
            error_log('[info]: sourceAddress: ' . $sourceAddress);
            if (empty($referrer)) {
                $msg = '[info]: referrer empty, setting localhost referrer for debugging';
                error_log($msg);
                $referrer = '127.0.0.1';
            }
            error_log('[info]: referrer: ' . $referrer);
        }

        if (empty($shortcode) || empty($sourceAddress) || empty($referrer)) {
            return false;
        }

        return true;
    }

}