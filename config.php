<?php
session_start();

$host = "localhost";
$banco = "recircula_completo";
$usuario = "root";
$senha = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $erro) {
    die("Erro ao conectar com o banco. Confira o arquivo config.php.");
}

function limpar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

function exigir_login() {
    if (!isset($_SESSION["usuario_id"])) {
        header("Location: login.php");
        exit;
    }
}

function criar_token() {
    if (empty($_SESSION["token"])) {
        $_SESSION["token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["token"];
}

function conferir_token() {
    if (
        !isset($_POST["token"]) ||
        !isset($_SESSION["token"]) ||
        !hash_equals($_SESSION["token"], $_POST["token"])
    ) {
        die("Solicitação inválida. Volte e tente novamente.");
    }
}
