<?php
$host = getenv("SUPABASE_DB_HOST") ?: "";
$port = getenv("SUPABASE_DB_PORT") ?: "5432";
$database = getenv("SUPABASE_DB_NAME") ?: "postgres";
$user = getenv("SUPABASE_DB_USER") ?: "";
$password = getenv("SUPABASE_DB_PASSWORD") ?: "";

if ($host === "" || $user === "" || $password === "") {
    http_response_code(503);
    exit(
        "Configuração do banco pendente. Configure SUPABASE_DB_HOST, " .
        "SUPABASE_DB_USER e SUPABASE_DB_PASSWORD nas variáveis de ambiente do Render."
    );
}

$forwardedProto = $_SERVER["HTTP_X_FORWARDED_PROTO"] ?? "";
$isHttps = (
    (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ||
    $forwardedProto === "https"
);

if (session_status() === PHP_SESSION_NONE) {
    ini_set("session.use_strict_mode", "1");
    session_set_cookie_params([
        "lifetime" => 0,
        "path" => "/",
        "secure" => $isHttps,
        "httponly" => true,
        "samesite" => "Lax"
    ]);
    session_start();
}

try {
    $dsn = "pgsql:host={$host};port={$port};dbname={$database};sslmode=require";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $erro) {
    error_log("Database connection failed: " . $erro->getMessage());
    http_response_code(503);
    exit("Não foi possível conectar ao banco de dados. Confira as variáveis do Supabase no Render.");
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
        http_response_code(400);
        die("Solicitação inválida. Volte e tente novamente.");
    }
}
