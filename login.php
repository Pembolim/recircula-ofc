<?php
require "config.php";
$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    conferir_token();
    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";

    $consulta = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $consulta->execute([$email]);
    $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario["senha"])) {
        session_regenerate_id(true);
        $_SESSION["usuario_id"] = $usuario["id"];
        $_SESSION["usuario_nome"] = $usuario["nome"];
        header("Location: index.php");
        exit;
    } else {
        $erro = "E-mail ou senha incorretos.";
    }
}

$titulo_pagina = "Entrar";
require "cabecalho.php";
?>
<section class="formulario">
    <h1>Entrar</h1>
    <?php if ($erro): ?><p class="erro"><?= limpar($erro) ?></p><?php endif; ?>
    <form method="POST">
        <input type="hidden" name="token" value="<?= criar_token() ?>">
        <label>E-mail</label>
        <input type="email" name="email" required>
        <label>Senha</label>
        <input type="password" name="senha" required>
        <button class="botao" type="submit">Entrar</button>
    </form>
    <p>Não tem conta? <a href="cadastro.php">Cadastre-se</a>.</p>
</section>
<?php require "rodape.php"; ?>
