<?php

namespace Moises\ShortenerApi\Application\Tasks;

use Moises\ShortenerApi\Application\Dtos\LinkDto;
use Moises\ShortenerApi\Application\Mappers\LinkMapper;
use Moises\ShortenerApi\Application\Tasks\TaskInterface;
use Moises\ShortenerApi\Application\UseCases\Factories\UseCaseFactory;
use Moises\ShortenerApi\Application\UseCases\ResolveShortenedLinkUseCase;
use Moises\ShortenerApi\Application\UseCases\UseCaseInterface;
use Moises\ShortenerApi\Infrastructure\Tasks\TaskHandler;
use Psr\Log\LoggerInterface;

class SendEmailTask implements TaskInterface
{
    // $body, $destination and $subject are passed via array in DI\Container::Make()
    // while $service and $logger are injected by the container according to bound definition
    public function __construct(
        private readonly string $body,
        private readonly string $destination,
        private readonly string $subject,
        private readonly EmailServiceInterface $service,
        private readonly LoggerInterface $logger
    ){}

    // interface defined execute method
    public function execute(): void
    {
        // try to use $this->service (which is an instance of EmailServiceInterface) to send an email
        try {

            $this->service->send($this->destination, $this->subject, $this->body);

            //logs success
            $this->logger->info('email sent', $context = ['success' => 'true']);

        } catch (\Throwable $e) {

            // logs failure
            $this->logger->error('email not sent', $context = ['exception' => $e]);

            // re-throw exection so it can be logged and handled by Task Handler
            throw $e;
        }
    }
    public function __toString(): string
    {
        return "BasicDemoTask";
    }
}

class NotifyUserOfLinkClickUseCase
{
    // DI injected TaskHandler
    public function __construct(
        private readonly TaskHandlerInterface $taskHandler,
    ){}

    public function execute(LinkDto $link): void
    {
        // get users email from LinkDto
        $email = $link->getEmail();

        // get shortcode from linkDto
        $shortcode = $link->getShortcode();

        // generate readable timestamp
        $time = date("H:i:s");

        // the email body with informative parameters
        $message = "Your link with shortcode $shortcode was clicked at $time!";

        // create task with an appropriate array containing 'class' key with class FQN
        // and 'parameters' key with values, as it is manted by TaskHandler
        $task = [
            'class' => SendEmailTask::class,
            'parameters' => [
                'body' => $message,
                'subject' => 'new click detected!',
                'destination' => $email,
            ]
        ];

        // add task to Handlers queue, so it can be executed later
        $this->taskHandler->add($task);
    }
}