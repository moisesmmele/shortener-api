<?php

namespace Moises\ShortenerApi\Application\UseCases;

use Moises\ShortenerApi\Application\Dtos\LinkDto;
use Moises\ShortenerApi\Application\Mappers\LinkMapper;
use Moises\ShortenerApi\Domain\Repositories\LinkRepository;
use Psr\Log\LoggerInterface;

class DeleteLinkUseCase
{
    public function __construct(
        private LinkMapper $linkMapper,
        private LinkRepository $linkRepository,
        private LoggerInterface $logger
    ){}

    /* @param LinkDto[] $expiredLinksDto */
    public function execute(array $expiredLinksDto): int
    {
        $countDeleted = 0;
        foreach ($expiredLinksDto as $linkDto) {
            try {
                $link = $this->linkMapper->fromDto($linkDto);
                $this->linkRepository->delete($link);
                $countDeleted++;
            } catch (\Throwable $e) {
                $logContext = [
                    'class_method' => __METHOD__,
                    'exception' => [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]
                ];
                $id = $linkDto->getId();
                $this->logger->error("Error during Link {$id} exclusion.", $logContext);
            }
        }
        return $countDeleted;
    }
}