<?php
namespace hardness;
/**
 * Este codigo PHP sera executado ao acessar: /ven/ven001/grid_func-ajax/ven001CalcularFrete/
 */

global $g;

$freteApiUrl = 'https://TODO-url-da-api-de-frete/calcular';
$freteApiToken = 'TODO-token-da-api-de-frete';

$sqlConfig = "SELECT C031A_Campo, C031A_Valor
                FROM C031A
               WHERE C031A_C004_Id = '{$g['empresaAtual']}'";
$resultConfig = mysql_query($sqlConfig);
while ($rowConfig = mysql_fetch_assoc($resultConfig)) {
    if ($rowConfig['C031A_Campo'] == 'freteApiUrl') {
        $freteApiUrl = $rowConfig['C031A_Valor'];
    }
    if ($rowConfig['C031A_Campo'] == 'freteApiToken') {
        $freteApiToken = $rowConfig['C031A_Valor'];
    }
}

// TODO: trocar para false quando a API real estiver disponivel
$freteMockAtivo = true;

$resposta = array('code' => true, 'data' => array());

$T003_Id = isset($_REQUEST['T003_Id']) ? mysql_real_escape_string($_REQUEST['T003_Id']) : false;

if (empty($T003_Id)) {
    $resposta['code'] = false;
    $resposta['data'] = 'Orcamento nao informado.';
    echo $_REQUEST['callback'] . '(' . json_encode($resposta) . ');';
    return;
}

$sql = "SELECT T003_Peso_Total,
               SUM(T004_Quantidade) AS quantidadeTotal,
               SUM(T004_Cubagem_Unitaria * T004_Quantidade) AS cubagemTotal
          FROM T003
     LEFT JOIN T004 ON T004_T003_Id = T003_Id
         WHERE T003_Id = '{$T003_Id}'
      GROUP BY T003_Id";

$result = mysql_query($sql);
$mT003 = mysql_fetch_assoc($result);

if (!$mT003) {
    $resposta['code'] = false;
    $resposta['data'] = 'Orcamento nao encontrado.';
    echo $_REQUEST['callback'] . '(' . json_encode($resposta) . ');';
    return;
}

if ($freteMockAtivo) {
    $opcoes = array(
        array('id' => '1', 'valorFrete' => '125,50', 'valorFreteNum' => 125.50, 'prazoEntrega' => '5', 'transportadora' => 'Mock Express', 'quemPaga' => 'CIF'),
        array('id' => '2', 'valorFrete' => '89,90', 'valorFreteNum' => 89.90, 'prazoEntrega' => '8', 'transportadora' => 'Mock Economica', 'quemPaga' => 'FOB'),
        array('id' => '3', 'valorFrete' => '210,00', 'valorFreteNum' => 210.00, 'prazoEntrega' => '3', 'transportadora' => 'Mock Rapida', 'quemPaga' => 'CIF'),
    );

    mysql_query("DELETE FROM P007 WHERE P007_T003_Id = '{$T003_Id}'");

    $dataCalculo = date('Y-m-d H:i:s');
    $opcoesComId = array();
    foreach ($opcoes as $op) {
        $opcaoId = mysql_real_escape_string($op['id']);
        $valorFreteDb = number_format($op['valorFreteNum'], 2, '.', '');
        $prazoEntrega = mysql_real_escape_string($op['prazoEntrega']);
        $transportadora = mysql_real_escape_string($op['transportadora']);
        $quemPaga = mysql_real_escape_string($op['quemPaga']);

        mysql_query("INSERT INTO P007 SET
                            P007_T003_Id = '{$T003_Id}',
                            P007_C004_Id = '{$g['empresaAtual']}',
                            P007_Opcao_Externa_Id = '{$opcaoId}',
                            P007_Valor_Frete = '{$valorFreteDb}',
                            P007_Prazo_Entrega = '{$prazoEntrega}',
                            P007_Transportadora = '{$transportadora}',
                            P007_Quem_Paga = '{$quemPaga}',
                            P007_Flag_Selecionada = 'N',
                            P007_Data_Calculo = '{$dataCalculo}'");

        $erroInsert = mysql_error();
        $p007IdInsert = (int) mysql_insert_id();
        if (!empty($erroInsert) || $p007IdInsert <= 0) {
            $resposta['code'] = false;
            $resposta['data'] = 'Erro ao gravar opcoes de frete: ' . ($erroInsert ? $erroInsert : 'ID invalido');
            echo $_REQUEST['callback'] . '(' . json_encode($resposta) . ');';
            return;
        }

        $op['p007Id'] = $p007IdInsert;
        $opcoesComId[] = $op;
    }

    $resposta['code'] = true;
    $resposta['data'] = array(
        'T003_Id' => $T003_Id,
        'opcoes' => $opcoesComId,
    );
} else {
    $resposta['code'] = false;
    $resposta['data'] = 'Consulta real da API de frete ainda nao configurada.';
}

echo $_REQUEST['callback'] . '(' . json_encode($resposta) . ');';
