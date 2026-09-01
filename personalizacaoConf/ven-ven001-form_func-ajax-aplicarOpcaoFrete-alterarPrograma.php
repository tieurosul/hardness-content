<?php
namespace hardness;
/**
 * AJAX: aplica opção de frete escolhida no orçamento.
 * Rota: /ven/ven001/form_func-ajax/aplicarOpcaoFrete/
 */
$resposta = array('code' => true, 'data' => array());

require_once('bibliotecas/classes/VEN001.php');
$VEN001 = new VEN001();

$T003_Id = isset($r_T003_Id) ? (int) $r_T003_Id : 0;
$opcaoRaw = isset($r_opcao) ? $r_opcao : (isset($_REQUEST['opcao']) ? $_REQUEST['opcao'] : '');

if ($T003_Id <= 0) {
    $resposta['code'] = false;
    $resposta['data'] = 'Orçamento inválido.';
    echo $_REQUEST['callback'] . '(' . json_encode($resposta) . ');';
    return;
}

$opcao = json_decode(stripslashes($opcaoRaw), true);
if (!is_array($opcao)) {
    $opcao = json_decode($opcaoRaw, true);
}

if (!is_array($opcao) || !isset($opcao['valor'])) {
    $resposta['code'] = false;
    $resposta['data'] = 'Opção de frete inválida.';
    echo $_REQUEST['callback'] . '(' . json_encode($resposta) . ');';
    return;
}

$retorno = $VEN001->aplicarOpcaoFrete($T003_Id, $opcao);

if (empty($retorno['ok'])) {
    $resposta['code'] = false;
    $resposta['data'] = isset($retorno['erro']) ? $retorno['erro'] : 'Não foi possível aplicar o frete.';
} else {
    $resposta['code'] = true;
    $resposta['data'] = array(
        'mensagem' => 'Frete aplicado ao orçamento.',
        'aviso' => isset($retorno['aviso']) ? $retorno['aviso'] : '',
    );
}

echo $_REQUEST['callback'] . '(' . json_encode($resposta) . ');';
