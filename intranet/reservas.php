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

// Verifica se os dados do evento foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recebe os dados do evento (titulo, start, end)
    $title = isset($_POST['title']) ? $_POST['title'] : '';  // Título do evento
    $start = isset($_POST['start']) ? $_POST['start'] : '';  // Data de início
    $end = isset($_POST['end']) ? $_POST['end'] : '';  // Data de término

    // Validação básica para garantir que todos os dados sejam fornecidos
    if (empty($title) || empty($start) || empty($end)) {
        echo json_encode(['error' => 'Todos os campos são obrigatórios.']);
        exit;
    }

    // Prepara a query para inserir os dados do evento na tabela de reservas
    $sql = "INSERT INTO reservas (title, start, end) VALUES (?, ?, ?)";

    // Usa prepared statement para evitar SQL injection
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sss", $title, $start, $end); // Liga os parâmetros
        if ($stmt->execute()) {
            // Retorna sucesso após inserir o evento
            echo json_encode(['success' => 'Evento adicionado com sucesso!']);
        } else {
            // Se houver erro na execução, retorna mensagem de erro
            echo json_encode(['error' => 'Erro ao adicionar o evento: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        // Se não for possível preparar a consulta, retorna erro
        echo json_encode(['error' => 'Erro na preparação da consulta: ' . $conn->error]);
    }

    // Fecha a conexão
    $conn->close();
}
?>
