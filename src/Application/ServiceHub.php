<?php

namespace Moises\ShortenerApi\Application;

use Moises\ShortenerApi\Domain\Services\ShortenerService;
use Moises\ShortenerApi\Domain\Services\TrackerService;
use Moises\ShortenerApi\Domain\Repositories\ClickRepository;
use Moises\ShortenerApi\Domain\Repositories\LinkRepository;
use Moises\ShortenerApi\Infrastructure\Repositories\Pdo\PdoClickRepository;
use Moises\ShortenerApi\Infrastructure\Repositories\Pdo\PdoLinkRepository;
use Symfony\Component\HttpFoundation\Request;

class ServiceHub
{
    private LinkRepository $linkRepository;
    private ClickRepository $clickRepository;
    public function __construct()
    {
        $this->linkRepository = new PdoLinkRepository();
        $this->clickRepository = new PdoClickRepository();
    }

    public function handle(Request $request)
    {
        $shortcode = str_replace('/', '', $request->getPathInfo());
        try {
            $link = $this->linkRepository->findByShortcode($shortcode);
            if (!$link) {
                $message = "No link found for the provided shortcode: $shortcode";
                $this->notFound($message);
                return;
            }
            $tracker = new TrackerService();
            $clientIp = $request->getClientIp();
            if (empty($request->headers->get('referer'))) {
                $referrer = 'Direct Access';
            } else {
                $referrer = $request->headers->get('referer');
            }
            $click = $tracker->registerClick($link, $clientIp, $referrer);
            $this->clickRepository->save($click);
            $url = $link->getLongUrl();
            header('Location: ' . $url);
        } catch (\Exception $exception) {
            http_response_code(500);
            header('Content-Type: application/json');
            error_log("ERROR: ".PHP_EOL."stackTrace:".PHP_EOL.$exception->getMessage());
            echo json_encode([
                'status' => 'Internal Server Error',
                'status_code' => '500',
                'message' => $exception->getMessage(),
            ]);
        }

    }

    public function register(Request $request)
    {
        $body = json_decode($request->getContent(), true);
        $shortener = new ShortenerService();
        $link = $shortener->generate($body['url']);
        try {
            $this->linkRepository->save($link);
            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => "OK",
                'status_code' => 200,
                'message' => "Link register successfully",
                'shortcode' => $link->getShortcode()
            ]);
        } catch (\Exception $exception) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => "Internal Server Error",
                'status_code' => 500,
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function notFound($message)
    {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => "Not Found",
            'status_code' => 404,
            'message' => $message
        ]);
    }

    public function getAllLinks()
    {
        try {
            $links = $this->linkRepository->getAll();
            $data = [];
            foreach ($links as $link) {
                $data[] = [
                    'id' => $link->getId(),
                    'long_url' => $link->getLongUrl(),
                    'shortcode' => $link->getShortCode(),
                ];
            }
            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => "OK",
                'status_code' => 200,
                'message' => 'links retrieved successfully',
                'data' => $data
            ]);
        } catch (\Exception $exception) {
            error_log('ERROR: ' . $exception->getMessage());
            error_log("stackTrace:".PHP_EOL.$exception->getTraceAsString());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => "Internal Server Error",
                'status_code' => 500,
                'location' => 'getAllLinks',
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function getLinkClicks(Request $request, String|array $shortCode)
    {
        if (is_array($shortCode)){
            $shortCode = $shortCode['shortcode'];
        };

        try {
            $link = $this->linkRepository->findByShortcode($shortCode);
            if (!$link) {
                $message = "No link found for the provided shortcode: $shortCode";
                $this->notFound($message);
            }
            $repository = new PdoClickRepository();
            $clicks = $repository->findByLink($link);
            $data = [];
            foreach ($clicks as $click) {
                $data[] = [
                    'id' => $click->getId(),
                    'utc_timestamp' => $click->getUtcTimestamp(),
                    'source_ip' => $click->getSourceIp(),
                    'referrer' => $click->getReferrer(),
                ];
            };
            http_response_code(200);
            header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'OK',
                    'status_code' => 200,
                    'message' => "Clicks retrieved successfully",
                    'shortcode' => $shortCode,
                    'clicks' => $data
                ]);
        } catch (\Exception $exception) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'Internal Server Error',
                'status_code' => '500',
                'message' => $exception->getMessage()
            ]);
        }
    }
}