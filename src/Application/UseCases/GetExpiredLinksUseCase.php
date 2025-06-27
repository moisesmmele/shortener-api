<?php

namespace Moises\ShortenerApi\Application\UseCases;

use Moises\ShortenerApi\Application\Dtos\LinkDto;
use Moises\ShortenerApi\Application\Mappers\LinkMapper;

class GetExpiredLinksUseCase
{
    public function __construct(
        private LinkMapper $linkMapper,
    )
    {}

    /**
     * @param LinkDto[] $linksDto
     * @return LinkDto[]
     */
    public function execute(array $linksDto): array
    {
        $expired = [];
        foreach ($linksDto as $linkDto) {
            $link = $this->linkMapper->FromDto($linkDto);
            if (!$link->isValid()) {
                $linkDto = $this->linkMapper->ToDto($link);
                $expired[] = $linkDto;
            }
        }
        return $expired;
    }
}