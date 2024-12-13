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

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verifica se os campos foram preenchidos
    if (empty($_POST['nome']) || empty($_POST['email']) || empty($_POST['senha']) || empty($_POST['cargo']) || empty($_POST['setor'])) {
        die("Erro: Todos os campos são obrigatórios.");
    }

    // Recebe os dados do formulário
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Criptografa a senha
    $cargo = $_POST['cargo'];
    $setor = $_POST['setor']; // Recebe o setor

    // Verifica se o e-mail já está cadastrado
    $sql_check = "SELECT COUNT(*) FROM usuarios WHERE email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count > 0) {
        die("Erro: Este e-mail já está cadastrado.");
    }

    // Prepara a query para inserir os dados
    $sql = "INSERT INTO usuarios (nome, email, senha, cargo, setor) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("sssss", $nome, $email, $senha, $cargo, $setor); // Liga os parâmetros
        if ($stmt->execute()) {
            echo "Usuário criado com sucesso!";
        } else {
            echo "Erro ao inserir usuário: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Erro na preparação da consulta: " . $conn->error;
    }
} else {
    echo "Erro: O formulário não foi enviado corretamente.";
}

// Fecha a conexão
$conn->close();
?>
