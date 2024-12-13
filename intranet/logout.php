<?php
// logout.php

session_start(); // Inicia a sessão

// Destrói todas as variáveis de sessão
session_unset();
session_destroy();

// Redireciona para a página de login
header('Location: login.php');
exit;
?>
