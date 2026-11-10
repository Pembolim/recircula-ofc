<?php
require "config.php";
$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    conferir_token();
    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";

    if ($nome === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($senha) < 6) {
        $erro = "Preencha os campos corretamente. A senha deve ter pelo menos 6 caracteres.";
    } else {
        $verificar = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $verificar->execute([$email]);

        if ($verificar->fetch()) {
            $erro = "Este e-mail já está cadastrado.";
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $inserir = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
            $inserir->execute([$nome, $email, $hash]);
            header("Location: login.php");
            exit;
        }
    }
}

$titulo_pagina = "Cadastro";
require "cabecalho.php";
?>
<section class="formulario">
    <h1>Criar conta</h1>
    <?php if ($erro): ?><p class="erro"><?= limpar($erro) ?></p><?php endif; ?>
    <form method="POST">
        <input type="hidden" name="token" value="<?= criar_token() ?>">
        <label>Nome</label>
        <input type="text" name="nome" required>
        <label>E-mail</label>
        <input type="email" name="email" required>
        <label>Senha (mínimo 6 caracteres)</label>
        <input type="password" name="senha" minlength="6" required>
        <button class="botao" type="submit">Cadastrar</button>
    </form>
</section>
<?php require "rodape.php"; ?>
