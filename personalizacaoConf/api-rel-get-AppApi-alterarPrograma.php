<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /api-rel-get-AppApi/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

$retorno = $API003->auth($_GET['API_AUTH'], false);

    if(is_array($retorno)){
        echo json_encode($retorno, JSON_UNESCAPED_UNICODE);
        die();
    }

    $codigoApp = $_GET['codigoApp'];
    $json = executarCodigo($codigoApp);
    echo $json;
    die();
