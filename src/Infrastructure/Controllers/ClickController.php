<?php

namespace Moises\ShortenerApi\Infrastructure\Controllers;

use Moises\ShortenerApi\Application\Contracts\UseCaseFactoryInterface;
use Moises\ShortenerApi\Application\UseCases\RegisterNewClickUseCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;

class ClickController
{
    private UseCaseFactoryInterface $useCaseFactory;
    public function __construct(UseCaseFactoryInterface $useCaseFactory)
    {
        $this->useCaseFactory = $useCaseFactory;
    }
    public function create(RequestInterface $request): ResponseInterface
    {
        $useCase = $this->useCaseFactory->create(RegisterNewClickUseCase::class);
        $useCase->execute($request->getUri());
        return new JsonResponse(["status" => "Success"])->withStatus(201);
    }
}