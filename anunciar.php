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
        $caminho_imagem = null;

        if (isset($_FILES["imagem"]) && $_FILES["imagem"]["error"] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES["imagem"]["error"] !== UPLOAD_ERR_OK) {
                $erro = "Não foi possível enviar a imagem.";
            } elseif ($_FILES["imagem"]["size"] > 3 * 1024 * 1024) {
                $erro = "A imagem deve ter no máximo 3 MB.";
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($_FILES["imagem"]["tmp_name"]);
                $tipos_permitidos = [
                    "image/jpeg" => "jpg",
                    "image/png" => "png",
                    "image/webp" => "webp"
                ];

                if (!isset($tipos_permitidos[$mime])) {
                    $erro = "Envie uma imagem JPG, PNG ou WEBP.";
                } else {
                    $nome_arquivo = bin2hex(random_bytes(16)) . "." . $tipos_permitidos[$mime];
                    $pasta = __DIR__ . "/uploads/objetos/";
                    if (move_uploaded_file($_FILES["imagem"]["tmp_name"], $pasta . $nome_arquivo)) {
                        $caminho_imagem = "uploads/objetos/" . $nome_arquivo;
                    } else {
                        $erro = "Erro ao salvar a imagem.";
                    }
                }
            }
        }

        if ($erro === "") {
            $inserir = $pdo->prepare(
                "INSERT INTO objetos (usuario_id, titulo, descricao, tipo, imagem)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $inserir->execute([
                $_SESSION["usuario_id"], $titulo, $descricao, $tipo, $caminho_imagem
            ]);
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
