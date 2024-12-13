<?php
session_start(); // Inicia a sessão

// Verifica se o usuário está logado
if (isset($_SESSION['usuario_id'])) {
    $usuario_id = $_SESSION['usuario_id'];

    // Conexão com o banco
    $conn = new mysqli("localhost", "root", "", "intranet");

    if ($conn->connect_error) {
        die("Conexão falhou: " . $conn->connect_error);
    }

    // Busca o nome, cargo e setor do usuário pelo ID
    $sql = "SELECT nome, cargo, setor FROM usuarios WHERE usuario_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $usuario_id); // Liga o ID como parâmetro
        $stmt->execute();
        $stmt->bind_result($nome, $cargo, $setor); // Liga os resultados às variáveis (incluindo o setor)
        if ($stmt->fetch()) {
            $bem_vindo_msg = "Bem-vindo, " . htmlspecialchars($nome) . " (" . htmlspecialchars($setor) . ")!";
            $isAdmin = ($cargo === 'administrador'); // Define $isAdmin se o cargo for "administrador"
        } else {
            $bem_vindo_msg = "Usuário não encontrado.";
            $isAdmin = false; // Não é administrador por padrão
        }
        $stmt->close();
    }

    $conn->close();
} else {
    $bem_vindo_msg = "Você não está logado.";
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="INTRANET.png" type="image/png">
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <title>Intranet Sinergia</title>
</head>
<body>
<header>
    <h1>Intranet Sinergia</h1>
</header>
<nav class="navigation">
    <ul class="menu-list">
        <a href="chat_geral.php" class="menu-link">Bate Papo</a>
        <a href="calendario.php" class="menu-link">Agendamento de Salas</a>
        <a href="aprova_pedidos.php" class="menu-link">Aprovação de Pedidos</a>
    </ul>
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
</nav>
<main>
    <div style="display: flex; gap: 20px; margin-top: 20px; align-items: flex-start;">
        <!-- Seção Avisos Importantes -->
        <div style="flex: 1; background-color: white; box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1); border-radius: 12px; padding: 25px; max-width: 450px; font-family: 'Arial', sans-serif;">
            <h3 style="display: flex; align-items: center; gap: 12px; color: #0078d7; font-size: 20px; font-weight: 600;">
                <i class="fas fa-exclamation-triangle" style="font-size: 28px; color: #0078d7;"></i>
                Avisos Importantes
            </h3>
            <?php if (isset($cargo) && ($cargo == 'administrador' || $cargo == 'supervisor')): ?>
                <button aria-label="Criar novo aviso" onclick="openCreateNoticeModal()" class="btn-novo-aviso"
                        style="background-color: #0078d7; color: white; border: none; padding: 14px 24px; font-size: 16px; border-radius: 8px; 
                        cursor: pointer; display: flex; align-items: center; gap: 12px; transition: background-color 0.3s ease; margin-top: 20px; width: 100%;">
                    <i class="fas fa-plus-circle" style="font-size: 20px;"></i> Novo Aviso
                </button>
            <?php endif; ?>
            <ul style="list-style: none; padding: 0; margin-top: 20px;">
                <?php
                $conn = new mysqli("localhost", "root", "", "intranet");
                if ($conn->connect_error) {
                    die("Conexão falhou: " . $conn->connect_error);
                }
                $sql = "SELECT titulo, descricao, data FROM avisos ORDER BY data DESC";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<li style='padding: 12px 0; border-bottom: 1px solid #eee;'>";
                        echo "<strong style='font-size: 16px; color: #333;'>" . htmlspecialchars($row['titulo']) . "</strong>";
                        echo "<p style='font-size: 14px; color: #555; margin: 8px 0;'>" . htmlspecialchars($row['descricao']) . "</p>";
                        echo "<p style='font-size: 12px; color: #777;'>Agendado para " . date("d/m/Y H:i", strtotime($row['data'])) . "</p>";
                        echo "</li>";
                    }
                } else {
                    echo "<li><p style='font-size: 14px; color: #555;'>Nenhum aviso encontrado.</p></li>";
                }
                $conn->close();
                ?>
            </ul>
        </div>

       <!-- Seção de Calendários -->
       <div style="flex: 1; background-color: white; box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1); border-radius: 12px; padding: 25px; font-family: 'Arial', sans-serif;">
            <h3 style="color: #0078d7; font-size: 20px; font-weight: 600;">Calendários 2025</h3>

<!-- Contêiner principal para os calendários e outros conteúdos -->
<div style="margin-top: 20px; display: flex; align-items: flex-start; gap: 20px;">
    <!-- Seção dos calendários -->
    <div style="flex: 1; max-width: 600px;">
        <!-- Botões das abas -->
        <div style="display: flex; gap: 10px; margin-bottom: 20px;">
            <button onclick="showCalendar('academico')" id="academico-btn" style="flex: 1; padding: 12px 20px; background-color: #0078d7; color: white; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; transition: background-color 0.3s;">
                Calendário Acadêmico
            </button>
            <button onclick="showCalendar('letivo')" id="letivo-btn" style="flex: 1; padding: 12px 20px; background-color: #e0e0e0; color: #333; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; transition: background-color 0.3s;">
                Calendário Letivo
            </button>
        </div>

        <!-- Conteúdo das abas -->
        <div id="calendar-academico" style="display: block;">
            <h4 style="font-size: 18px; color: #333; margin-bottom: 15px;">Calendário Acadêmico 2025</h4>
            <embed src="Calendário Acadêmico 2025-1.pdf" type="application/pdf" width="100%" height="350px" style="border: 1px solid #ddd; border-radius: 8px;">
            <a href="Calendário Acadêmico 2025-1.pdf" download style="display: inline-block; margin-top: 15px; text-decoration: none; color: white; background-color: #0078d7; padding: 10px 20px; border-radius: 8px; font-size: 14px;">Baixar PDF</a>
        </div>
        <div id="calendar-letivo" style="display: none;">
            <h4 style="font-size: 18px; color: #333; margin-bottom: 15px;">Calendário Letivo 2025</h4>
            <embed src="Calendario-Letivo.pdf" type="application/pdf" width="100%" height="350px" style="border: 1px solid #ddd; border-radius: 8px;">
            <a href="Calendario-Letivo.pdf" download style="display: inline-block; margin-top: 15px; text-decoration: none; color: white; background-color: #0078d7; padding: 10px 20px; border-radius: 8px; font-size: 14px;">Baixar PDF</a>
        </div>
    </div>

    <!-- Espaço lateral direito (pode conter outros conteúdos ou ser removido) -->
    <div style="flex: 1; max-width: 600px;">

    </div>
</div>

</main>
<div class="chat-container">
    <div class="chat-header">
        Chat Aviso Geral
    </div>
    <div class="chat-messages" id="chatMessages"></div>
    <div class="chat-input">
        <input 
            type="text" 
            id="chatInput" 
            class="chat-input-field" 
            placeholder="Digite sua mensagem..." 
            <?= ($cargo !== 'administrador' && $cargo !== 'supervisor') ? 'disabled' : '' ?>
        />
        
        <!-- Label para o ícone de "Escolher Arquivo" -->
        <label for="chatFileInput" class="file-label">
            <i class="fas fa-paperclip"></i> <!-- Somente o ícone -->
        </label>
        <!-- Input de arquivo escondido -->
        <input 
            type="file" 
            id="chatFileInput" 
            class="chat-file-input"
            <?= ($cargo !== 'administrador' && $cargo !== 'supervisor') ? 'disabled' : '' ?>/><button 
    id="sendButton" 
    class="chat-send-button" 
    <?= ($cargo !== 'administrador' && $cargo !== 'supervisor') ? 'disabled' : '' ?>> Enviar</button>
    </div>
</div>

</div>
</div>
</div>
</main>
<!-- Modal para Criar Aviso -->
<div id="createNoticeModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center; z-index: 9999;">
    <div id="modal-content" style="background-color: white; padding: 30px; border-radius: 12px; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1); max-width: 600px; width: 90%; font-family: 'Arial', sans-serif;">
        <h2 style="color: #0078d7; font-size: 24px; margin-bottom: 20px; text-align: center;">Criar Novo Aviso</h2>
        <form id="createNoticeForm" style="display: flex; flex-direction: column; gap: 20px;">
            <input type="text" name="titulo" placeholder="Título do Aviso" required style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; width: 100%; transition: border-color 0.3s ease-in-out;">
            <textarea name="descricao" placeholder="Descrição do Aviso" rows="5" required style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; width: 100%; transition: border-color 0.3s ease-in-out;"></textarea>
            
            <label style="font-size: 14px; color: #555; font-weight: 500;">Duração do Aviso:</label>
            <select name="duracao" required style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; width: 100%; transition: border-color 0.3s ease-in-out;">
                <option value="1">1 Hora</option>
                <option value="24">1 Dia</option>
                <option value="168">1 Semana</option>
                <option value="720">1 Mês</option>
            </select>
            
            <label style="font-size: 14px; color: #555; font-weight: 500;">Data do Evento:</label>
            <input type="datetime-local" name="data" required style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; width: 100%; transition: border-color 0.3s ease-in-out;">
            
            <div style="margin-top: 20px; display: flex; justify-content: space-between; gap: 15px;">
                <button type="submit" style="background-color: #0078d7; color: white; border: none; padding: 12px 20px; border-radius: 8px; font-size: 16px; cursor: pointer; transition: background-color 0.3s ease;">
                    Salvar
                </button>
                <button type="button" onclick="closeCreateNoticeModal()" style="background-color: #f1f1f1; color: #333; border: none; padding: 12px 20px; border-radius: 8px; font-size: 16px; cursor: pointer; transition: background-color 0.3s ease;">
                    Cancelar
                </button>
            </div>
        </form>
        <!-- Aqui será exibida a mensagem de sucesso ou erro -->
        <div id="notification" style="display: none; padding: 15px; background-color: #4CAF50; color: white; border-radius: 5px; margin-top: 20px; text-align: center;">
            Aviso criado com sucesso!
        </div>
    </div>
</div>

        </form>
    </div>
</div>

<style>
    /* Adicionando um efeito de foco nos inputs */
    input:focus, textarea:focus, select:focus {
        border-color: #0078d7;
        outline: none;
    }

    /* Efeito hover nos botões */
    button:hover {
        background-color: #005fa3;
    }
    button[type="button"]:hover {
        background-color: #e0e0e0;
    }
</style>

</div>
<div id="modal">
    <div id="modal-content">
        <h2 style="font-size: 1.5em; color: var(--blue-dark); margin-bottom: 15px;">Criar Usuário</h2>
        <form action="processa_usuario.php" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
            <input type="text" name="nome" placeholder="Nome" required style="padding: 12px; border: 1px solid #ccc; border-radius: 8px; font-size: 1em;">
            <input type="email" name="email" placeholder="Email" required style="padding: 12px; border: 1px solid #ccc; border-radius: 8px; font-size: 1em;">
            <input type="password" name="senha" placeholder="Senha" required style="padding: 12px; border: 1px solid #ccc; border-radius: 8px; font-size: 1em;">
            <div style="display: flex; gap: 10px;">
                <select name="cargo" required style="flex: 1; padding: 12px; border: 1px solid #ccc; border-radius: 8px; font-size: 1em; background-color: #fff;">
                    <option value="" disabled selected>Selecione o Cargo</option>
                    <option value="administrador">Administrador</option>
                    <option value="coordenacao">Supervisão</option>
                    <option value="financeiro">Diretoria</option>
                    <option value="Comercial">Comercial</option>
                    <option value="marketing">Usuários gerais</option>
                </select>
                <select name="setor" required style="flex: 1; padding: 12px; border: 1px solid #ccc; border-radius: 8px; font-size: 1em; background-color: #fff;">
                    <option value="" disabled selected>Selecione o Setor</option>
                    <option value="financeiro">Financeiro</option>
                    <option value="marketing">Marketing</option>
                    <option value="vendas">Vendas</option>
                    <option value="operacional">Operacional</option>
                    <option value="tecnologia">Tecnologia</option>
                </select>
            </div>
            <div style="display: flex; justify-content: space-between; gap: 10px;">
                <button type="submit" style="flex: 1; padding: 12px; background-color: var(--blue-dark); color: white; border: none; border-radius: 8px; font-size: 1em; cursor: pointer;">Salvar</button>
                <button type="button" onclick="closeModal()" style="flex: 1; padding: 12px; background-color: var(--red); color: white; border: none; border-radius: 8px; font-size: 1em; cursor: pointer;">Cancelar</button>
            </div>
        </form>
    </div>
</div>

        </form>
    </div>
</div>
<script>
document.getElementById('createNoticeForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Evitar envio normal do formulário
    
    var formData = new FormData(this); // Captura os dados do formulário
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'criar_aviso.php', true); // Coloque o caminho correto do seu script PHP
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            var notification = document.getElementById('notification');
            
            if (response.status === 'success') {
                // Fechar o modal
                closeCreateNoticeModal();

                // Exibir a notificação de sucesso
                notification.style.display = 'block';
                
                // Ocultar a notificação após 5 segundos
                setTimeout(function() {
                    notification.style.display = 'none';
                }, 5000);
            } else {
                notification.style.backgroundColor = '#f44336'; // Cor de erro
                notification.innerHTML = 'Erro: ' + response.message; // Exibir mensagem de erro
                notification.style.display = 'block';
                
                // Ocultar a notificação após 5 segundos
                setTimeout(function() {
                    notification.style.display = 'none';
                }, 5000);
            }
        }
    };
    
    xhr.send(formData); // Enviar os dados via AJAX
});

// Função para fechar o modal
function closeCreateNoticeModal() {
    document.getElementById('createNoticeModal').style.display = 'none';
}

  // Garantir que o modal esteja oculto ao carregar a página
  window.onload = function() {
        document.getElementById('createNoticeModal').style.display = 'none';
    };

    // Função para abrir o modal
    function openCreateNoticeModal() {
        document.getElementById('createNoticeModal').style.display = 'flex';
    }

    // Função para fechar o modal
    function closeCreateNoticeModal() {
        document.getElementById('createNoticeModal').style.display = 'none';
    }

    const usuarioNome = "<?php echo isset($nome) ? htmlspecialchars($nome) : ''; ?>";
    const chatMessages = document.getElementById('chatMessages');
    const chatInput = document.getElementById('chatInput');
    const chatSendBtn = document.getElementById('chatSendBtn');
    const sendButton = document.getElementById('sendButton');  // Corrigido para pegar o botão corretamente

    // Função para mostrar a animação de carregamento
    function showLoading() {
        chatMessages.innerHTML = `<div class="loading">Carregando mensagens...</div>`;
    }

    // Função para rolar até a última mensagem
function scrollToBottom() {
    chatMessages.scrollTop = chatMessages.scrollHeight;
}
document.addEventListener("DOMContentLoaded", function () {
    const chatMessagesDiv = document.getElementById("chatMessages");
    const chatInput = document.getElementById("chatInput");
    const sendButton = document.getElementById("sendButton");

    // Função para carregar mensagens
    async function loadMessages() {
    try {
        const response = await fetch("carregar_mensagens.php");
        const messages = await response.json();

        // Limpar o conteúdo anterior
        chatMessagesDiv.innerHTML = "";

        // Iterar sobre as mensagens e adicioná-las ao chat
        messages.forEach(message => {
            const messageElement = document.createElement("div");
            messageElement.classList.add("message");

            // Exibe a mensagem de texto
            let messageContent = `<strong>${message.nome}</strong>: ${message.mensagem}`;

            // Verifica se há um arquivo e exibe de forma adequada
            if (message.arquivo) {
                const fileExtension = message.arquivo.split('.').pop().toLowerCase();
                
                if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
                    // Exibe imagem
                    messageContent += `<br><img src="${message.arquivo}" alt="Imagem enviada" style="max-width: 300px; max-height: 300px;">`;
                } else if (fileExtension === 'pdf') {
                    // Exibe link para PDF
                    messageContent += `<br><a href="${message.arquivo}" target="_blank">Abrir PDF</a>`;
                } else {
                    // Exibe link para outros tipos de arquivos
                    messageContent += `<br><a href="${message.arquivo}" download>Baixar arquivo</a>`;
                }
            }

            messageElement.innerHTML = messageContent;
            messageElement.innerHTML += `<span class="message-date">${new Date(message.data_envio).toLocaleString()}</span>`;
            chatMessagesDiv.appendChild(messageElement);
        });

        // Rolagem automática para a última mensagem
        chatMessagesDiv.scrollTop = chatMessagesDiv.scrollHeight;
    } catch (error) {
        console.error("Erro ao carregar mensagens:", error);
    }
}

    async function sendMessage() {
    const message = chatInput.value.trim();
    const fileInput = document.getElementById('chatFileInput');
    const file = fileInput.files[0]; // Pega o primeiro arquivo selecionado

    if (message === '' && !file) return; // Não envia se não houver mensagem nem arquivo

    const formData = new FormData();
    formData.append("mensagem", message);

    // Se um arquivo for selecionado, adiciona ao FormData
    if (file) {
        formData.append("arquivo", file);
    }

    try {
        await fetch("enviar_mensagem.php", {
            method: "POST",
            body: formData
        });
        chatInput.value = ''; // Limpa o campo de mensagem
        fileInput.value = ''; // Limpa o campo de arquivo
        loadMessages(); // Carrega as mensagens após enviar
    } catch (error) {
        console.error("Erro ao enviar a mensagem:", error);
        showNotification("Ocorreu um erro ao enviar a mensagem");
    }
}

    // Adiciona evento de click no botão de enviar
    sendButton.addEventListener("click", sendMessage);

    // Permite enviar a mensagem com a tecla Enter
    chatInput.addEventListener("keypress", (e) => {
        if (e.key === 'Enter') sendMessage();
    });

    // Carregar mensagens automaticamente a cada 2 segundos
    setInterval(loadMessages, 2000);
    loadMessages(); // Carrega as mensagens ao carregar a página
});

    sendButton.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });

    // Carrega mensagens automaticamente a cada 2 segundos
    setInterval(loadMessages, 2000);
    loadMessages();

    function toggleModal(modalId, displayStyle = 'flex') {
        const modal = document.getElementById(modalId);
        modal.style.display = modal.style.display === displayStyle ? 'none' : displayStyle;
    }

    // Funções para abrir e fechar modais
    function openModal() {
        toggleModal('modal');
    }

    function closeModal() {
        toggleModal('modal', 'none');
    }


    // Função para mostrar notificações
    function showNotification(message) {
        const notification = document.getElementById('notification');
        notification.textContent = message;
        notification.style.display = 'block';
        notification.classList.add('fadeIn'); // Adicionar uma classe para animação

        setTimeout(() => {
            notification.classList.remove('fadeIn'); // Remover a animação de fade
            notification.classList.add('fadeOut');
            setTimeout(() => {
                notification.style.display = 'none';
                notification.classList.remove('fadeOut');
            }, 500); // Tempo para o fade out
        }, 3000);
    }
    document.addEventListener("DOMContentLoaded", function () {
    const chatMessagesDiv = document.getElementById("chatMessages");
    
    // Pegue o conteúdo JSON da div
    const messages = JSON.parse(chatMessagesDiv.textContent);

    // Limpe o conteúdo bruto da div
    chatMessagesDiv.innerHTML = "";

    // Itere sobre as mensagens e adicione ao HTML
    messages.forEach(message => {
        // Crie um elemento de mensagem
        const messageElement = document.createElement("div");
        messageElement.classList.add("message");

        // Formate a mensagem com nome e texto
        messageElement.innerHTML = `
            <strong>${message.nome}</strong>: ${message.mensagem}
            <span class="message-date">${new Date(message.data_envio).toLocaleString()}</span>
        `;

        // Adicione a mensagem ao chat
        chatMessagesDiv.appendChild(messageElement);
    });
});
function showCalendar(type) {
        const academico = document.getElementById("calendar-academico");
        const letivo = document.getElementById("calendar-letivo");
        const academicoBtn = document.getElementById("academico-btn");
        const letivoBtn = document.getElementById("letivo-btn");

        if (type === 'academico') {
            academico.style.display = 'block';
            letivo.style.display = 'none';
            academicoBtn.style.backgroundColor = '#0078d7';
            academicoBtn.style.color = '#fff';
            letivoBtn.style.backgroundColor = '#e0e0e0';
            letivoBtn.style.color = '#333';
        } else {
            academico.style.display = 'none';
            letivo.style.display = 'block';
            letivoBtn.style.backgroundColor = '#0078d7';
            letivoBtn.style.color = '#fff';
            academicoBtn.style.backgroundColor = '#e0e0e0';
            academicoBtn.style.color = '#333';
        }
    }
    function sendMessage() {
        }
</script>

<style>
    .fadeIn {
        animation: fadeIn 0.5s ease-in;
    }

    .fadeOut {
        animation: fadeOut 0.5s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
</style>
<div id="notification" class="notification"></div>
</body>
</html>
