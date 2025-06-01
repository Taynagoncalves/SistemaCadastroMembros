<?php
include("conexao.php");

if ($conexao->connect_error) {
    die("Falha na conexão: " . $conexao->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT) ?: null;
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING) ?? '';
    $data_nascimento = filter_input(INPUT_POST, 'data_nascimento', FILTER_SANITIZE_STRING) ?? '';
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING) ?? '';
    $endereco = filter_input(INPUT_POST, 'endereco', FILTER_SANITIZE_STRING) ?? '';
    $cep = filter_input(INPUT_POST, 'cep', FILTER_SANITIZE_STRING) ?? '';
    $numero = filter_input(INPUT_POST, 'numero', FILTER_SANITIZE_STRING) ?? '';
    $bairro = filter_input(INPUT_POST, 'bairro', FILTER_SANITIZE_STRING) ?? '';
    $sexo = filter_input(INPUT_POST, 'sexo', FILTER_SANITIZE_STRING) ?? '';
    $batizado = filter_input(INPUT_POST, 'batizado', FILTER_SANITIZE_STRING) ?? '';
    $musico = filter_input(INPUT_POST, 'musico', FILTER_SANITIZE_STRING) ?? '';
    $instrumento_id = filter_input(INPUT_POST, 'instrumento', FILTER_SANITIZE_NUMBER_INT) ?: null;
    $atuacao_id = filter_input(INPUT_POST, 'atuacao', FILTER_SANITIZE_NUMBER_INT) ?: null;
    $organista = filter_input(INPUT_POST, 'organista', FILTER_SANITIZE_STRING) ?? '';
    $cargo_id = filter_input(INPUT_POST, 'cargo', FILTER_SANITIZE_NUMBER_INT) ?: null;

    if (empty($id) || empty($nome)) {
        die("Erro: ID ou nome inválido.");
    }

    $valid_sexo = ['Masculino', 'Feminino', ''];
    $valid_batizado = ['Sim', 'Não', ''];
    $valid_musico = ['Sim', 'Não', ''];
    $valid_organista = ['Sim', 'Não', ''];
    if (!in_array($sexo, $valid_sexo) || !in_array($batizado, $valid_batizado) ||
        !in_array($musico, $valid_musico) || !in_array($organista, $valid_organista)) {
        die("Erro: Valores inválidos para sexo, batizado, músico ou organista.");
    }

    $data_nascimento = $data_nascimento ?: null;

    $sql = "UPDATE membros SET 
            nome = ?, data_nascimento = ?, telefone = ?, endereco = ?, cep = ?, numero = ?, bairro = ?, 
            sexo = ?, batizado = ?, musico = ?, instrumento_id = ?, atuacao_id = ?, 
            organista = ?, cargo_id = ?
            WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param(
            $stmt,
            "ssssssssss".(is_null($instrumento_id) ? "s" : "i").(is_null($atuacao_id) ? "s" : "i")."s".(is_null($cargo_id) ? "s" : "i")."i",
            $nome,
            $data_nascimento,
            $telefone,
            $endereco,
            $cep,
            $numero,
            $bairro,
            $sexo,
            $batizado,
            $musico,
            $instrumento_id,
            $atuacao_id,
            $organista,
            $cargo_id,
            $id
        );

        if (mysqli_stmt_execute($stmt)) {
            echo "ok";
        } else {
            echo "Erro ao atualizar: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Erro na preparação da query: " . mysqli_error($conexao);
    }
}

mysqli_close($conexao);
?>