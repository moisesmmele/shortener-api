<?php declare(strict_types=1);

namespace Moises\ShortenerApi\Application\Contracts;

interface UseCaseFactoryInterface
{
    public function create(string $useCase);
}