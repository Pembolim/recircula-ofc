<?php
require "config.php";
exigir_login();

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    conferir_token();

    $titulo = trim($_POST["titulo"] ?? "");
    $descricao = trim($_POST["descricao"] ?? "");
    $tipo = $_POST["tipo"] ?? "";

    if ($titulo === "" || $descricao === "" || !in_array($tipo, ["doacao", "troca"], true)) {
        $erro = "Preencha todos os campos corretamente.";
    } else {
        $dados_imagem = null;
        $mime_imagem = null;

        if (isset($_FILES["imagem"]) && $_FILES["imagem"]["error"] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES["imagem"]["error"] !== UPLOAD_ERR_OK) {
                $erro = "Não foi possível enviar a imagem.";
            } elseif ($_FILES["imagem"]["size"] > 3 * 1024 * 1024) {
                $erro = "A imagem deve ter no máximo 3 MB.";
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($_FILES["imagem"]["tmp_name"]);
                $tipos_permitidos = ["image/jpeg", "image/png", "image/webp"];

                if (!in_array($mime, $tipos_permitidos, true)) {
                    $erro = "Envie uma imagem JPG, PNG ou WEBP.";
                } else {
                    $conteudo = file_get_contents($_FILES["imagem"]["tmp_name"]);

                    if ($conteudo === false) {
                        $erro = "Não foi possível ler a imagem enviada.";
                    } else {
                        $dados_imagem = $conteudo;
                        $mime_imagem = $mime;
                    }
                }
            }
        }

        if ($erro === "") {
            $inserir = $pdo->prepare(
                "INSERT INTO objetos
                    (usuario_id, titulo, descricao, tipo, imagem_bytes, imagem_mime)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $inserir->bindValue(1, (int)$_SESSION["usuario_id"], PDO::PARAM_INT);
            $inserir->bindValue(2, $titulo, PDO::PARAM_STR);
            $inserir->bindValue(3, $descricao, PDO::PARAM_STR);
            $inserir->bindValue(4, $tipo, PDO::PARAM_STR);
            $inserir->bindValue(
                5,
                $dados_imagem,
                $dados_imagem === null ? PDO::PARAM_NULL : PDO::PARAM_LOB
            );
            $inserir->bindValue(
                6,
                $mime_imagem,
                $mime_imagem === null ? PDO::PARAM_NULL : PDO::PARAM_STR
            );
            $inserir->execute();

            header("Location: meus_anuncios.php");
            exit;
        }
    }
}

$titulo_pagina = "Anunciar objeto";
require "cabecalho.php";
?>
<section class="formulario">
    <h1>Anunciar objeto</h1>
    <?php if ($erro): ?><p class="erro"><?= limpar($erro) ?></p><?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="token" value="<?= criar_token() ?>">
        <label>Nome do objeto</label>
        <input type="text" name="titulo" maxlength="120" required>
        <label>Descrição</label>
        <textarea name="descricao" rows="5" required></textarea>
        <label>Modalidade</label>
        <select name="tipo" required>
            <option value="doacao">Doação</option>
            <option value="troca">Troca</option>
        </select>
        <label>Foto (JPG, PNG ou WEBP, até 3 MB)</label>
        <input type="file" name="imagem" accept="image/jpeg,image/png,image/webp">
        <button class="botao" type="submit">Publicar anúncio</button>
    </form>
</section>
<?php require "rodape.php"; ?>
