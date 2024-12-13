<?php
// Configuração da conexão com o banco de dados
$servername = "localhost"; // ou o endereço do seu servidor
$username = "root"; // usuário do banco de dados
$password = ""; // senha do banco de dados
$dbname = "intranet"; // nome do banco de dados

// Cria a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica se a conexão foi bem-sucedida
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Prepara a query para buscar os eventos
$sql = "SELECT title, start, end FROM reservas"; // Substitua 'reservas' pela sua tabela de eventos, se necessário
$result = $conn->query($sql);

// Cria um array para armazenar os eventos
$events = [];

// Verifica se há eventos
if ($result->num_rows > 0) {
    // Percorre os resultados e adiciona ao array
    while($row = $result->fetch_assoc()) {
        $events[] = [
            'title' => $row['title'],
            'start' => $row['start'],
            'end' => $row['end']
        ];
    }
}

// Retorna os eventos em formato JSON
echo json_encode($events);

// Fecha a conexão
$conn->close();
?>
