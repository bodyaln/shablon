<?php

declare(strict_types=1);
require_once __DIR__ . '/Response.php';

const DB_HOST = '127.0.0.1';
const DB_PORT = 3306;
const DB_NAME = 'mydatabase';
const DB_USER = 'root';
const DB_PASS = '';
const DB_CHARSET = 'utf8mb4';

function connectDatabase(
    string $hostname,
    int $port,
    string $database,
    string $username,
    string $password
): PDO {
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $hostname,
        $port,
        $database,
        DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        return new PDO($dsn, $username, $password, $options);
    } catch (PDOException $exception) {
        Response::json(['error' => 'Database connection failed'], 500);
    }
}
