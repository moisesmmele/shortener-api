<?php

namespace Moises\ShortenerApi\Application\Contracts\Tasks;

interface TaskInterface
{
    public function execute(): void;
}