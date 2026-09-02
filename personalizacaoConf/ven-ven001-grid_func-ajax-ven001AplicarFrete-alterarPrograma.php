<?php
namespace hardness;
/**
 * Este codigo PHP sera executado ao acessar: /ven/ven001/grid_func-ajax/ven001AplicarFrete/
 * Grava a opcao de frete escolhida no orcamento (T003) e vincula na P007.
 */

require_once('bibliotecas/classes/VEN001.php');
$VEN001 = new VEN001();

global $g;

$resposta = array('code' => true, 'data' => array());

$T003_Id = false;
if (!empty($_REQUEST['T003_Id'])) {
    $T003_Id = mysql_real_escape_string($_REQUEST['T003_Id']);
} elseif (!empty($_REQUEST['acaoId'])) {
    $T003_Id = mysql_real_escape_string($_REQUEST['acaoId']);
}

$p007Id = isset($_REQUEST['p007Id']) ? (int) $_REQUEST['p007Id'] : 0;
$opcaoExternaId = isset($_REQUEST['opcaoId']) ? mysql_real_escape_string($_REQUEST['opcaoId']) : '';
$valorFreteNum = isset($_REQUEST['valorFreteNum']) ? (float) $_REQUEST['valorFreteNum'] : false;
$prazoEntrega = isset($_REQUEST['prazoEntrega']) ? mysql_real_escape_string($_REQUEST['prazoEntrega']) : '';
$transportadora = isset($_REQUEST['transportadora']) ? mysql_real_escape_string($_REQUEST['transportadora']) : '';
$quemPaga = isset($_REQUEST['quemPaga']) ? strtoupper(trim($_REQUEST['quemPaga'])) : '';

if (empty($T003_Id) || $valorFreteNum === false) {
    $resposta['code'] = false;
    $resposta['data'] = 'Dados insuficientes para aplicar o frete.';
    echo $_REQUEST['callback'] . '(' . json_encode($resposta) . ');';
    return;
}

$mP007 = false;
if ($p007Id > 0) {
    $p007 = mysql_query("SELECT P007_Id,
                                P007_T003_Id,
                                P007_Valor_Frete,
                                P007_Prazo_Entrega,
                                P007_Transportadora,
                                P007_Quem_Paga
                           FROM P007
                          WHERE P007_Id = '{$p007Id}'
                            AND P007_T003_Id = '{$T003_Id}'");
    $mP007 = mysql_fetch_assoc($p007);
}

if (!$mP007 && !empty($opcaoExternaId)) {
    $p007 = mysql_query("SELECT P007_Id,
                                P007_T003_Id,
                                P007_Valor_Frete,
                                P007_Prazo_Entrega,
                                P007_Transportadora,
                                P007_Quem_Paga
                           FROM P007
                          WHERE P007_T003_Id = '{$T003_Id}'
                            AND P007_Opcao_Externa_Id = '{$opcaoExternaId}'
                       ORDER BY P007_Id DESC
                          LIMIT 1");
    $mP007 = mysql_fetch_assoc($p007);
}

if (!$mP007) {
    $valorFreteDbBusca = number_format($valorFreteNum, 2, '.', '');
    $p007 = mysql_query("SELECT P007_Id,
                                P007_T003_Id,
                                P007_Valor_Frete,
                                P007_Prazo_Entrega,
                                P007_Transportadora,
                                P007_Quem_Paga
                           FROM P007
                          WHERE P007_T003_Id = '{$T003_Id}'
                            AND P007_Valor_Frete = '{$valorFreteDbBusca}'
                            AND P007_Transportadora = '{$transportadora}'
                       ORDER BY P007_Id DESC
                          LIMIT 1");
    $mP007 = mysql_fetch_assoc($p007);
}

if (!$mP007) {
    $valorFreteDbInsert = number_format($valorFreteNum, 2, '.', '');
    $opcaoIdInsert = !empty($opcaoExternaId) ? $opcaoExternaId : '0';
    mysql_query("INSERT INTO P007 SET
                        P007_T003_Id = '{$T003_Id}',
                        P007_C004_Id = '{$g['empresaAtual']}',
                        P007_Opcao_Externa_Id = '{$opcaoIdInsert}',
                        P007_Valor_Frete = '{$valorFreteDbInsert}',
                        P007_Prazo_Entrega = '{$prazoEntrega}',
                        P007_Transportadora = '{$transportadora}',
                        P007_Quem_Paga = '{$quemPaga}',
                        P007_Flag_Selecionada = 'N',
                        P007_Data_Calculo = NOW()");

    $p007IdInsert = (int) mysql_insert_id();
    if ($p007IdInsert > 0) {
        $p007 = mysql_query("SELECT P007_Id,
                                    P007_T003_Id,
                                    P007_Valor_Frete,
                                    P007_Prazo_Entrega,
                                    P007_Transportadora,
                                    P007_Quem_Paga
                               FROM P007
                              WHERE P007_Id = '{$p007IdInsert}'");
        $mP007 = mysql_fetch_assoc($p007);
    }
}

if (!$mP007) {
    $resposta['code'] = false;
    $resposta['data'] = 'Opcao de frete nao encontrada para este orcamento.';
    echo $_REQUEST['callback'] . '(' . json_encode($resposta) . ');';
    return;
}

$p007Id = (int) $mP007['P007_Id'];

$T003 = mysql_query("SELECT T003_Valor_Frete,
                            T003_Flag_Frete,
                            T003A_Flag_Ratear_Frete_Custo
                       FROM T003
                  LEFT JOIN T003A ON T003_Id = T003A_T003_Id
                      WHERE T003_Id = '{$T003_Id}'");
$rT003 = mysql_fetch_assoc($T003);

if (!$rT003) {
    $resposta['code'] = false;
    $resposta['data'] = 'Orcamento nao encontrado.';
    echo $_REQUEST['callback'] . '(' . json_encode($resposta) . ');';
    return;
}

$flagFrete = ($quemPaga == 'FOB') ? '1' : '0';
$valorFreteDb = number_format($valorFreteNum, 2, '.', '');

mysql_query("UPDATE T003 SET
                    T003_Valor_Frete = '{$valorFreteDb}',
                    T003_Valor_Frete_Calculado = '{$valorFreteDb}',
                    T003_Prazo_Entrega = '{$prazoEntrega}',
                    T003_Nome_Transportadora = '{$transportadora}',
                    T003_Flag_Frete = '{$flagFrete}'
              WHERE T003_Id = '{$T003_Id}'");

$erro = mysql_error();
if (!empty($erro)) {
    $resposta['code'] = false;
    $resposta['data'] = 'Erro ao gravar frete: ' . $erro;
    echo $_REQUEST['callback'] . '(' . json_encode($resposta) . ');';
    return;
}

$frete = $flagFrete;
$valorFrete = $valorFreteDb;
$atualizouPrecoTabela = false;

if ($frete != '0') {
    $T250 = mysql_query("SELECT T250_Id, T250_Aliquota, T250_Flag_Altera_Preco FROM T250 WHERE T250_T003_Id = '{$T003_Id}' AND T250_Tipo = '1' AND T250_Flag_Campo_Calculado = 'S'");
    $mT250 = mysql_fetch_array($T250);
    if ($mT250 && $mT250['T250_Aliquota'] > 0) {
        mysql_query("UPDATE T250 SET T250_Aliquota = 0 WHERE T250_Id = '{$mT250['T250_Id']}'");
        $atualizouPrecoTabela = true;
        $VEN001->atualizarPrecoTabela($T003_Id);
    }

    if ((($valorFrete != $rT003['T003_Valor_Frete']) || ($frete != $rT003['T003_Flag_Frete'])) && $rT003['T003A_Flag_Ratear_Frete_Custo'] == 'S' && !$atualizouPrecoTabela) {
        $VEN001->atualizarPrecoTabela($T003_Id);
    }
} else {
    $T250 = mysql_query("SELECT T250_Id, T250_Aliquota, T250_Flag_Altera_Preco FROM T250 WHERE T250_T003_Id = '{$T003_Id}' AND T250_Tipo = '1' AND T250_Flag_Campo_Calculado = 'S'");
    $mT250 = mysql_fetch_array($T250);
    $T249 = mysql_query("SELECT T249_Aliquota FROM T249 WHERE T249_Tipo = '1' AND T249_Flag_Campo_Calculado = 'S' AND T249_C004_Id = '{$g['empresaAtual']}'");
    $mT249 = mysql_fetch_array($T249);

    if ($mT250 && $mT250['T250_Aliquota'] <= 0 && $mT249['T249_Aliquota'] > 0) {
        mysql_query("UPDATE T250 SET T250_Aliquota = '{$mT249['T249_Aliquota']}' WHERE T250_Id = '{$mT250['T250_Id']}'");
        $atualizouPrecoTabela = true;
        $VEN001->atualizarPrecoTabela($T003_Id);
    }

    if ((($valorFrete != $rT003['T003_Valor_Frete']) || ($frete != $rT003['T003_Flag_Frete'])) && $rT003['T003A_Flag_Ratear_Frete_Custo'] == 'S' && !$atualizouPrecoTabela) {
        $VEN001->atualizarPrecoTabela($T003_Id);
    }
}

mysql_query("CALL T003_Gravar_Totalizacao_4('{$T003_Id}')");

mysql_query("UPDATE P007 SET P007_Flag_Selecionada = 'N' WHERE P007_T003_Id = '{$T003_Id}'");
mysql_query("UPDATE P007 SET
                    P007_Flag_Selecionada = 'S',
                    P007_Data_Selecao = NOW(),
                    P007_C007_Id = '{$g['usuarioAtual']}'
              WHERE P007_Id = '{$p007Id}'
                AND P007_T003_Id = '{$T003_Id}'");

$resposta['code'] = true;
$resposta['data'] = array(
    'p007Id' => $p007Id,
    'valorFrete' => gCorrigeNumero($valorFreteNum),
    'prazoEntrega' => $prazoEntrega,
    'transportadora' => $transportadora,
    'quemPaga' => $quemPaga,
);

echo $_REQUEST['callback'] . '(' . json_encode($resposta) . ');';
