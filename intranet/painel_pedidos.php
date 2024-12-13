<?php
// Configuração da conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "intranet";

// Cria a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica se a conexão foi bem-sucedida
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Função para inserir um novo pedido
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['descricao'])) {
    $usuario_id = 1; // ID do usuário que faz o pedido, altere conforme necessário
    $descricao = $_POST['descricao'];

    // Prepara a query para inserir os dados
    $sql = "INSERT INTO pedidos (usuario_id, descricao, status) VALUES (?, ?, 'Pendente')";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("is", $usuario_id, $descricao);
        if ($stmt->execute()) {
            echo json_encode(["message" => "Pedido criado com sucesso!"]);
        } else {
            echo json_encode(["message" => "Erro ao criar o pedido: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["message" => "Erro na preparação da consulta: " . $conn->error]);
    }
}

// Função para listar pedidos pendentes
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $sql = "SELECT * FROM pedidos WHERE status = 'Pendente'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $pedidos = [];
        while ($row = $result->fetch_assoc()) {
            $pedidos[] = $row;
        }
        echo json_encode($pedidos);
    } else {
        echo json_encode([]);
    }
}

// Função para atualizar status de um pedido (aprovar ou reprovar)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id']) && isset($_POST['status'])) {
    $pedido_id = $_POST['id'];
    $status = $_POST['status'];

    // Atualiza o status do pedido
    $sql = "UPDATE pedidos SET status = ? WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("si", $status, $pedido_id);
        if ($stmt->execute()) {
            echo json_encode(["message" => "Pedido " . $status . " com sucesso!"]);
        } else {
            echo json_encode(["message" => "Erro ao atualizar o status: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["message" => "Erro na preparação da consulta: " . $conn->error]);
    }
}

// Fecha a conexão
$conn->close();
?>
