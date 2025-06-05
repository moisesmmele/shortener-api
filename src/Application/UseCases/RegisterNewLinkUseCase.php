<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Application\UseCases;

use Moises\ShortenerApi\Application\Dtos\LinkDto;
use Moises\ShortenerApi\Application\Mappers\LinkMapper;
use Moises\ShortenerApi\Domain\Factories\LinkFactory;
use Moises\ShortenerApi\Domain\Repositories\LinkRepository;

class RegisterNewLinkUseCase
{
    public function __construct(
        private LinkFactory $linkFactory,
        private LinkRepository $linkRepository,
        private LinkMapper $linkMapper,
    ){}

    public function execute(String $url): ?LinkDto
    {
        $link = $this->linkFactory->create($url);
        $this->linkRepository->save($link);
        return $this->linkMapper->toDto($link);
    }
}
