<?php
// Central config + DB + simple auth (SANS JWT)
// Auth: X-API-KEY header

header('Content-Type: application/json');

session_start();

$DB_HOST = 'localhost';
$DB_NAME = 'decoration_shop';
$DB_USER = 'root';
$DB_PASS = '';

$API_KEY = 'CHANGE_ME'; // <-- changer

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";

$pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

function unauthorized(): void {
    http_response_code(401);
    echo json_encode(['message' => 'UNAUTHORIZED']);
    exit;
}

function require_api_key(string $apiKey): void {
    $key = $_SERVER['HTTP_X_API_KEY'] ?? '';
    if (!$key || !hash_equals($apiKey, $key)) {
        unauthorized();
    }
}

// Auth optionnelle : si X-API-KEY n’est pas fourni, les endpoints restent accessibles.
// (Pour activer réellement la sécurité, mets la clé dans le header X-API-KEY)
$providedKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if ($providedKey !== '' && hash_equals($API_KEY, $providedKey) === false) {
    unauthorized();
}


