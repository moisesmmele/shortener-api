<?php

namespace Moises\ShortenerApi\Application\Tasks;

use Moises\ShortenerApi\Application\Tasks\TaskInterface;

class BasicDemoTask implements TaskInterface
{
    public function __construct(
        private readonly string $shortcode
    ){}

    public function execute(): void
    {
        sleep(2);
        error_log("[info]: task was executed! shortcode: $this->shortcode");
    }

    public function __toString(): string
    {
        return "BasicDemoTask";
    }
}