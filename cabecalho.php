<?php
$titulo_pagina = $titulo_pagina ?? "ReCircula";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= limpar($titulo_pagina) ?> | ReCircula</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topo">
    <a class="logo" href="index.php">Re<span>Circula</span></a>
    <nav>
        <a href="index.php">Início</a>
        <?php if (isset($_SESSION["usuario_id"])): ?>
            <a href="anunciar.php">Anunciar objeto</a>
            <a href="minhas_solicitacoes.php">Solicitações</a>
            <a href="meus_anuncios.php">Meus anúncios</a>
            <a href="logout.php">Sair</a>
        <?php else: ?>
            <a href="login.php">Entrar</a>
            <a href="cadastro.php">Cadastrar</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
