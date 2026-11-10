<?php
require "config.php";
exigir_login();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    conferir_token();
    $id = (int)($_POST["id"] ?? 0);

    $consulta = $pdo->prepare("SELECT imagem FROM objetos WHERE id = ? AND usuario_id = ?");
    $consulta->execute([$id, $_SESSION["usuario_id"]]);
    $objeto = $consulta->fetch(PDO::FETCH_ASSOC);

    if ($objeto) {
        $excluir = $pdo->prepare("DELETE FROM objetos WHERE id = ? AND usuario_id = ?");
        $excluir->execute([$id, $_SESSION["usuario_id"]]);

        if ($objeto["imagem"] && file_exists(__DIR__ . "/" . $objeto["imagem"])) {
            unlink(__DIR__ . "/" . $objeto["imagem"]);
        }
    }

    header("Location: meus_anuncios.php");
    exit;
}

$consulta = $pdo->prepare("SELECT * FROM objetos WHERE usuario_id = ? ORDER BY criado_em DESC");
$consulta->execute([$_SESSION["usuario_id"]]);
$objetos = $consulta->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = "Meus anúncios";
require "cabecalho.php";
?>
<section class="secao">
    <h1>Meus anúncios</h1>
    <div class="grade">
        <?php foreach ($objetos as $objeto): ?>
            <article class="card">
                <?php if ($objeto["imagem"]): ?>
                    <img class="foto-objeto" src="<?= limpar($objeto["imagem"]) ?>" alt="">
                <?php else: ?>
                    <div class="sem-foto">Sem imagem</div>
                <?php endif; ?>
                <div class="card-conteudo">
                    <h3><?= limpar($objeto["titulo"]) ?></h3>
                    <p>Status: <?= limpar($objeto["status"]) ?></p>
                    <a href="objeto.php?id=<?= (int)$objeto["id"] ?>">Ver anúncio</a>
                    <form method="POST" onsubmit="return confirm('Deseja excluir este anúncio?')">
                        <input type="hidden" name="token" value="<?= criar_token() ?>">
                        <input type="hidden" name="id" value="<?= (int)$objeto["id"] ?>">
                        <button class="botao secundario" type="submit">Excluir</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if (!$objetos): ?><p>Você ainda não publicou objetos.</p><?php endif; ?>
    </div>
</section>
<?php require "rodape.php"; ?>
