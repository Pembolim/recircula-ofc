<?php
require "config.php";
exigir_login();

$id = (int)($_GET["id"] ?? $_POST["solicitacao_id"] ?? 0);

$consulta = $pdo->prepare("SELECT * FROM solicitacoes WHERE id = ?");
$consulta->execute([$id]);
$solicitacao = $consulta->fetch(PDO::FETCH_ASSOC);

if (
    !$solicitacao ||
    !in_array($_SESSION["usuario_id"], [$solicitacao["dono_id"], $solicitacao["solicitante_id"]]) ||
    !in_array($solicitacao["status"], ["pendente", "aceita"], true)
) {
    die("Conversa não disponível.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    conferir_token();
    $mensagem = trim($_POST["mensagem"] ?? "");

    if ($mensagem !== "") {
        $inserir = $pdo->prepare(
            "INSERT INTO mensagens (solicitacao_id, remetente_id, mensagem) VALUES (?, ?, ?)"
        );
        $inserir->execute([$id, $_SESSION["usuario_id"], $mensagem]);
    }

    header("Location: chat.php?id=" . $id);
    exit;
}

$dados_objeto = $pdo->prepare("SELECT titulo FROM objetos WHERE id = ?");
$dados_objeto->execute([$solicitacao["objeto_id"]]);
$objeto = $dados_objeto->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT m.*, u.nome FROM mensagens m
        JOIN usuarios u ON m.remetente_id = u.id
        WHERE m.solicitacao_id = ? ORDER BY m.enviado_em ASC";
$consulta = $pdo->prepare($sql);
$consulta->execute([$id]);
$mensagens = $consulta->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = "Chat";
require "cabecalho.php";
?>
<section class="chat">
    <h1>Conversa sobre: <?= limpar($objeto["titulo"]) ?></h1>
    <p class="aviso">As mensagens aparecem quando a página é atualizada.</p>
    <div class="mensagens">
        <?php if (!$mensagens && $solicitacao["mensagem_inicial"]): ?>
            <div class="mensagem outra">
                <strong>Mensagem inicial</strong>
                <p><?= nl2br(limpar($solicitacao["mensagem_inicial"])) ?></p>
            </div>
        <?php endif; ?>

        <?php foreach ($mensagens as $m): ?>
            <div class="mensagem <?= $m["remetente_id"] == $_SESSION["usuario_id"] ? "minha" : "outra" ?>">
                <strong><?= limpar($m["nome"]) ?></strong>
                <p><?= nl2br(limpar($m["mensagem"])) ?></p>
                <small><?= date("d/m/Y H:i", strtotime($m["enviado_em"])) ?></small>
            </div>
        <?php endforeach; ?>
    </div>
    <form method="POST" class="enviar-mensagem">
        <input type="hidden" name="token" value="<?= criar_token() ?>">
        <input type="hidden" name="solicitacao_id" value="<?= $id ?>">
        <textarea name="mensagem" rows="3" maxlength="2000"
                  placeholder="Escreva sua mensagem..." required></textarea>
        <button class="botao" type="submit">Enviar mensagem</button>
    </form>
    <a href="minhas_solicitacoes.php">Voltar às solicitações</a>
</section>
<?php require "rodape.php"; ?>
