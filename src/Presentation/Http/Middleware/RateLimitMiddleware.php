<?php

namespace Moises\ShortenerApi\Presentation\Http\Middleware;

use Moises\ShortenerApi\Presentation\Http\Response\Factories\ResponseDecoratorFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface;

class RateLimitMiddleware implements MiddlewareInterface
{
    public function __construct(
        private CacheInterface $cache,
        private ResponseDecoratorFactory $responseFactory,
        private int $maxAttempts = 5,
    ) {}

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $possibleHeaders = [
            'Client-Ip',
            'X-Forwarded-For',
            'X-Forwarded',
            'X-Cluster-Client-Ip',
            'Forwarded-For',
            'Forwarded',
        ];

        $ip = null;
        foreach ($possibleHeaders as $header) {
            $headerValue = $request->getHeaderLine($header);
            if (!empty($headerValue)) {
                $ip = explode(',', $headerValue)[0]; // Use first IP in list
                break;
            }
        }

        if (empty($ip)) {
            $ip = $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
        }

        $cacheKey = "rate_limit_{$ip}";
        $requestCount = $this->cache->get($cacheKey, 0);

        if ($requestCount >= $this->maxAttempts) {
            $response = $this->responseFactory->getResponse();
            return $response->withStatus(429);
        }

        $this->cache->set($cacheKey, $requestCount + 1, 60); // TTL = 60 seconds

        return $handler->handle($request);
    }
}
