<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
error_log("Iniciando listar.php");

try {
    $conexaoFile = "conexao.php";
    if (!file_exists($conexaoFile)) {
        error_log("Arquivo conexao.php não encontrado em " . $conexaoFile);
        throw new Exception("Arquivo de conexao não encontrado: " . $conexaoFile);
    }

    include($conexaoFile);
    error_log("conexao.php incluído com sucesso");

    if (!isset($conexao) || $conexao->connect_error) {
        error_log("Erro de conexao: " . ($conexao->connect_error ?? "Variável conexao não definida"));
        throw new Exception("Falha na conexao: " . ($conexao->connect_error ?? "conexao não definida"));
    }
    error_log("conexao estabelecida com sucesso");

    $sql = "SELECT m.id, m.nome, m.data_nascimento, m.telefone, m.bairro, m.batizado, m.musico, 
                   i.nome AS instrumento, m.instrumento_id, a.nome AS atuacao, m.atuacao_id, 
                   m.organista, c.nome AS cargo, m.cargo_id, m.endereco, m.cep, m.numero, m.sexo
            FROM membros m
            LEFT JOIN instrumentos i ON m.instrumento_id = i.id
            LEFT JOIN atuacoes a ON m.atuacao_id = a.id
            LEFT JOIN cargos c ON m.cargo_id = c.id";
    error_log("Executando consulta: $sql");

    $result = mysqli_query($conexao, $sql);
    if (!$result) {
        error_log("Erro na query: " . mysqli_error($conexao));
        throw new Exception("Erro na query: " . mysqli_error($conexao));
    }

    $membros = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $membros[] = [
            'id' => $row['id'] ?? '',
            'nome' => $row['nome'] ?? '',
            'data_nascimento' => $row['data_nascimento'] ? date('d/m/Y', strtotime($row['data_nascimento'])) : '',
            'telefone' => $row['telefone'] ?? '',
            'bairro' => $row['bairro'] ?? '',
            'batizado' => $row['batizado'] ?? '',
            'musico' => $row['musico'] ?? '',
            'instrumento' => $row['instrumento'] ?? '',
            'instrumento_id' => $row['instrumento_id'] ?? '',
            'atuacao' => $row['atuacao'] ?? '',
            'atuacao_id' => $row['atuacao_id'] ?? '',
            'organista' => $row['organista'] ?? '',
            'cargo' => $row['cargo'] ?? '',
            'cargo_id' => $row['cargo_id'] ?? '',
            'endereco' => $row['endereco'] ?? '',
            'cep' => $row['cep'] ?? '',
            'numero' => $row['numero'] ?? '',
            'sexo' => $row['sexo'] ?? ''
        ];
    }
    error_log("Membros encontrados: " . count($membros));

    ob_end_clean();
    echo json_encode($membros);
} catch (Exception $e) {
    error_log("Erro em listar.php: " . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}

if (isset($conexao)) {
    mysqli_close($conexao);
}
?>