<?php

declare(strict_types=1);

final class Response
{
    public static function json(mixed $data, int $status = 200): never
    {
        // $status - HTTP-код ответа, например 200 OK, 201 Created, 400 Bad Request, 404 Not Found, 409 Conflict, 422 Unprocessable Entity.
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function empty(int $status): never
    {
        // Для ответов без тела, например 204 No Content после успешного удаления.
        http_response_code($status);
        exit;
    }
}
