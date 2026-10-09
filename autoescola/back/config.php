<?php

header('Content-Type: application/json; charset=utf-8');

$host = 'localhost';
$dbname = 'autoescola';
$user = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'erro' => 'Erro ao conectar ao banco de dados.'
    ]);
    exit;
}