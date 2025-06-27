<?php

namespace Moises\ShortenerApi\Application\Tasks;

use Moises\ShortenerApi\Application\Dtos\LinkDto;
use Moises\ShortenerApi\Application\UseCases\CollectAllLinksUseCase;
use Moises\ShortenerApi\Application\UseCases\DeleteLinkUseCase;
use Moises\ShortenerApi\Application\UseCases\Factories\UseCaseFactory;
use Moises\ShortenerApi\Application\UseCases\GetExpiredLinksUseCase;
use Moises\ShortenerApi\Domain\Entities\Link;
use Moises\ShortenerApi\Domain\Repositories\LinkRepository;

class PerformDatabaseCleanupTask implements TaskInterface
{
    public function __construct(
        private UseCaseFactory $useCaseFactory
    ){}

    public function execute(): void
    {
        error_log('Hit database cleanup task');
        /* @var CollectAllLinksUseCase $collectAllLinks */
        $collectAllLinks =  $this->useCaseFactory
            ->create(CollectAllLinksUseCase::class);
        $linksDto = $collectAllLinks->execute();
        if (empty($linksDto)) {
            return;
        }

        /* @var GetExpiredLinksUseCase $getExpiredLinks */
        $getExpiredLinks = $this->useCaseFactory
            ->create(GetExpiredLinksUseCase::class);
        $expiredLinksDto = $getExpiredLinks->execute($linksDto);
        if (empty($expiredLinksDto)) {
            return;
        }

        /* @var DeleteLinkUseCase $deleteLink */
        $deleteLink = $this->useCaseFactory
            ->create(DeleteLinkUseCase::class);
        $deleteLink->execute($expiredLinksDto);

    }

    public function __toString(): string
    {
        return 'PerformDatabaseCleanupTask';
    }
}