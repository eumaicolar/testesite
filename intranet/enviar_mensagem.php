<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    die("Erro: Usuário não logado.");
}

$usuario_id = $_SESSION['usuario_id'];
$nome_usuario = $_SESSION['usuario_nome']; 

$conn = new mysqli("localhost", "root", "", "intranet");

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Verifica o cargo do usuário
$stmt = $conn->prepare("SELECT cargo FROM usuarios WHERE usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->bind_result($cargo);
$stmt->fetch();
$stmt->close();

// Exibe o formulário somente se for administrador ou supervisor
if ($cargo === 'administrador' || $cargo === 'supervisor') {
    echo '
        <form method="POST" action="" enctype="multipart/form-data">
            <label for="mensagem">Digite sua mensagem:</label>
            <textarea id="mensagem" name="mensagem" rows="4" cols="50" required></textarea><br><br>
            <label for="arquivo">Enviar arquivo:</label>
            <input type="file" id="arquivo" name="arquivo" accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx" /><br><br>
            <button type="submit">Enviar</button>
        </form>
    ';
} else {
    echo "Você não tem permissão para enviar mensagens.";
}

$conn->close();

// Processa o envio da mensagem e arquivo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensagem = $_POST['mensagem'];
    $arquivo = $_FILES['arquivo'];

    // Processar o arquivo, se existir
    if ($arquivo['error'] === UPLOAD_ERR_OK) {
        $diretorio = "uploads/"; // Diretório onde os arquivos serão salvos
        $arquivo_nome = basename($arquivo['name']);
        $caminho_arquivo = $diretorio . $arquivo_nome;

        // Verifica o tipo de arquivo e move para o diretório
        if (move_uploaded_file($arquivo['tmp_name'], $caminho_arquivo)) {
            echo "Arquivo enviado com sucesso. ";
        } else {
            echo "Erro ao enviar arquivo. ";
        }
    } else {
        echo "Nenhum arquivo enviado ou houve um erro no envio do arquivo. ";
    }

    // Conexão novamente para inserir dados no banco
    $conn = new mysqli("localhost", "root", "", "intranet");

    if ($conn->connect_error) {
        die("Conexão falhou: " . $conn->connect_error);
    }

    // Insere a mensagem e o arquivo no banco de dados
    if ($cargo === 'administrador' || $cargo === 'supervisor') {
        $stmt = $conn->prepare("INSERT INTO chat (nome, mensagem, arquivo, data_envio) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $nome_usuario, $mensagem, $caminho_arquivo);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo "Mensagem e arquivo enviados com sucesso.";
        } else {
            echo "Erro ao enviar mensagem e arquivo.";
        }
        $stmt->close();
    }

    $conn->close();
}

// Exibe as mensagens e arquivos do chat
$conn = new mysqli("localhost", "root", "", "intranet");

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Buscar mensagens
$query = "SELECT nome, mensagem, arquivo, data_envio FROM chat ORDER BY data_envio DESC";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div>";
        echo "<strong>" . htmlspecialchars($row['nome']) . "</strong>: " . htmlspecialchars($row['mensagem']) . "<br>";
        
        // Verifica se existe um arquivo
        if ($row['arquivo']) {
            $arquivo = $row['arquivo'];
            $extensao = pathinfo($arquivo, PATHINFO_EXTENSION);

            // Exibe o arquivo de acordo com o tipo
            if (in_array($extensao, ['jpg', 'jpeg', 'png', 'gif'])) {
                // Exibe imagem
                echo "<img src='$arquivo' alt='Imagem enviada' width='200'><br>";
            } elseif ($extensao === 'pdf') {
                // Exibe link para PDF
                echo "<a href='$arquivo' target='_blank'>Abrir PDF</a><br>";
            } else {
                // Exibe link para outros arquivos
                echo "<a href='$arquivo' download>Baixar Arquivo</a><br>";
            }
        }

        echo "<hr></div>";
    }
} else {
    echo "Nenhuma mensagem encontrada.";
}

$conn->close();
?>
