<?php declare(strict_types=1);

namespace Moises\ShortenerApi\Application\UseCases;

use Moises\ShortenerApi\Application\Dtos\LinkDto;
use Moises\ShortenerApi\Domain\Repositories\LinkRepository;

class ResolveShortenedLinkUseCase
{
    private LinkRepository $linkRepository;
    public function __construct(LinkRepository $linkRepository)
    {
        $this->linkRepository = $linkRepository;
    }

    public function execute(string $shortcode): ?LinkDto
    {
        $link = $this->linkRepository->findByShortcode($shortcode);
        if (is_null($link)) {
            return null;
        }
        return LinkDto::fromEntity($link);
    }
}