<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/config.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$caminho = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$caminho = str_replace(
    '/autoescola/back/index.php',
    '',
    $caminho
);

$rota = '/' . trim($caminho, '/');

try {

    if ($metodo === 'GET' && $rota === '/alunos') {
        $stmt = $pdo->prepare('SELECT * FROM Alunos');
        $stmt->execute();

        echo json_encode($stmt->fetchAll());
        exit;
    }

    if ($metodo === 'POST' && $rota === '/alunos/create') {
        $dados = json_decode(file_get_contents('php://input'), true);

        if (!is_array($dados)) {
            http_response_code(400);
            echo json_encode(['erro' => 'JSON inválido.']);
            exit;
        }

        foreach (['nome', 'email', 'data_nascimento'] as $campo) {
            if (
                !isset($dados[$campo]) ||
                trim((string) $dados[$campo]) === ''
            ) {
                http_response_code(400);
                echo json_encode([
                    'erro' => "Campo obrigatório: $campo"
                ]);
                exit;
            }
        }

        if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['erro' => 'E-mail inválido.']);
            exit;
        }

        $sql = 'INSERT INTO Alunos
                (nome, email, telefone, data_nascimento)
                VALUES
                (:nome, :email, :telefone, :data_nascimento)';

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':nome' => trim($dados['nome']),
            ':email' => trim($dados['email']),
            ':telefone' => !empty($dados['telefone'])
                ? trim($dados['telefone'])
                : null,
            ':data_nascimento' => $dados['data_nascimento']
        ]);

        $id = $pdo->lastInsertId();

        $stmt = $pdo->prepare(
            'SELECT id, nome, email, telefone,
                    data_nascimento, data_cadastro
             FROM Alunos
             WHERE id = :id'
        );

        $stmt->execute([':id' => $id]);

        http_response_code(201);
        echo json_encode([
            'mensagem' => 'Cadastrado com sucesso',
            'aluno' => $stmt->fetch()
        ]);
        exit;
    }

    if ($metodo === 'GET' && $rota === '/instrutores') {
        $stmt = $pdo->prepare('SELECT * FROM Instrutores');
        $stmt->execute();

        echo json_encode($stmt->fetchAll());
        exit;
    }

    if ($metodo === 'POST' && $rota === '/instrutores/create') {
        $dados = json_decode(file_get_contents('php://input'), true);

        if (!is_array($dados)) {
            http_response_code(400);
            echo json_encode(['erro' => 'JSON inválido.']);
            exit;
        }

        foreach (['nome', 'email', 'categoria_cnh'] as $campo) {
            if (
                !isset($dados[$campo]) ||
                trim((string) $dados[$campo]) === ''
            ) {
                http_response_code(400);
                echo json_encode([
                    'erro' => "Campo obrigatório: $campo"
                ]);
                exit;
            }
        }

        if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['erro' => 'E-mail inválido.']);
            exit;
        }

        $sql = 'INSERT INTO Instrutores
                (nome, email, telefone, categoria_cnh)
                VALUES
                (:nome, :email, :telefone, :categoria_cnh)';

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':nome' => trim($dados['nome']),
            ':email' => trim($dados['email']),
            ':telefone' => !empty($dados['telefone'])
                ? trim($dados['telefone'])
                : null,
            ':categoria_cnh' => trim($dados['categoria_cnh'])
        ]);

        http_response_code(201);
        echo json_encode([
            'mensagem' => 'Instrutor cadastrado com sucesso!',
            'id' => $pdo->lastInsertId()
        ]);
        exit;
    }

    http_response_code(404);

    echo json_encode([
        'erro' => 'Rota não encontrada.',
        'rota_recebida' => $rota,
        'metodo' => $metodo
    ]);

} catch (PDOException $e) {

    if ($e->getCode() === '23000') {
        http_response_code(409);

        echo json_encode([
            'erro' => 'E-mail já cadastrado ou registro inválido.'
        ]);
    } else {
        http_response_code(500);

        echo json_encode([
            'erro' => 'Erro ao acessar o banco de dados.',
            'detalhes' => $e->getMessage()
        ]);
    }
}