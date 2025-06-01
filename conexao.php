<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "sistema_cadastro";

$conexao = new mysqli($host, $user, $pass, $dbname);

if ($conexao->connect_error) {
    die("Erro na conexao: " . $conexao->connect_error);
}
?>