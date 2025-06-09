<?php

namespace Moises\ShortenerApi\Application\UseCases;

use Moises\ShortenerApi\Application\Dtos\LinkDto;
use Moises\ShortenerApi\Domain\Repositories\LinkRepository;

class CollectAllLinksUseCase
{
    private LinkRepository $linkRepository;

    public function __construct(LinkRepository $linkRepository)
    {
        $this->linkRepository = $linkRepository;
    }

    public function execute(): array
    {
        $links = $this->linkRepository->getAll();
        $linkDtos = [];
        foreach ($links as $link) {
            $linkDtos[] = LinkDto::fromEntity($link);
        }
        return $linkDtos;
    }
}