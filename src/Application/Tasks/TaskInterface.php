<?php

namespace Moises\ShortenerApi\Application\Tasks;

interface TaskInterface
{
    public function execute(): void;
    public function __toString(): string;
}