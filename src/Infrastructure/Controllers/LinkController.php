<?php

namespace Moises\ShortenerApi\Infrastructure\Controllers;

use Laminas\Diactoros\Response\JsonResponse;
use Moises\ShortenerApi\Application\Contracts\UseCaseFactoryInterface;
use Moises\ShortenerApi\Application\UseCases\RegisterNewLinkUseCase;
use Psr\Http\Message\ServerRequestInterface;

class LinkController
{
    private $useCaseFactory;
    public function __construct(UseCaseFactoryInterface $useCaseFactory)
    {
        $this->useCaseFactory = $useCaseFactory;
    }

    public function create(ServerRequestInterface $request): JsonResponse
    {
        /** @var RegisterNewLinkUseCase $registerNewLinkUseCase */
        $registerNewLinkUseCase = $this->useCaseFactory->create(RegisterNewLinkUseCase::class);

        $data = $request->getParsedBody();
        $url = $data['url'];

        $linkDto = $registerNewLinkUseCase->execute($url);
        $responseBody = [
            'message' => 'success',
            'link' => [
                'id' => $linkDto->getId(),
                'url' => $linkDto->getLongUrl(),
                'shortCode' => $linkDto->getShortCode(),
            ]
        ];
        return new JsonResponse($responseBody);
    }
}