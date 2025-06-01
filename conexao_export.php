<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "sistema_cadastro";

$conexao = new mysqli($host, $user, $pass, $dbname);

if ($conexao->connect_error) {
    error_log("Erro na conexao: " . $conexao->connect_error);
    http_response_code(500);
    die(json_encode(["error" => "Erro na conexao: " . $conexao->connect_error]));
}

$conexao->set_charset("utf8mb4");
?>