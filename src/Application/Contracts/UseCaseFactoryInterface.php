<?php

namespace Moises\ShortenerApi\Application\Contracts;

interface UseCaseFactoryInterface
{
    public function create(string $useCase);
}