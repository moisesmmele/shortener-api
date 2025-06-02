<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Application\UseCases;

use Moises\ShortenerApi\Application\Dtos\LinkDto;
use Moises\ShortenerApi\Domain\Factories\LinkFactory;
use Moises\ShortenerApi\Domain\Repositories\LinkRepository;

class RegisterNewLinkUseCase
{
    private LinkRepository $linkRepository;
    private LinkFactory $shortenerService;
    public function __construct(LinkFactory $shortenerService, LinkRepository $linkRepository)
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
