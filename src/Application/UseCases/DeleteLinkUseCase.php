<?php

namespace Moises\ShortenerApi\Application\UseCases;

use Moises\ShortenerApi\Application\Dtos\LinkDto;
use Moises\ShortenerApi\Application\Mappers\LinkMapper;
use Moises\ShortenerApi\Domain\Repositories\LinkRepository;

class DeleteLinkUseCase
{
    public function __construct(
        private LinkMapper $linkMapper,
        private LinkRepository $linkRepository,
    ){}

    /* @param LinkDto[] $expiredLinksDto */
    public function execute(array $expiredLinksDto): void
    {
        foreach ($expiredLinksDto as $linkDto) {
            $link = $this->linkMapper->fromDto($linkDto);
            $this->linkRepository->delete($link);
        }
    }
}