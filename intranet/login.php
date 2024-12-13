<?php
// Configuração de conexão com o banco de dados
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

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['password'] ?? '';

    // Consulta SQL para buscar o usuário com o e-mail fornecido
    $sql = "SELECT usuario_id, nome, email, senha, cargo FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // A consulta encontrou o usuário, agora verifica a senha
        $stmt->bind_result($id, $nome, $emailDb, $senhaHash, $cargo);
        $stmt->fetch();

        // Verifica a senha fornecida com a senha criptografada no banco de dados
        if (password_verify($senha, $senhaHash)) {
            // Senha correta, inicia a sessão do usuário
            session_start();
            $_SESSION['usuario_id'] = $id;
            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['usuario_email'] = $emailDb;
            $_SESSION['usuario_cargo'] = $cargo;

            // Redireciona para a página inicial ou dashboard
            header('Location: home.php');
            exit;
        } else {
            $error = "Senha incorreta.";
        }
    } else {
        $error = "Email não encontrado.";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="logo.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Intranet</title>
    <!-- Fonte moderna do Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <!-- Font Awesome para ícones -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }

        .login-container {
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
            transition: transform 0.3s ease-in-out;
            opacity: 0;
            animation: fadeIn 0.5s ease-in-out forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .login-container h1 {
            font-size: 30px;
            margin-bottom: 25px;
            color: #00509E;
            font-weight: 600;
        }

        .login-container img {
            width: 160px; /* Logo da Intranet */
            margin-bottom: 20px;
        }

        .login-container .input-container {
            position: relative;
            margin-bottom: 20px;
        }

        .login-container input {
            width: 100%;
            padding: 15px;
            padding-left: 40px;
            margin-bottom: 10px;
            border: 2px solid #ccc;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .login-container input:focus {
            border-color: #00509E;
            outline: none;
            box-shadow: 0 0 8px rgba(0, 80, 158, 0.5);
        }

        .login-container input[type="email"]::placeholder,
        .login-container input[type="password"]::placeholder {
            color: #888;
            font-weight: 500;
        }

        .login-container input:focus::placeholder {
            color: transparent;
        }

        .login-container button {
            width: 100%;
            padding: 15px;
            background-color: #00509E;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
            position: relative;
        }

        .login-container button:hover {
            background-color: #003366;
        }

        .login-container .error {
            color: red;
            font-size: 14px;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #ffcccc;
            border: 1px solid red;
            border-radius: 5px;
        }

        .login-container .input-container i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="logo.png" alt="Logo"> <!-- Logo da Intranet -->
        <h1>Intranet Sinergia</h1>
        <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <div class="input-container">
                <i class="fas fa-envelope"></i> <!-- Ícone de email -->
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="input-container">
                <i class="fas fa-lock"></i> <!-- Ícone de senha -->
                <input type="password" name="password" placeholder="Senha" required>
            </div>
            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>
