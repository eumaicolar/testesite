<?php
session_start();
include 'conexao.php'; // Inclua a conexão com o banco de dados

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Recebe os dados do grupo via POST
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['nome_grupo']) && isset($data['usuarios'])) {
    $nome_grupo = $data['nome_grupo'];
    $usuarios = $data['usuarios'];

    // Cria o grupo no banco de dados
    $sql = "INSERT INTO grupos (nome_grupo) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nome_grupo);
    $stmt->execute();
    $grupo_id = $stmt->insert_id;
    $stmt->close();

    // Associa os usuários ao grupo
    foreach ($usuarios as $usuario_id) {
        $sql_associar = "INSERT INTO grupo_usuarios (grupo_id, usuario_id) VALUES (?, ?)";
        $stmt_associar = $conn->prepare($sql_associar);
        $stmt_associar->bind_param("ii", $grupo_id, $usuario_id);
        $stmt_associar->execute();
        $stmt_associar->close();
    }

    echo json_encode(['success' => true, 'grupo_id' => $grupo_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
}

$conn->close();
?>
