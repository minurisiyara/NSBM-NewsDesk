<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

// Project root: NSBM-NewsDesk/
$projectRoot = dirname(__DIR__, 2);

// Load environment variables from the project's .env file
$dotenv = Dotenv::createImmutable($projectRoot);
$dotenv->safeLoad();

// Check that the required variables exist
$dotenv->required([
    'DB_HOST',
    'DB_PORT',
    'DB_NAME',
    'DB_USER',
    'DB_PASS'
])->notEmpty();

$host = $_ENV['DB_HOST'];
$port = $_ENV['DB_PORT'];
$dbname = $_ENV['DB_NAME'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());

    http_response_code(500);
    exit('A database connection error occurred.');
}
