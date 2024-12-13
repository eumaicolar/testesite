<?php
session_start();
require_once 'config.php'; // Inclua seu arquivo de configuração com a conexão ao banco

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'Usuário não logado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id']; // Usuário logado
$destinatario_id = $_GET['usuario_id'] ?? null; // ID do destinatário, ou null se não passado

// Verifica se o parâmetro destinatario_id foi passado via GET
if (!$destinatario_id) {
    echo json_encode(['error' => 'Destinatário não fornecido']);
    exit;
}

// Verifica se a conexão com o banco foi estabelecida
if ($conn->connect_error) {
    echo json_encode(['error' => 'Erro de conexão: ' . $conn->connect_error]);
    exit;
}

// Função para carregar as mensagens de um chat
function carregarMensagens($usuario_id, $destinatario_id) {
    global $conn;

    // Consulta para pegar as mensagens entre o usuário logado e o destinatário
    $sql = "SELECT m.mensagem, m.data_envio, u.nome
            FROM mensagens m
            JOIN usuarios u ON m.usuario_id = u.usuario_id
            WHERE (m.destinatario_id = ? AND m.usuario_id = ?) 
               OR (m.destinatario_id = ? AND m.usuario_id = ?)
            ORDER BY m.data_envio ASC";

    // Prepara a consulta SQL
    if ($stmt = $conn->prepare($sql)) {
        // Bind dos parâmetros
        $stmt->bind_param("iiii", $usuario_id, $destinatario_id, $destinatario_id, $usuario_id);
        
        // Executa a consulta
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Verifica se há resultados
        $mensagens = [];
        while ($row = $result->fetch_assoc()) {
            $mensagens[] = $row;
        }

        // Fecha a instrução
        $stmt->close();

        return $mensagens;
    } else {
        // Caso a preparação da consulta falhe
        return ['error' => 'Erro na consulta SQL'];
    }
}

// Carregar as mensagens
$mensagens = carregarMensagens($usuario_id, $destinatario_id);

// Verifica se existem mensagens e formata a resposta
if (empty($mensagens)) {
    $mensagens = []; // Devolve um array vazio quando não houver mensagens
}

// Retorna as mensagens em formato JSON
header('Content-Type: application/json');
echo json_encode(['mensagens' => $mensagens]);

// Fecha a conexão com o banco de dados
$conn->close();
?>
