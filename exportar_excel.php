<?php
ob_start();
error_log("Iniciando exportar_excel.php");

require_once 'vendor/autoload.php';
require_once 'conexao_export.php';

use OpenSpout\Writer\XLSX\Writer as XLSXWriter;
use OpenSpout\Writer\XLSX\Options;

try {
    if (!isset($conexao) || $conexao === null) {
        error_log("conexao com o banco de dados não foi estabelecida.");
        throw new Exception("conexao com o banco de dados não foi estabelecida.");
    }

    $filtro = isset($_POST['filtro']) ? trim($_POST['filtro']) : '';
    error_log("Filtro recebido: " . $filtro);

    $sql = "SELECT m.*, i.nome AS instrumento, a.nome AS atuacao, c.nome AS cargo
            FROM membros m
            LEFT JOIN instrumentos i ON m.instrumento_id = i.id
            LEFT JOIN atuacoes a ON m.atuacao_id = a.id
            LEFT JOIN cargos c ON m.cargo_id = c.id";
    if ($filtro) {
        $sql .= " WHERE m.nome LIKE ?";
        $filtro = "%$filtro%";
    }
    $sql .= " ORDER BY m.nome";

    $stmt = $conexao->prepare($sql);
    if (!$stmt) {
        error_log("Erro ao preparar a consulta: " . $conexao->error);
        throw new Exception("Erro ao preparar a consulta: " . $conexao->error);
    }

    if ($filtro) {
        $stmt->bind_param('s', $filtro);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $membros = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    error_log("Membros encontrados: " . count($membros));

    $options = new Options();
    $writer = new XLSXWriter($options);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="membros.xlsx"');
    header('Cache-Control: max-age=0');

    ob_end_clean();

    $writer->openToFile('php://output');
    $writer->addRow([
        'Nome',
        'Telefone',
        'Bairro',
        'Batizado',
        'Músico',
        'Instrumento',
        'Atuação',
        'Organista',
        'Cargo'
    ]);

    foreach ($membros as $membro) {
        $writer->addRow([
            $membro['nome'] ?? '',
            $membro['telefone'] ?? '',
            $membro['bairro'] ?? '',
            $membro['batizado'] ?? '',
            $membro['musico'] ?? '',
            $membro['instrumento'] ?? '',
            $membro['atuacao'] ?? '',
            $membro['organista'] ?? '',
            $membro['cargo'] ?? ''
        ]);
    }

    $writer->close();
    error_log("Excel gerado com sucesso");

} catch (Exception $e) {
    error_log("Erro em exportar_excel.php: " . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Erro ao gerar Excel: ' . $e->getMessage()]);
}

$conexao?->close();
exit;
?>