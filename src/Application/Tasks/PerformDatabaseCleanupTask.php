<?php

namespace Moises\ShortenerApi\Application\Tasks;

use Moises\ShortenerApi\Application\Dtos\LinkDto;
use Moises\ShortenerApi\Application\UseCases\Factories\UseCaseFactory;
use Moises\ShortenerApi\Domain\Entities\Link;
use Moises\ShortenerApi\Domain\Repositories\LinkRepository;

class PerformDatabaseCleanupTask implements TaskInterface
{

    private UseCaseFactory $useCaseFactory;
    private LinkRepository $linkRepository;

    public function __construct(UseCaseFactory $useCaseFactory, LinkRepository $linkRepository)
    {
        $this->useCaseFactory = $useCaseFactory;
        $this->linkRepository = $linkRepository;
    }

    public function execute(): void
    {
        $usecase = $this->useCaseFactory->create();
        $links = $usecase->execute();
        foreach ($links as $dto) {
            if ($this->isTtlExpired($dto)) {
                $link = new Link();
                $link->setId($dto->getId());
                $this->linkRepository->delete($link);
            }
        }
    }

    public function isTtlExpired(LinkDto $link): bool
    {
        $ttl = $link->getTtl();
        $expiry = $link->getUtcTimestamp()->modify("+{$ttl} seconds");
        return $expiry < new \DateTime();
    }

    public function __toString(): string
    {
        return 'PerformDatabaseCleanupTask';
    }
}