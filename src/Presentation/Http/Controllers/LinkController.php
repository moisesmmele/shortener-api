<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Presentation\Http\Controllers;

use Moises\ShortenerApi\Application\Dtos\LinkDto;
use Moises\ShortenerApi\Application\UseCases\CollectClicksByLinkUseCase;
use Moises\ShortenerApi\Application\UseCases\RegisterNewLinkUseCase;
use Moises\ShortenerApi\Application\UseCases\ResolveShortenedLinkUseCase;
use Moises\ShortenerApi\Application\UseCases\UseCaseFactoryInterface;
use Moises\ShortenerApi\Presentation\Http\Controllers\Traits\LogThrowable;
use Moises\ShortenerApi\Presentation\Http\Response\Factories\ResponseDecoratorFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;


/**
 * This class is responsible for executing Http logic related to Link Resource.
 *
 * Observations:
 *
 * I'm retrieving a query param to determine if Show method should return
 * a resumed response (with only link data like creation date, id and count of clicks)
 * or a full response, with data related to each click. This should probably be extracted
 * to validation middleware and passed as an attribute, as this would allow for simpler logic
 * here and more robust validation in the middleware itself.
 *
 * As stated in ClickController, I'm using a 'service locator' to instantiate use cases,
 * but I have some reasoning behind it, check previously cited.
 *
 * I'm using a shared Trait LogThrowable to encapsulate throwable logging and stacktrace
 * suppressing. The trait method uses the LoggerInterface instantiated here with the 'this'
 * keyword, and i'm not sure if this is good practice. Both PHPStan and IDE are complaining
 * about logger being only written, but never read, but it is being read. In the Trait.
 */


class LinkController
{
    public function __construct(
        private readonly UseCaseFactoryInterface $useCaseFactory,
        private readonly ResponseDecoratorFactory $responseFactory,
        private readonly LoggerInterface $logger
    ){}

    use LogThrowable;

    public function create(ServerRequestInterface $request): ResponseInterface
    {
        //resolve variables that will be used throughout execution
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        //initiate logContext array with basic info
        /** @var array<string, mixed> $logContext */
        $logContext = [
            "class_method" => __METHOD__,
            'request' => [
                'method' => $method,
                'path' => $path,
            ]
        ];


        //try to register new link
        try {

            //get necessary variables (validated previously via middleware)
            $url = $request->getAttribute('url');

            //register the new link using UseCase
            //no need to validate linkDto because if it fails we should throw exception
            $linkDto = $this->registerNewLink($url);

            //write payload with new link data in linkDto
            $payload = [
                'id' => $linkDto->getId(),
                'url' => $linkDto->getLongUrl(),
                'short_code' => $linkDto->getShortCode(),
                'created_at' => $linkDto->getCreatedAt(),
            ];

            //return success json response
            $response = $this->responseFactory->json();
            return $response->success(message: "201 Created", data: $payload, statusCode: 201);

            //catch domain Exceptions (probably Bad Requests)
        } catch (\DomainException $domainException) {

            $message =$this->logThrowable('warning', $domainException, $logContext);

            //return error response with 400 Bad Request, including message
            $responseFactory = $this->responseFactory->json();
            return $responseFactory->badRequest(message: $message);

            //catch other exceptions (probably 500s Internal server Error)
        } catch (\Throwable $throwable) {

            $this->logThrowable('warning', $throwable, $logContext);

            //error response without context to avoid sensitive leaking
            $responseFactory = $this->responseFactory->json();
            return $responseFactory->error();
        }
    }
    public function show(ServerRequestInterface $request): ResponseInterface
    {
        /* As always, resolve necessary variables and logContext
         * */
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        $logContext = [
            "class_method" => __METHOD__,
            'request' => [
                'method' => $method,
                'path' => $path,
            ]
        ];

        try {

            // get the shortcode attribute set and validated previously in middleware
            $shortcode = $request->getAttribute('shortcode');

            // get linkDto trough helper method that uses UseCase
            $linkDto = $this->resolveShortcode($shortcode);

            // if linkDto empty return not found
            if (!$linkDto) {
                $rf = $this->responseFactory->json();
                return $rf->notFound();
            }

            // get clicks via helper method that uses UseCase
            $clicks = $this->collectClicks($linkDto);

            // create response payload
            $payload = [
                'status' => 'OK',
                'link' => [
                    'id' => $linkDto->getId(),
                    'shortCode' => $linkDto->getShortCode(),
                    'longUrl' => $linkDto->getLongUrl(),
                    'count' => count($clicks),
                ],
            ];

            // verify if query param resumed is string false
            // if it is, append clicks array
            // if it isn't, coalesce to null to suppress undefined array key warning
            // not the best check but oh well
            $isResumed = $request->getQueryParams()['resumed'] ?? 'true';
            if ($isResumed === 'false') {
                $payload['link']['clicks'] = $clicks;
            }

            //return json response with payload
            $responseFactory = $this->responseFactory->json();
            return $responseFactory->success(data: $payload);

        // catch throwable, no need to distinct between domain exceptions cuz
        // it's not possible for this flux to throw it.
        } catch (\Throwable $throwable) {

            // log it
            $this->logThrowable('warning', $throwable, $logContext);

            // return json error response
            $responseFactory = $this->responseFactory->json();
            return $responseFactory->error();
        }
    }

    private function resolveShortcode(string $shortcode): ?LinkDto
    {
        /* @var ResolveShortenedLinkUseCase $resolveShortenedLinkUseCase */
        $resolveShortenedLinkUseCase = $this->useCaseFactory
            ->create(ResolveShortenedLinkUseCase::class);
        return $resolveShortenedLinkUseCase->execute($shortcode);
    }

    /**
     * @param LinkDto $linkDto
     * @return array<string, mixed>
     */
    private function collectClicks(LinkDto $linkDto): array
    {
        /** @var CollectClicksByLinkUseCase $collectClicksByLinkUseCase */
        $collectClicksByLinkUseCase = $this->useCaseFactory
            ->create(CollectClicksByLinkUseCase::class);
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
        return $clicksArray;
    }

    private function registerNewLink(string $url): LinkDto
    {
        /** @var RegisterNewLinkUseCase $registerNewLinkUseCase */
        $registerNewLinkUseCase = $this->useCaseFactory
            ->create(RegisterNewLinkUseCase::class);
        return $registerNewLinkUseCase->execute($url);
    }
}
