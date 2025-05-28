<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Application\UseCases;

use Moises\ShortenerApi\Domain\Repositories\LinkRepository;
use Moises\ShortenerApi\Domain\Services\ShortenerService;
use Moises\ShortenerApi\Application\Dtos\LinkDto;

class RegisterNewLinkUseCase
{
    private LinkRepository $linkRepository;
    private ShortenerService $shortenerService;
    public function __construct(ShortenerService $shortenerService, LinkRepository $linkRepository)
    {
        $this->linkRepository = $linkRepository;
        $this->shortenerService = $shortenerService;
    }

    public function execute(String $url): ?LinkDto
    {
        $link = $this->shortenerService->generateShortLink($url);
        $this->linkRepository->save($link);
        return LinkDto::fromEntity($link);
    }
}
