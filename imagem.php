<?php
require "config.php";

$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) {
    http_response_code(404);
    exit;
}

$consulta = $pdo->prepare(
    "SELECT imagem_bytes, imagem_mime FROM objetos WHERE id = ?"
);
$consulta->execute([$id]);
$imagem = $consulta->fetch(PDO::FETCH_ASSOC);

if (!$imagem || $imagem["imagem_bytes"] === null || $imagem["imagem_mime"] === null) {
    http_response_code(404);
    exit;
}

$conteudo = $imagem["imagem_bytes"];
if (is_resource($conteudo)) {
    $conteudo = stream_get_contents($conteudo);
}

if (!is_string($conteudo)) {
    http_response_code(404);
    exit;
}

header("Content-Type: " . $imagem["imagem_mime"]);
header("X-Content-Type-Options: nosniff");
header("Cache-Control: private, max-age=300");
header("Content-Length: " . strlen($conteudo));
echo $conteudo;
