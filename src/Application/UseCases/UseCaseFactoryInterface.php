<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Application\UseCases;

interface UseCaseFactoryInterface
{
    public function create(string $useCase);
}
