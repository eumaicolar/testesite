<?php
// Conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "intranet";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

// Busca todos os grupos
$sql = "SELECT id, nome FROM grupos";
$result = $conn->query($sql);

$grupos = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $grupos[] = $row;
    }
}

$conn->close();

// Retorna os grupos em formato JSON
echo json_encode(['grupos' => $grupos]);
?>
