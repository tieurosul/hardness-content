<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /est-est004-grid_func-ajax-paranaCompetitivoProcessar/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/


require_once('bibliotecas/classes/FIS003.php');

$FIS003 = new FIS003();

$ids = isset($r_ids)
    ? array_filter(array_map('intval', explode(',', $r_ids)))
    : array();

$retorno = $FIS003->paranaCompetitivoProcessar(
    'entrada',
    $ids,
    isset($r_extra) ? $r_extra : ''
);

$resposta = array(
    'code' => !empty($retorno['ok']),
    'data' => !empty($retorno['ok'])
        ? (isset($retorno['resumo']) ? $retorno['resumo'] : array())
        : (isset($retorno['erro']) ? $retorno['erro'] : 'Erro no processamento.')
);

$json = json_encode($resposta);

if ($json === false) {
    $json = json_encode(array(
        'code' => false,
        'data' => 'Não foi possível gerar o retorno do processamento.'
    ));
}

echo $_REQUEST['callback'] . '(' . $json . ');';
