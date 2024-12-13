<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'Usuário não está logado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$destinatario_id = $_POST['destinatario_id'];
$mensagem = $_POST['mensagem'];

// Conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "intranet";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['error' => 'Erro de conexão com o banco']);
    exit;
}

// Insere a mensagem no banco de dados
$sql = "INSERT INTO mensagens (remetente_id, destinatario_id, mensagem, data_envio) 
        VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("iis", $usuario_id, $destinatario_id, $mensagem);
    $stmt->execute();
    echo json_encode(['success' => true]);
    $stmt->close();
} else {
    echo json_encode(['error' => 'Erro ao salvar mensagem']);
}

$conn->close();
?>
