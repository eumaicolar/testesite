<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}



$usuario_id = $_SESSION['usuario_id'];

// Conexão com o banco
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "intranet";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// 1. Busca o nome e cargo do usuário logado
$sql = "SELECT nome, cargo FROM usuarios WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $stmt->bind_result($nome, $cargo);
    if ($stmt->fetch()) {
        $bem_vindo_msg = "Bem-vindo, " . htmlspecialchars($nome) . "!";
        $isAdmin = ($cargo === 'administrador');
    } else {
        $bem_vindo_msg = "Usuário não encontrado.";
        $isAdmin = false;
    }
    $stmt->close();
}

// 2. Atualiza o status do usuário logado para 'online'
$sql = "UPDATE usuarios SET status = 'online', ultimo_login = NOW() WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $stmt->close();
}

// 3. Busca a lista de usuários para exibição
$sql = "SELECT usuario_id, nome, status, ultimo_login, setor FROM usuarios";

$result = $conn->query($sql);

$usuarios = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
}
// 3. Busca os usuários por setor para exibição no formulário de criação de grupo
$sql_ti = "SELECT usuario_id, nome FROM usuarios WHERE setor = 'TI'";
$sql_adm = "SELECT usuario_id, nome FROM usuarios WHERE setor = 'Administrativo'";
$sql_professores = "SELECT usuario_id, nome FROM usuarios WHERE setor = 'Professores'";

$result_ti = $conn->query($sql_ti);
$result_adm = $conn->query($sql_adm);
$result_professores = $conn->query($sql_professores);

$usuarios_ti = [];
$usuarios_adm = [];
$usuarios_professores = [];

if ($result_ti->num_rows > 0) {
    while ($row = $result_ti->fetch_assoc()) {
        $usuarios_ti[] = $row;
    }
}

if ($result_adm->num_rows > 0) {
    while ($row = $result_adm->fetch_assoc()) {
        $usuarios_adm[] = $row;
    }
}

if ($result_professores->num_rows > 0) {
    while ($row = $result_professores->fetch_assoc()) {
        $usuarios_professores[] = $row;
    }
}
// 1. Busca os grupos criados no banco de dados
$sql_grupos = "SELECT * FROM grupos"; // Altere para a tabela que você usa para armazenar grupos
$result_grupos = $conn->query($sql_grupos);

$grupos = [];
if ($result_grupos->num_rows > 0) {
    while ($row = $result_grupos->fetch_assoc()) {
        $grupos[] = $row;
    }
} else {
    $grupos = []; // Caso não existam grupos
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="INTRANET.png" type="image/png">
    <link rel="stylesheet" href="styles2.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>Intranet Sinergia</title>
</head>
<body>
<header>
<h1>Bate Papo</h1>
    <p>Intranet Sinergia</p>
</header>
<nav class="navigation">
    <ul class="menu-list">
        <a href="home.php" class="menu-link">Início</a>
        <a href="calendario.php" class="menu-link">Agendamento de Salas</a>
    </ul>

    <<!-- Botão "Grupo" ao lado do menu do perfil -->
<div style="display: flex; align-items: center; gap: 15px;">
    <button class="menu-link btn-group" data-bs-toggle="modal" data-bs-target="#addGroupModalTI">
        <i class="fas fa-users"></i> Grupo TI
    </button>
    <button class="menu-link btn-group" data-bs-toggle="modal" data-bs-target="#addGroupModalADM">
        <i class="fas fa-users"></i> Grupo Administrativo 
    </button>
    <button class="menu-link btn-group" data-bs-toggle="modal" data-bs-target="#addGroupModalProf">
        <i class="fas fa-users"></i> Grupo Professores
    </button>
</div>
        <div class="menu-container">
            <button class="menu-button" aria-haspopup="true" aria-expanded="false">
                <img src="https://cdn-icons-png.flaticon.com/512/1077/1077114.png" alt="Foto de perfil" class="profile-pic">
                <span class="user-name">
                    <?= isset($nome) ? htmlspecialchars($nome) . " (" . htmlspecialchars($cargo) . ")" : "Usuário" ?>
                </span>
            </button>
            <div class="dropdown-menu" aria-hidden="true">
                <?php if ($isAdmin): ?>
                    <a href="#" id="createUserOption" class="dropdown-item" onclick="openModal()">Criar Usuário</a>
                <?php endif; ?>
                <a href="#" class="dropdown-item">Configurações</a>
                <a href="logout.php" class="dropdown-item logout-btn">Sair</a>
            </div>
        </div>
    </div>
</nav>

<main>
<div class="container mt-4">
        <div class="row">
            <?php foreach ($usuarios as $usuario): ?>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($usuario['nome']) ?></h5>
                            <p class="card-text">Status: <?= $usuario['status'] ?></p>
                            <p class="card-text">Último login: <?= $usuario['ultimo_login'] ?></p>
                            <!-- Botão para abrir o modal de chat -->
                            <button class="btn btn-primary" onclick="abrirChat(<?= $usuario['usuario_id'] ?>, '<?= htmlspecialchars($usuario['nome']) ?>')">Chat</button>
                        </div>
                    </div>
                </div>

 <!-- Modal para o chat do usuário -->
<div class="modal fade" id="chatModal<?= $usuario['usuario_id'] ?>" tabindex="-1" aria-labelledby="chatModalLabel<?= $usuario['usuario_id'] ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="chatModalLabel<?= $usuario['usuario_id'] ?>">Chat com <?= htmlspecialchars($usuario['nome']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="chat-box" id="chatBox<?= $usuario['usuario_id'] ?>">
                    <!-- As mensagens do chat serão exibidas aqui -->
                </div>
                <textarea id="chatMessage<?= $usuario['usuario_id'] ?>" class="form-control" rows="3" placeholder="Digite sua mensagem..."></textarea>
                <button class="btn btn-send mt-2" onclick="enviarMensagem(<?= $usuario['usuario_id'] ?>)">Enviar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Criação de Grupo TI -->
<div class="modal fade" id="addGroupModalTI" tabindex="-1" aria-labelledby="addGroupModalLabelTI" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addGroupModalLabelTI">Criar Grupo TI</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulário para criar o grupo TI -->
                <form id="createGroupFormTI">
                    <!-- Lista de usuários TI -->
                    <div class="form-check">
                        <h6>TI</h6>
                        <?php foreach ($usuarios_ti as $usuario): ?>
                            <input class="form-check-input" type="checkbox" value="<?= $usuario['usuario_id'] ?>" id="userTI<?= $usuario['usuario_id'] ?>">
                            <label class="form-check-label" for="userTI<?= $usuario['usuario_id'] ?>">
                                <?= htmlspecialchars($usuario['nome']) ?>
                            </label><br>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-group mt-3">
                        <button type="submit" class="btn btn-primary">Criar Grupo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Criação de Grupo Administrativo -->
<div class="modal fade" id="addGroupModalADM" tabindex="-1" aria-labelledby="addGroupModalLabelADM" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addGroupModalLabelADM">Criar Grupo Administrativo</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulário para criar o grupo Administrativo -->
                <form id="createGroupFormADM">
                    <!-- Lista de usuários Administrativo -->
                    <div class="form-check">
                        <h6>Administrativo</h6>
                        <?php foreach ($usuarios_adm as $usuario): ?>
                            <input class="form-check-input" type="checkbox" value="<?= $usuario['usuario_id'] ?>" id="userADM<?= $usuario['usuario_id'] ?>">
                            <label class="form-check-label" for="userADM<?= $usuario['usuario_id'] ?>">
                                <?= htmlspecialchars($usuario['nome']) ?>
                            </label><br>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-group mt-3">
                    <button type="button" class="btn btn-primary" onclick="criarGrupo()">Criar Grupo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Criação de Grupo Professores -->
<div class="modal fade" id="addGroupModalProf" tabindex="-1" aria-labelledby="addGroupModalLabelProf" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addGroupModalLabelProf">Criar Grupo Professores</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulário para criar o grupo Professores -->
                <form id="createGroupFormProf">
                    <!-- Lista de usuários Professores -->
                    <div class="form-check">
                        <h6>Professores</h6>
                        <?php foreach ($usuarios_professores as $usuario): ?>
                            <input class="form-check-input" type="checkbox" value="<?= $usuario['usuario_id'] ?>" id="userProf<?= $usuario['usuario_id'] ?>">
                            <label class="form-check-label" for="userProf<?= $usuario['usuario_id'] ?>">
                                <?= htmlspecialchars($usuario['nome']) ?>
                            </label><br>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-group mt-3">
                        <button type="submit" class="btn btn-primary">Criar Grupo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="container mt-4">
    <div class="row">
        <h3>Grupos Criados</h3>
        <?php foreach ($grupos as $grupo): ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($grupo['nome_grupo']) ?></h5>
                        <p class="card-text">Membros: <?= implode(', ', $grupo['usuarios']) ?></p>
                        <button class="btn btn-primary" onclick="abrirChatGrupo(<?= $grupo['grupo_id'] ?>)">Chat do Grupo</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
     // Função para abrir o chat modal para o usuário específico
     function abrirChat(usuarioId, nome) {
    var modal = new bootstrap.Modal(document.getElementById('chatModal' + usuarioId));
    modal.show();

    // Busca as mensagens do chat
    fetch('buscar_mensagens.php?destinatario_id=' + usuarioId)
        .then(response => response.json())
        .then(data => {
            if (data.mensagens) {
                var chatBox = document.getElementById('chatBox' + usuarioId);
                chatBox.innerHTML = ''; // Limpa o chat

                // Exibe as mensagens antigas
                data.mensagens.forEach(function(mensagem) {
                    var msgElement = document.createElement('p');
                    if (mensagem.remetente_id == usuarioId) {
                        msgElement.textContent = nome + ": " + mensagem.mensagem;
                        msgElement.classList.add('other');
                    } else {
                        msgElement.textContent = "Você: " + mensagem.mensagem;
                        msgElement.classList.add('you');
                    }
                    chatBox.appendChild(msgElement);
                });

                // Rolagem automática para o final
                chatBox.scrollTop = chatBox.scrollHeight;
            } else {
                alert('Erro ao carregar as mensagens: ' + data.error);
            }
        })
        .catch(error => {
            alert('Erro: ' + error);
        });
}


    function openModal() {
        var modal = new bootstrap.Modal(document.getElementById('createUserModal'));
        modal.show();
    }

    function enviarMensagem(usuarioId) {
    var message = document.getElementById('chatMessage' + usuarioId).value;
    if (message.trim() !== '') {
        // Enviar via AJAX para salvar a mensagem
        fetch('salvar_mensagem.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'destinatario_id=' + usuarioId + '&mensagem=' + encodeURIComponent(message)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                var chatBox = document.getElementById('chatBox' + usuarioId);
                var newMessage = document.createElement('p');
                newMessage.textContent = "Você: " + message;
                newMessage.classList.add('you');
                chatBox.appendChild(newMessage);
                chatBox.scrollTop = chatBox.scrollHeight;  // Rolagem automática para o final
                document.getElementById('chatMessage' + usuarioId).value = ''; // Limpa o campo de texto
            } else {
                alert('Erro ao salvar a mensagem: ' + data.error);
            }
        })
        .catch(error => {
            alert('Erro: ' + error);
        });
    }
}

function criarGrupo() {
    var groupName = document.getElementById('groupName').value;
    var selectedUsers = [];
    var checkboxes = document.querySelectorAll('#createGroupForm input[type="checkbox"]:checked');
    
    checkboxes.forEach(function(checkbox) {
        selectedUsers.push(checkbox.value);
    });

    if (groupName.trim() !== '' && selectedUsers.length > 0) {
        // Envia os dados para o PHP para criar o grupo
        fetch('criar_grupo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ nome_grupo: groupName, usuarios: selectedUsers })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Grupo criado com sucesso!');
                // Agora cria o chat em grupo
                abrirChatGrupo(selectedUsers);
                // Fechar o modal ou limpar o formulário
                document.getElementById('createGroupForm').reset();
                var modal = new bootstrap.Modal(document.getElementById('modalCreateGroup'));
                modal.hide();
            } else {
                alert('Erro ao criar grupo: ' + data.message);
            }
        })
        .catch(error => {
            alert('Erro: ' + error);
        });
    } else {
        alert("Por favor, preencha todos os campos corretamente.");
    }
}


function abrirChatGrupo(grupoId) {
    // Lógica para abrir o chat do grupo
    var modal = new bootstrap.Modal(document.getElementById('chatModal' + grupoId));
    modal.show();

    // Carregar mensagens do grupo via AJAX (ajuste conforme sua lógica de mensagens em grupo)
    fetch('buscar_mensagens_grupo.php?grupo_id=' + grupoId)
        .then(response => response.json())
        .then(data => {
            var chatBox = document.getElementById('chatBox' + grupoId);
            chatBox.innerHTML = '';
            data.mensagens.forEach(function(mensagem) {
                var msgElement = document.createElement('p');
                msgElement.textContent = mensagem.remetente + ": " + mensagem.mensagem;
                chatBox.appendChild(msgElement);
            });
            chatBox.scrollTop = chatBox.scrollHeight;
        })
        .catch(error => {
            alert('Erro: ' + error);
        });
}


function enviarMensagemGrupo() {
    var message = document.getElementById('chatMessageGroup').value;
    if (message.trim() !== '') {
        var chatBox = document.getElementById('chatBoxGroup');
        var newMessage = document.createElement('p');
        newMessage.textContent = "Você: " + message;
        newMessage.classList.add('you');
        chatBox.appendChild(newMessage);
        chatBox.scrollTop = chatBox.scrollHeight; // Rolagem automática para o final
        document.getElementById('chatMessageGroup').value = ''; // Limpa o campo de texto
    }
}
</script>
</body>
</html>
