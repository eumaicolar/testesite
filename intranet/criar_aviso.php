<?php
// Obter dados do formulário
$titulo = $_POST['titulo'] ?? null;
$descricao = $_POST['descricao'] ?? null;
$data_evento = $_POST['data'] ?? null;
$duracao = $_POST['duracao'] ?? null;

// Validação dos campos obrigatórios
if (!$titulo || !$descricao || !$duracao) {
    echo json_encode(['status' => 'error', 'message' => 'Erro: Campos obrigatórios não preenchidos.']);
    exit;
}

// Verificar a validade da data (se fornecida)
if (!empty($data_evento)) {
    $data_evento = date('Y-m-d H:i:s', strtotime($data_evento)); // Converter para formato aceito pelo banco de dados
} else {
    $data_evento = null; // Se não houver data, deixe como null
}

// Calcular a data de expiração do aviso com base na duração
$data_criacao = date('Y-m-d H:i:s');
$data_expiracao = date('Y-m-d H:i:s', strtotime("+{$duracao} hours"));

// Exemplo de inserção no banco de dados
$conn = new mysqli('localhost', 'root', '', 'intranet');

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Erro de conexão: ' . $conn->connect_error]);
    exit;
}

$stmt = $conn->prepare('INSERT INTO avisos (titulo, descricao, data_evento, data_criacao, data_expiracao) VALUES (?, ?, ?, ?, ?)');
$stmt->bind_param('sssss', $titulo, $descricao, $data_evento, $data_criacao, $data_expiracao);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Aviso criado com sucesso!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao criar aviso: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
