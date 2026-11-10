<?php
require "config.php";
$id = (int)($_GET["id"] ?? 0);

$consulta = $pdo->prepare(
    "SELECT objetos.*, usuarios.nome AS dono
     FROM objetos JOIN usuarios ON objetos.usuario_id = usuarios.id
     WHERE objetos.id = ?"
);
$consulta->execute([$id]);
$objeto = $consulta->fetch(PDO::FETCH_ASSOC);

if (!$objeto) {
    http_response_code(404);
    die("Objeto não encontrado.");
}

$erro = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    exigir_login();
    conferir_token();

    if ($objeto["usuario_id"] == $_SESSION["usuario_id"]) {
        $erro = "Você não pode solicitar seu próprio objeto.";
    } elseif ($objeto["status"] !== "disponivel") {
        $erro = "Este objeto não está mais disponível.";
    } else {
        $verificar = $pdo->prepare(
            "SELECT id FROM solicitacoes
             WHERE objeto_id = ? AND solicitante_id = ? AND status IN ('pendente', 'aceita')"
        );
        $verificar->execute([$id, $_SESSION["usuario_id"]]);

        if ($verificar->fetch()) {
            $erro = "Você já tem uma solicitação ativa para este objeto.";
        } else {
            $mensagem = trim($_POST["mensagem_inicial"] ?? "");
            $inserir = $pdo->prepare(
                "INSERT INTO solicitacoes (objeto_id, solicitante_id, dono_id, mensagem_inicial)
                 VALUES (?, ?, ?, ?)"
            );
            $inserir->execute([$id, $_SESSION["usuario_id"], $objeto["usuario_id"], $mensagem]);
            header("Location: minhas_solicitacoes.php");
            exit;
        }
    }
}

$titulo_pagina = $objeto["titulo"];
require "cabecalho.php";
?>
<section class="detalhe">
    <?php if ($objeto["imagem"]): ?>
        <img class="foto-detalhe" src="<?= limpar($objeto["imagem"]) ?>" alt="Foto do objeto">
    <?php endif; ?>
    <div>
        <span class="etiqueta"><?= $objeto["tipo"] === "doacao" ? "DOAÇÃO" : "TROCA" ?></span>
        <h1><?= limpar($objeto["titulo"]) ?></h1>
        <p><?= nl2br(limpar($objeto["descricao"])) ?></p>
        <p><strong>Publicado por:</strong> <?= limpar($objeto["dono"]) ?></p>
        <?php if ($erro): ?><p class="erro"><?= limpar($erro) ?></p><?php endif; ?>

        <?php if (!isset($_SESSION["usuario_id"])): ?>
            <a class="botao" href="login.php">Entre para solicitar</a>
        <?php elseif ($objeto["usuario_id"] != $_SESSION["usuario_id"] && $objeto["status"] === "disponivel"): ?>
            <form method="POST">
                <input type="hidden" name="token" value="<?= criar_token() ?>">
                <label>Mensagem para o anunciante (opcional)</label>
                <textarea name="mensagem_inicial" maxlength="500" rows="3"
                          placeholder="Explique brevemente por que você quer o objeto."></textarea>
                <button class="botao" type="submit">Solicitar objeto</button>
            </form>
        <?php elseif ($objeto["usuario_id"] == $_SESSION["usuario_id"]): ?>
            <p>Este anúncio pertence a você.</p>
        <?php else: ?>
            <p>Este objeto não está disponível no momento.</p>
        <?php endif; ?>
    </div>
</section>
<?php require "rodape.php"; ?>
