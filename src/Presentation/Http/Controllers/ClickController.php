<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Presentation\Http\Controllers;

use Moises\ShortenerApi\Application\Dtos\LinkDto;
use Moises\ShortenerApi\Application\Tasks\BasicDemoTask;
use Moises\ShortenerApi\Application\UseCases\RegisterNewClickUseCase;
use Moises\ShortenerApi\Application\UseCases\ResolveShortenedLinkUseCase;
use Moises\ShortenerApi\Application\UseCases\UseCaseFactoryInterface;
use Moises\ShortenerApi\Infrastructure\Tasks\TaskHandler;
use Moises\ShortenerApi\Presentation\Http\Controllers\Traits\LogThrowable;
use Moises\ShortenerApi\Presentation\Http\Response\Factories\ResponseDecoratorFactory;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Log\LoggerInterface;

/**
 * This class is responsible for executing Http logic related to the Click resource.
 *
 * Observations:
 *
 * I may be using an "antipattern" (service locator) to instantiate my useCases, but that's
 * because I don't see how it's better to actually inject many use cases that I may not use.
 * Sure, in this scenario, it's not a real problem. But if I had more methods (hence, more
 * routes for REST actions) I would inject at least 5 different useCases in this controller,
 * when I surely would only use one. Since DI containers are recursive, I would not only inject
 * 5 use cases, but ALL their dependencies would also be resolved. This REALLY doesn't sound
 * like a good idea. The way I have it here is with a "UseCaseFactory" (injected, this one)
 * that actually receives a class FQN for the required UseCase and uses the DI container to
 * instantiate it. Sounds like a legitimate approach to me.
 */

class ClickController
{
    use LogThrowable;
    public function __construct(
        private readonly UseCaseFactoryInterface  $useCaseFactory,
        private readonly ResponseDecoratorFactory $responseDecoratorFactory,
        private readonly TaskHandler $taskHandler,
        private readonly LoggerInterface $logger
    ){}

    public function click(ServerRequestInterface $request): ResponseInterface
    {
        //resolve variables that will be used throughout execution
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        //initiate logContext array with basic info
        /** @var array<string, mixed> $logContext */
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
                return $response->notFound();
            }

            //if Dto is not null, then we can proceed to registering the click with needed data
            $this->registerClick($linkDto, $sourceAddress, $referrerAddress, $logContext);

            //create a new basic task to simulate a task with deferred execution, like email sending
            $newTask = [
                'class' => BasicDemoTask::class,
                'parameters' => [
                    'shortcode' => $shortcode,
                ]
            ];

            // add task to queue
            $this->taskHandler->add($newTask);

            //and return a redirect response with appropriate location
            $responseFactory = $this->responseDecoratorFactory->getResponse();
            return $responseFactory
                ->withHeader('Location', $linkDto->getLongUrl())
                ->withStatus(302);

            //if any domain exception is thrown (malformed variables, etc), return a
            // 400 Bad Request textResponse
        } catch (\DomainException $domainException) {

            $message = $this->logThrowable('warning', $domainException, $logContext);

            //error response WITH context, cuz if it's a malformed request and user needs to know the error
            $responseFactory = $this->responseDecoratorFactory->text();
            return $responseFactory->badRequest(message: $message);

        //if any other unexpected exception or error is thrown (like a database exception,
        // return a 500 ISE textResponse
        } catch (\Throwable $exception) {

            $this->logThrowable('error', $exception, $logContext);

            //error response without context to avoid leaking
            $responseFactory = $this->responseDecoratorFactory->text();
            return $responseFactory->error();
        }
    }

    /** helper method to execute ResolveShortenedLinkUseCase
     */
    private function resolveShortcode(string $shortcode): ?LinkDto
    {
            /** @var ResolveShortenedLinkUseCase $resolveShortenedLinkUseCase */
            $resolveShortenedLinkUseCase = $this->useCaseFactory
                ->create(ResolveShortenedLinkUseCase::class);
           return $resolveShortenedLinkUseCase->execute($shortcode);
    }


    /** helper method to execute RegisterNewClickUseCase
     * @param array<string, mixed> $logContext
     */
    private function registerClick(
        LinkDto $linkDto,
        string $sourceAddress,
        string $referrerAddress,
        array &$logContext
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
        } catch (\Throwable $throwable) {
            $logContext['class_method'] = __METHOD__;
            $this->logThrowable('error', $throwable, $logContext);
        }
    }
}
