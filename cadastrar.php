<?php
include("conexao.php");

if ($conexao->connect_error) {
    error_log("Falha na conexao: " . $conexao->connect_error);
    die("Erro: Falha na conexao com o banco de dados.");
}

try {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING) ?? '';
    $data_nascimento = filter_input(INPUT_POST, 'data_nascimento', FILTER_SANITIZE_STRING) ?? null;
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING) ?? '';
    $endereco = filter_input(INPUT_POST, 'endereco', FILTER_SANITIZE_STRING) ?? '';
    $cep = filter_input(INPUT_POST, 'cep', FILTER_SANITIZE_STRING) ?? '';
    $numero = filter_input(INPUT_POST, 'numero', FILTER_SANITIZE_STRING) ?? '';
    $bairro = filter_input(INPUT_POST, 'bairro', FILTER_SANITIZE_STRING) ?? '';
    $sexo = filter_input(INPUT_POST, 'sexo', FILTER_SANITIZE_STRING) ?? '';
    $batizado = filter_input(INPUT_POST, 'batizado', FILTER_SANITIZE_STRING) ?? '';
    $musico = filter_input(INPUT_POST, 'musico', FILTER_SANITIZE_STRING) ?? '';
    $organista = filter_input(INPUT_POST, 'organista', FILTER_SANITIZE_STRING) ?? '';
    $cargo_id = filter_input(INPUT_POST, 'cargo', FILTER_SANITIZE_NUMBER_INT) ?: null;

    if (empty($nome)) {
        throw new Exception("O campo nome é obrigatório.");
    }

    $valid_sexo = ['Masculino', 'Feminino', ''];
    $valid_batizado = ['Sim', 'Não', ''];
    $valid_musico = ['Sim', 'Não', ''];
    $valid_organista = ['Sim', 'Não', ''];
    if (!in_array($sexo, $valid_sexo) || !in_array($batizado, $valid_batizado) ||
        !in_array($musico, $valid_musico) || !in_array($organista, $valid_organista)) {
        throw new Exception("Valores inválidos para sexo, batizado, músico ou organista.");
    }

    $instrumento_id = null;
    $atuacao_id = null;
    if ($musico === 'Sim') {
        $instrumento_id = filter_input(INPUT_POST, 'instrumento', FILTER_SANITIZE_NUMBER_INT);
        $atuacao_id = filter_input(INPUT_POST, 'atuacao', FILTER_SANITIZE_NUMBER_INT);
        if ($instrumento_id === false || $instrumento_id === '' || $instrumento_id === '0') {
            throw new Exception("Instrumento é obrigatório para músicos.");
        }
        if ($atuacao_id === false || $atuacao_id === '' || $atuacao_id === '0') {
            throw new Exception("Atuação é obrigatória para músicos.");
        }
    }

    if ($data_nascimento) {
        $data = DateTime::createFromFormat('Y-m-d', $data_nascimento);
        if (!$data || $data->format('Y-m-d') !== $data_nascimento) {
            throw new Exception("Data de nascimento inválida.");
        }
    }

    $sql = "INSERT INTO membros (nome, data_nascimento, telefone, endereco, cep, numero, bairro, sexo, batizado, musico, instrumento_id, atuacao_id, organista, cargo_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexao, $sql);

    if (!$stmt) {
        throw new Exception("Erro na preparação da query: " . mysqli_error($conexao));
    }

    $data_nasc = $data_nascimento ?: null;
    $inst_id = $instrumento_id;
    $atu_id = $atuacao_id;
    $cargo = $cargo_id;
    

    mysqli_stmt_bind_param(
        $stmt,
        "ssssssssss" . (is_null($inst_id) ? "s" : "i") . (is_null($atu_id) ? "s" : "i") . "s" . (is_null($cargo) ? "s" : "i"),
        $nome,
        $data_nasc,
        $telefone,
        $endereco,
        $cep,
        $numero,
        $bairro,
        $sexo,
        $batizado,
        $musico,
        $inst_id,
        $atu_id,
        $organista,
        $cargo
    );    

    if (mysqli_stmt_execute($stmt)) {
        echo "ok";
    } else {
        throw new Exception("Erro ao cadastrar: " . mysqli_stmt_error($stmt));
    }

    mysqli_stmt_close($stmt);
} catch (Exception $e) {
    error_log("Erro em cadastrar.php: " . $e->getMessage());
    echo "Erro ao cadastrar membro: " . $e->getMessage();
}

mysqli_close($conexao);
?>