<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Presentation\Http\Controllers;

use Moises\ShortenerApi\Application\Dtos\LinkDto;
use Moises\ShortenerApi\Application\UseCases\ResolveShortenedLinkUseCase;
use Moises\ShortenerApi\Application\UseCases\RegisterNewClickUseCase;
use Moises\ShortenerApi\Application\UseCases\UseCaseFactoryInterface;
use Moises\ShortenerApi\Presentation\Http\Factories\ResponseDecoratorFactory;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Psr\Log\LoggerInterface;

/** @clickController
 * This class is responsible for executing Http logic related to the Click resource.
 * Some observations:
 * I may be using an "antipattern" (service locator) to instantiate my useCases, but that's
 * because i don't see how it's better to actually inject many use cases that i may not use.
 * Sure, in this scenario, it's not a real problem. But if I had more methods (hence, more
 * routes for REST actions) i would inject at least 5 different useCases in this controller,
 * when I surely would only use one. Since DI containers are recursive, i would not only inject
 * 5 use cases, but ALL their dependencies would also be resolved. This REALLY doesn't sound
 * like a good idea. The way I have it here is with a "UseCaseFactory" (injected, this one)
 * that actually receives a class FQN for the required UseCase and uses the DI container to
 * instantiate it. Sounds like a legitimate approach to me.
 */

class ClickController
{
    public function __construct(
        private UseCaseFactoryInterface $useCaseFactory,
        private ResponseDecoratorFactory $responseDecoratorFactory,
        private LoggerInterface $logger
    ){}
    public function click(ServerRequestInterface $request, array $params): ResponseInterface
    {
        //resolve variables that will be used throughout execution
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        //initiate logContext array with basic info
        $logContext = [
            'class_method' => __METHOD__,
            'request' => [
                'method' => $method,
                'path' => $path,
            ],
        ];

        //try to resolve link and register click
        try {

            //get necessary variables (validated previously via middleware)
            $shortcode = $request->getAttribute('shortcode');
            $sourceAddress = $request->getAttribute('source');
            $referrerAddress = $request->getAttribute('referrer');

            //resolve the shortcode using UseCase
            $linkDto = $this->resolveShortcode($shortcode);

            //if Dto is null (that is, if the UseCase returned null) that means that no link
            // was found for provided shortcode. If that's the case, return a NotFound TextResponse.
            if (is_null($linkDto)) {
                $response = $this->responseDecoratorFactory->text();
                $this->logger->info('404 Not Found', $logContext);
                return $response->notFound();
            }

            //if Dto is not null, then we can proceed to registering the click with needed data
            $this->registerClick($linkDto, $sourceAddress, $referrerAddress);
            $responseFactory = $this->responseDecoratorFactory->getResponse();

            //log response for future analytics
            $this->logger->info('200 OK', $logContext);

            //and return a redirect response with appropriate location
            return $responseFactory
                ->withHeader('Location', $linkDto->getLongUrl())
                ->withStatus(302);

            //if any domain exception is thrown (malformed variables, etc), return a
            // 400 Bad Request textResponse
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
            $responseFactory = $this->responseDecoratorFactory->text();
            return $responseFactory->badRequest(message: $message);

        //if any other unexprected exception is thrown (like a database exception,
        // return a 500 ISE textResponse
        } catch (\Throwable $exception) {
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
            $responseFactory = $this->responseDecoratorFactory->text();
            return $responseFactory->error();
        }
    }

    //helper method to call ResolveShortenedLinkUseCase
    private function resolveShortcode(string $shortcode): ?LinkDto
    {
        /** @var ResolveShortenedLinkUseCase $resolveShortenedLinkUseCase */
        $resolveShortenedLinkUseCase = $this->useCaseFactory
            ->create(ResolveShortenedLinkUseCase::class);
        return $resolveShortenedLinkUseCase->execute($shortcode) ?? null;
    }

    //helper method to call RegisterNewClickUseCase
    private function registerClick(
        LinkDto $linkDto,
        string $sourceAddress,
        string $referrerAddress
    ): void
    {
        //this is inside a try catch so the Exception cant propagate for the main method try catch.
        //this way, if we can find the url and redirect the user, but cannot register click for
        // some reason it will not impact users experience.
        try {
            /** @var RegisterNewClickUseCase $registerNewClickUseCase */
            $registerNewClickUseCase = $this->useCaseFactory
                ->create(RegisterNewClickUseCase::class);
            $registerNewClickUseCase->execute($linkDto, $sourceAddress, $referrerAddress);
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            $this->logger->warning("Failed to register click ($message)", [
                'error' => $exception->getMessage(),
                'link_id' => method_exists($linkDto, 'getId') ? $linkDto->getId() : 'unknown'
            ]);
        }
    }
}
