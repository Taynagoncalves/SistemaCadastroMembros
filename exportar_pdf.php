<?php
header('X-Debug: Iniciando exportar_pdf.php');
require_once 'vendor/autoload.php';
require_once 'conexao_export.php';

use Dompdf\Dompdf;
use Dompdf\Options;

try {
    if (!isset($conexao) || $conexao === null) {
        throw new Exception("Conexão com o banco de dados não foi estabelecida.");
    }

    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);

    $filtro = isset($_POST['filtro']) ? trim($_POST['filtro']) : '';
    header('X-Debug: Filtro recebido: ' . $filtro);
    $sql = "SELECT m.*, DATE_FORMAT(m.data_nascimento, '%d/%m/%Y') AS data_nascimento_formatada, 
                   i.nome AS instrumento, a.nome AS atuacao, c.nome AS cargo
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
        throw new Exception("Erro ao preparar a consulta: " . $conexao->error);
    }

    if ($filtro) {
        $stmt->bind_param('s', $filtro);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $membros = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    header('X-Debug: Membros encontrados: ' . count($membros));

    function calcularIdade($dataNascimento) {
        if (!$dataNascimento || $dataNascimento === '00/00/0000') return '';
        try {
            $nascimento = DateTime::createFromFormat('d/m/Y', $dataNascimento);
            if (!$nascimento) return '';
            $hoje = new DateTime();
            $idade = $hoje->diff($nascimento)->y;
            return $idade >= 0 ? $idade : '';
        } catch (Exception $e) {
            return '';
        }
    }

    if (empty($membros)) {
        $html = '<!DOCTYPE html><html><body><h1>Lista de Membros</h1><p>Nenhum membro encontrado.</p></body></html>';
    } else {
        $html = '
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
                h1 { text-align: center; color: #007bff; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #007bff; color: white; }
                tr:nth-child(even) { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <h1>Lista de Membros</h1>
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Telefone</th>
                        <th>Bairro</th>
                        <th>Batizado</th>
                        <th>Músico</th>
                        <th>Instrumento</th>
                        <th>Atuação</th>
                        <th>Organista</th>
                        <th>Cargo</th>
                        <th>Data de Nascimento</th>
                        <th>Idade</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($membros as $membro) {
            $idade = calcularIdade($membro['data_nascimento_formatada']);
            $html .= '<tr>
                <td>' . htmlspecialchars($membro['nome'] ?? '') . '</td>
                <td>' . htmlspecialchars($membro['telefone'] ?? '') . '</td>
                <td>' . htmlspecialchars($membro['bairro'] ?? '') . '</td>
                <td>' . htmlspecialchars($membro['batizado'] ?? '') . '</td>
                <td>' . htmlspecialchars($membro['musico'] ?? '') . '</td>
                <td>' . htmlspecialchars($membro['instrumento'] ?? '') . '</td>
                <td>' . htmlspecialchars($membro['atuacao'] ?? '') . '</td>
                <td>' . htmlspecialchars($membro['organista'] ?? '') . '</td>
                <td>' . htmlspecialchars($membro['cargo'] ?? '') . '</td>
                <td>' . htmlspecialchars($membro['data_nascimento_formatada'] ?? '') . '</td>
                <td>' . htmlspecialchars($idade) . '</td>
            </tr>';
        }

        $html .= '
                </tbody>
            </table>
        </body>
        </html>';
    }

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    header('X-Debug: PDF gerado com sucesso');
    $dompdf->stream("membros.pdf", ["Attachment" => true]);
} catch (Exception $e) {
    header('X-Debug: Erro: ' . $e->getMessage());
    http_response_code(500);
    echo "Erro ao gerar PDF: " . $e->getMessage();
}

$conexao?->close();
?>