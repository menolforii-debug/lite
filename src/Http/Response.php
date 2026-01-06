<?php

declare(strict_types=1);

namespace LiteCMS\Http;

final class Response
{
    public static function jsonSuccess(array $data = []): void
    {
        self::sendJson(['success' => true, 'data' => $data, 'error' => null]);
    }

    public static function jsonError(string $message, int $status = 400): void
    {
        http_response_code($status);
        self::sendJson(['success' => false, 'data' => null, 'error' => $message]);
    }

    private static function sendJson(array $payload): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
