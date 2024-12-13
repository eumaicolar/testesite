<?php
session_start(); // Se você estiver usando sessões para autenticar o usuário
// Configuração da conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "intranet";

// Criando a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificando a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Exemplo de verificação de sessão para obter o cargo do usuário logado
if (isset($_SESSION['usuario_cargo'])) {
    $cargo = $_SESSION['usuario_cargo'];
} else {
    echo "Usuário não autenticado.";
    exit;
}

// Definindo um array de permissões
$permissoes = ['administrador', 'supervisor', 'operador']; // Exemplo

// Verifique se a requisição é GET e contém o id do aviso
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $aviso_id = $_GET['id'];
    
    // Preparar a consulta para buscar o aviso no banco
    $sql = "SELECT titulo, conteudo FROM avisos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    // Verifique se a preparação foi bem-sucedida
    if ($stmt === false) {
        echo "Erro ao preparar a consulta.";
        exit;
    }

    // Vincular o parâmetro e executar a consulta
    $stmt->bind_param("i", $aviso_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $aviso = $result->fetch_assoc();

    if (!$aviso) {
        echo "Aviso não encontrado.";
        exit;
    }

    // Verifica permissões
    if (!in_array($cargo, $permissoes)) {
        echo "Você não tem permissão para editar este aviso.";
        exit;
    }

    // Exibir o formulário de edição
    ?>
    <h1>Editar Aviso</h1>
    <form method="POST" action="editar_aviso.php">
        <input type="hidden" name="id" value="<?= htmlspecialchars($aviso_id) ?>">
        <label>Título:</label>
        <input type="text" name="titulo" value="<?= htmlspecialchars($aviso['titulo']) ?>" required>
        <label>Conteúdo:</label>
        <textarea name="conteudo" required><?= htmlspecialchars($aviso['conteudo']) ?></textarea>
        <button type="submit">Salvar</button>
    </form>
    <?php
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifique permissões antes de processar o formulário
    if (!in_array($cargo, $permissoes)) {
        echo "Você não tem permissão para editar este aviso.";
        exit;
    }

    // Recuperar os dados do formulário
    $aviso_id = $_POST['id'];
    $titulo = $_POST['titulo'];
    $conteudo = $_POST['conteudo'];

    // Preparar a consulta para atualizar o aviso
    $sql = "UPDATE avisos SET titulo = ?, conteudo = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    // Verifique se a preparação foi bem-sucedida
    if ($stmt === false) {
        echo "Erro ao preparar a consulta.";
        exit;
    }

    // Vincular os parâmetros e executar a consulta
    $stmt->bind_param("ssi", $titulo, $conteudo, $aviso_id);

    if ($stmt->execute()) {
        echo "Aviso atualizado com sucesso.";
        header("Location: home.php"); // Redirecionar para a página principal
        exit;
    } else {
        echo "Erro ao atualizar o aviso.";
    }
}
?>
