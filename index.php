<?php
require "config.php";

$busca = trim($_GET["busca"] ?? "");

$sql = "SELECT
            objetos.id,
            objetos.usuario_id,
            objetos.titulo,
            objetos.descricao,
            objetos.tipo,
            objetos.status,
            objetos.criado_em,
            CASE WHEN objetos.imagem_bytes IS NOT NULL THEN 1 ELSE 0 END AS tem_imagem,
            usuarios.nome AS dono
        FROM objetos
        JOIN usuarios ON objetos.usuario_id = usuarios.id
        WHERE objetos.status = 'disponivel'";
$parametros = [];

if ($busca !== "") {
    $sql .= " AND objetos.titulo ILIKE ?";
    $parametros[] = "%$busca%";
}

$sql .= " ORDER BY objetos.criado_em DESC";
$consulta = $pdo->prepare($sql);
$consulta->execute($parametros);
$objetos = $consulta->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = "Início";
require "cabecalho.php";
?>
<section class="hero">
    <div>
        <p class="etiqueta">CONSUMO CONSCIENTE</p>
        <h1>O que não serve mais para você pode fazer diferença para alguém.</h1>
        <p>Encontre objetos para doação ou troca e coloque coisas em circulação.</p>
        <a class="botao" href="anunciar.php">Anunciar um objeto</a>
    </div>
</section>

<section class="secao">
    <h2>Objetos disponíveis</h2>
    <form class="busca" method="GET">
        <input type="text" name="busca" placeholder="Buscar pelo nome do objeto"
               value="<?= limpar($busca) ?>">
        <button class="botao" type="submit">Buscar</button>
    </form>

    <div class="grade">
        <?php foreach ($objetos as $objeto): ?>
            <article class="card">
                <?php if ((int)$objeto["tem_imagem"] === 1): ?>
                    <img class="foto-objeto" src="imagem.php?id=<?= (int)$objeto["id"] ?>"
                         alt="Foto de <?= limpar($objeto["titulo"]) ?>">
                <?php else: ?>
                    <div class="sem-foto">Sem imagem</div>
                <?php endif; ?>
                <div class="card-conteudo">
                    <span class="etiqueta"><?= $objeto["tipo"] === "doacao" ? "DOAÇÃO" : "TROCA" ?></span>
                    <h3><?= limpar($objeto["titulo"]) ?></h3>
                    <p><?= limpar($objeto["descricao"]) ?></p>
                    <small>Anunciado por <?= limpar($objeto["dono"]) ?></small>
                    <a class="botao botao-menor" href="objeto.php?id=<?= (int)$objeto["id"] ?>">Ver objeto</a>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if (!$objetos): ?>
            <p>Nenhum objeto encontrado.</p>
        <?php endif; ?>
    </div>
</section>
<?php require "rodape.php"; ?>
