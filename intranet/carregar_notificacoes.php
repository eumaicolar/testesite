<?php
session_start();
$usuario_id = $_SESSION['usuario_id'];
$conn = new mysqli("localhost", "root", "", "intranet");

$sql = "SELECT mensagem, data FROM notificacoes WHERE usuario_id = ? AND lida = FALSE";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$notificacoes = [];
while ($row = $result->fetch_assoc()) {
    $notificacoes[] = $row;
}
$stmt->close();
$conn->close();
echo json_encode($notificacoes);
?>
