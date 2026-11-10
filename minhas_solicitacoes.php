<?php
require "config.php";
exigir_login();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    conferir_token();
    $id = (int)($_POST["solicitacao_id"] ?? 0);
    $acao = $_POST["acao"] ?? "";

    $consulta = $pdo->prepare("SELECT * FROM solicitacoes WHERE id = ?");
    $consulta->execute([$id]);
    $solicitacao = $consulta->fetch(PDO::FETCH_ASSOC);

    if ($solicitacao) {
        if ($solicitacao["dono_id"] == $_SESSION["usuario_id"] && $solicitacao["status"] === "pendente") {
            if ($acao === "aceitar") {
                $pdo->beginTransaction();
                $atualizar = $pdo->prepare("UPDATE solicitacoes SET status = 'aceita' WHERE id = ?");
                $atualizar->execute([$id]);
                $objeto = $pdo->prepare("UPDATE objetos SET status = 'em_negociacao' WHERE id = ?");
                $objeto->execute([$solicitacao["objeto_id"]]);
                $pdo->commit();
            } elseif ($acao === "recusar") {
                $atualizar = $pdo->prepare("UPDATE solicitacoes SET status = 'recusada' WHERE id = ?");
                $atualizar->execute([$id]);
            }
        }

        if (
            $solicitacao["dono_id"] == $_SESSION["usuario_id"] &&
            $acao === "concluir" &&
            $solicitacao["status"] === "aceita"
        ) {
            $pdo->beginTransaction();
            $atualizar = $pdo->prepare("UPDATE solicitacoes SET status = 'concluida' WHERE id = ?");
            $atualizar->execute([$id]);
            $objeto = $pdo->prepare("UPDATE objetos SET status = 'concluido' WHERE id = ?");
            $objeto->execute([$solicitacao["objeto_id"]]);
            $pdo->commit();
        }
    }

    header("Location: minhas_solicitacoes.php");
    exit;
}

$sql = "SELECT s.*, o.titulo, o.imagem, u.nome AS solicitante, d.nome AS dono
        FROM solicitacoes s
        JOIN objetos o ON s.objeto_id = o.id
        JOIN usuarios u ON s.solicitante_id = u.id
        JOIN usuarios d ON s.dono_id = d.id
        WHERE s.solicitante_id = ? OR s.dono_id = ?
        ORDER BY s.criado_em DESC";
$consulta = $pdo->prepare($sql);
$consulta->execute([$_SESSION["usuario_id"], $_SESSION["usuario_id"]]);
$solicitacoes = $consulta->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = "Solicitações";
require "cabecalho.php";
?>
<section class="secao">
    <h1>Solicitações e conversas</h1>
    <?php if (!$solicitacoes): ?><p>Você ainda não tem solicitações.</p><?php endif; ?>

    <?php foreach ($solicitacoes as $s): ?>
        <article class="linha-solicitacao">
            <div>
                <h3><?= limpar($s["titulo"]) ?></h3>
                <p>Solicitante: <?= limpar($s["solicitante"]) ?></p>
                <p>Anunciante: <?= limpar($s["dono"]) ?></p>
                <p>Status: <strong><?= limpar($s["status"]) ?></strong></p>
            </div>
            <div class="acoes">
                <?php if ($s["dono_id"] == $_SESSION["usuario_id"] && $s["status"] === "pendente"): ?>
                    <form method="POST">
                        <input type="hidden" name="token" value="<?= criar_token() ?>">
                        <input type="hidden" name="solicitacao_id" value="<?= (int)$s["id"] ?>">
                        <button class="botao" name="acao" value="aceitar">Aceitar</button>
                        <button class="botao secundario" name="acao" value="recusar">Recusar</button>
                    </form>
                <?php endif; ?>

                <?php if (in_array($s["status"], ["pendente", "aceita"], true)): ?>
                    <a class="botao" href="chat.php?id=<?= (int)$s["id"] ?>">Abrir chat</a>
                <?php endif; ?>

                <?php if ($s["dono_id"] == $_SESSION["usuario_id"] && $s["status"] === "aceita"): ?>
                    <form method="POST">
                        <input type="hidden" name="token" value="<?= criar_token() ?>">
                        <input type="hidden" name="solicitacao_id" value="<?= (int)$s["id"] ?>">
                        <button class="botao" name="acao" value="concluir">Marcar como concluída</button>
                    </form>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</section>
<?php require "rodape.php"; ?>
