<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'Usuário não está logado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$destinatario_id = $_GET['destinatario_id'];

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

// Busca as mensagens entre o usuário logado e o destinatário
$sql = "SELECT remetente_id, destinatario_id, mensagem, data_envio FROM mensagens 
        WHERE (remetente_id = ? AND destinatario_id = ?) 
        OR (remetente_id = ? AND destinatario_id = ?) 
        ORDER BY data_envio ASC";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("iiii", $usuario_id, $destinatario_id, $destinatario_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $mensagens = [];
    while ($row = $result->fetch_assoc()) {
        $mensagens[] = $row;
    }
    
    echo json_encode(['mensagens' => $mensagens]);
    $stmt->close();
} else {
    echo json_encode(['error' => 'Erro ao buscar mensagens']);
}

$conn->close();
?>
