<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Application\UseCases;

use Moises\ShortenerApi\Application\Mappers\LinkMapper;
use Moises\ShortenerApi\Domain\Repositories\LinkRepository;
use Moises\ShortenerApi\Application\Dtos\LinkDto;
use Moises\ShortenerApi\Infrastructure\Services\Logger\Log;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

class ResolveShortenedLinkUseCase
{
    public function __construct(
        private readonly LinkRepository $linkRepository,
        private readonly LinkMapper $linkMapper,
        private readonly CacheInterface $cache,
        private readonly LoggerInterface $logger
    ){}

    public function execute(string $shortcode): ?LinkDto
    {
        $logContext = ['class_method' => __METHOD__];
        try {
            $linkArray = $this->cache->get($shortcode);
            if (!empty($linkArray)) {
                return LinkDto::fromArray($linkArray);
            }
        } catch (\Throwable $exception) {
            $logContext['exception'] = [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ];
            $this->logger->error($exception->getTraceAsString() ,$logContext);
        }

        $link = $this->linkRepository->findByShortcode($shortcode);
        if (is_null($link)) {
            return null;
        }
        try {
            $this->cache->set($shortcode, $this->linkMapper->toArray($link));
        } catch (\Throwable $exception) {
            $logContext['exception'] = [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ];
            $this->logger->error($exception->getTraceAsString(), $logContext);
        }

        return LinkDto::fromEntity($link);
    }
}
