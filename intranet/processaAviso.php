<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $id = $_POST['id'];  // ID do aviso (se estiver editando)

    if ($id) {
        // Atualizar aviso existente
        $sql = "UPDATE avisos SET titulo = '$titulo', descricao = '$descricao' WHERE id = $id";
    } else {
        // Criar novo aviso
        $sql = "INSERT INTO avisos (titulo, descricao) VALUES ('$titulo', '$descricao')";
    }

    if ($conn->query($sql) === TRUE) {
        echo "Aviso salvo com sucesso!";
        header('Location: sua_pagina_de_avisos.php'); // Redirecionar após salvar
    } else {
        echo "Erro ao salvar o aviso: " . $conn->error;
    }
}
?>
