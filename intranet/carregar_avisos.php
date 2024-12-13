<?php
// Conexão com o banco de dados
$conn = new mysqli("localhost", "root", "", "intranet");

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Busca todos os avisos
$sql = "SELECT titulo, descricao, data FROM avisos ORDER BY data DESC";
$result = $conn->query($sql);

$avisos = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $avisos[] = $row;
    }
}

$conn->close();

echo json_encode($avisos);
?>
