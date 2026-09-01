<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /est-est004-grid_func-ajax-paranaCompetitivoPrevia/
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

$retorno = $FIS003->paranaCompetitivoPrevisualizar(
    'entrada',
    $ids,
    isset($r_extra) ? $r_extra : ''
);

$resposta = array(
    'code' => !empty($retorno['ok']),
    'data' => !empty($retorno['ok'])
        ? (isset($retorno['resumo']) ? $retorno['resumo'] : array())
        : (isset($retorno['erro']) ? $retorno['erro'] : 'Erro na prévia.'),
    'ids' => isset($retorno['ids']) ? $retorno['ids'] : array(),
    'linhas' => array()
);

if (isset($retorno['linhas']) && is_array($retorno['linhas'])) {
    foreach ($retorno['linhas'] as $linha) {
        $resposta['linhas'][] = array(
            'situacao' => isset($linha['situacao'])
                ? $linha['situacao']
                : ''
        );
    }
}

$json = json_encode($resposta);

if ($json === false) {
    $json = json_encode(array(
        'code' => false,
        'data' => 'Não foi possível gerar o retorno da prévia.'
    ));
}

echo $_REQUEST['callback'] . '(' . $json . ');';
