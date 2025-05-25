<?php

namespace Moises\ShortenerApi\Infrastructure\Controllers;

use Laminas\Diactoros\Response\RedirectResponse;
use Moises\ShortenerApi\Application\Contracts\UseCaseFactoryInterface;
use Moises\ShortenerApi\Application\UseCases\RegisterNewClickUseCase;
use Moises\ShortenerApi\Application\UseCases\ResolveShortenedLinkUseCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ClickController
{
    private UseCaseFactoryInterface $useCaseFactory;
    public function __construct(UseCaseFactoryInterface $useCaseFactory)
    {
        $this->useCaseFactory = $useCaseFactory;
    }
    public function click(RequestInterface $request): ResponseInterface
    {
        /** @var ResolveShortenedLinkUseCase $resolveShortenedLinkUseCase */
        $resolveShortenedLinkUseCase = $this->useCaseFactory->create(ResolveShortenedLinkUseCase::class);

        /** @var RegisterNewClickUseCase $registerNewClickUseCase */
        $registerNewClickUseCase = $this->useCaseFactory->create(RegisterNewClickUseCase::class);

        $shortcode = str_replace('/', '', $request->getUri()->getPath());
        $sourceAddress = $request->getServerParams()['REMOTE_ADDR'];
        $referrerAddress = $request->getHeaderLine('Referer');

        $linkDto = $resolveShortenedLinkUseCase
            ->execute(shortcode: $shortcode);

        $registerNewClickUseCase
            ->execute(linkDto: $linkDto, sourceAddress: $sourceAddress, referrerAddress: $referrerAddress);

        return new RedirectResponse($linkDto->getLongUrl());
    }
}