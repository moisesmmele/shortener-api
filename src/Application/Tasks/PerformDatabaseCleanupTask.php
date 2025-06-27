<?php

namespace Moises\ShortenerApi\Application\Tasks;

use Moises\ShortenerApi\Application\Dtos\LinkDto;
use Moises\ShortenerApi\Application\UseCases\CollectAllLinksUseCase;
use Moises\ShortenerApi\Application\UseCases\DeleteLinkUseCase;
use Moises\ShortenerApi\Application\UseCases\Factories\UseCaseFactory;
use Moises\ShortenerApi\Application\UseCases\GetExpiredLinksUseCase;
use Moises\ShortenerApi\Domain\Entities\Link;
use Moises\ShortenerApi\Domain\Repositories\LinkRepository;
use Psr\Log\LoggerInterface;

class PerformDatabaseCleanupTask implements TaskInterface
{
    /* Basic Database Cleanup Task. This Class is responsible for declaring a task
     * to be executed globally each request and response cycle. This is task verifies
     * if any link is expired. If it is, the task remove the link from the database.
     * It implements the TaskInterface Interface, and it is supposed to be executed by
     * a Task Handler.
     */


    public function __construct(
        private UseCaseFactory $useCaseFactory,
        private LoggerInterface $logger,
    ){}

    public function execute(): void
    {
        $logContext = ['class_method' => __METHOD__,];
        /* @var CollectAllLinksUseCase $collectAllLinks */
        try {
            $collectAllLinks =  $this->useCaseFactory
                ->create(CollectAllLinksUseCase::class);
            $linksDto = $collectAllLinks->execute();
            if (empty($linksDto)) {
                $this->logger->info('No registered links found', $logContext);
                return;
            }
        } catch (\Throwable $e) {
            $logContext['exception'] = [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ];
            $this->logger->error('Error during links collection for exclusion flow', $logContext);
            return;
        }

        try {
            /* @var GetExpiredLinksUseCase $getExpiredLinks */
            $getExpiredLinks = $this->useCaseFactory
                ->create(GetExpiredLinksUseCase::class);
            $expiredLinksDto = $getExpiredLinks->execute($linksDto);
            if (empty($expiredLinksDto)) {
                $this->logger->info('No expired links found.', $logContext);
                return;
            }
            $count = count($expiredLinksDto);
            $this->logger->info("{$count} links marked for exclusion.");
        } catch (\Throwable $e) {
            $logContext['exception'] = [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ];
            $this->logger->error('Error during link expiry flow.', $logContext);
            return;
        }
        try {
            /* @var DeleteLinkUseCase $deleteLink */
            $deleteLink = $this->useCaseFactory
                ->create(DeleteLinkUseCase::class);
            $countDeleted = $deleteLink->execute($expiredLinksDto);
            $countNotDeleted = count($expiredLinksDto) - $countDeleted;
            $this->logger->info("$countDeleted links deleted, $countNotDeleted links not deleted.");
        } catch (\Throwable $e) {
            $logContext['exception'] = [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ];
            $this->logger->error('Error during Link exclusion flow.', $logContext);
            return;
        }
    }

    public function __toString(): string
    {
        return 'PerformDatabaseCleanupTask';
    }
}