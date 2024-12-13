<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagina de Aprovação de Pedidos</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7fb;
        }
        header {
            background-color: #004c6d;
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        header button {
            background-color: #005792;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin-right: auto; /* Alinha o botão à esquerda */
        }
        header button:hover {
            background-color: #003f5f;
        }
        header h1 {
            flex-grow: 1; /* Faz o título crescer para ocupar o espaço restante */
            text-align: center; /* Centraliza o título */
        }
        main {
            padding: 20px;
        }
        h1, h2 {
            font-size: 24px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        table th {
            background-color: #005792;
            color: white;
        }
        table td {
            background-color: #ffffff;
        }
        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            color: white;
            font-size: 14px;
            cursor: pointer;
        }
        .btn-approve {
            background-color: #28a745;
        }
        .btn-reject {
            background-color: #dc3545;
        }
        .btn:hover {
            opacity: 0.8;
        }
        .form-container {
            margin-top: 20px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-container label {
            font-weight: bold;
        }
        .form-container input, .form-container textarea, .form-container select {
            width: 100%;
            padding: 8px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .form-container button {
            width: 100%;
            background-color: #005792;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #003f5f;
        }
        .order-list {
            margin-top: 20px;
        }
        .order-list p {
            font-size: 16px;
            font-weight: bold;
        }
        
    </style>
</head>
<body>
    <header>
        <button onclick="window.location.href='home.php';">Início</button>
        <h1>Pagina de Aprovação de Pedidos</h1>
    </header>
    <nav>
        
    </nav>
    <main>
        <section class="form-container">
            <h2>Faça seu Pedido</h2>
            <form id="pedidoForm">
                <textarea id="descricao" placeholder="Descrição do pedido" required></textarea><br>
                <button type="submit">Enviar Pedido</button>
            </form>
        </section>

        <section class="order-list">
            <h2>Pedidos Pendentes</h2>
            <table id="pedidosPendentes">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Descrição</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Pedidos serão listados aqui dinamicamente -->
                </tbody>
            </table>
        </section>
    </main>

    <script>
        // Função para enviar o pedido
        document.getElementById('pedidoForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const descricao = document.getElementById('descricao').value;
            
            fetch('painel_pedidos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'descricao': descricao
                })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                carregarPedidosPendentes();
            });
        });

        // Função para carregar pedidos pendentes (para Admins e Supervisores)
        function carregarPedidosPendentes() {
            fetch('painel_pedidos.php', {
                method: 'GET',
            })
                .then(response => response.json())
                .then(data => {
                    const tabela = document.getElementById('pedidosPendentes').getElementsByTagName('tbody')[0];
                    tabela.innerHTML = ''; // Limpar tabela antes de adicionar
                    data.forEach(pedido => {
                        const row = tabela.insertRow();
                        row.insertCell(0).innerText = pedido.id;
                        row.insertCell(1).innerText = pedido.descricao;
                        row.insertCell(2).innerText = pedido.status;
                        row.insertCell(3).innerHTML = `<button class="btn btn-approve" onclick="aprovarReprovarPedido(${pedido.id}, 'Aprovado')">Aprovar</button> 
                                                     <button class="btn btn-reject" onclick="aprovarReprovarPedido(${pedido.id}, 'Reprovado')">Reprovar</button>`;
                    });
                });
        }

        // Função para aprovar ou reprovar pedido
        function aprovarReprovarPedido(id, status) {
            fetch('painel_pedidos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'id': id,
                    'status': status
                })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                carregarPedidosPendentes(); // Atualiza a lista de pedidos pendentes
            });
        }

        // Carregar pedidos ao carregar a página
        window.onload = carregarPedidosPendentes;
    </script>
</body>
</html>
