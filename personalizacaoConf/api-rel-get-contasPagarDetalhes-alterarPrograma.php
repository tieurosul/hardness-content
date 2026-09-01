<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /api-rel-get-contasPagarDetalhes/
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

    $dataInicio = $_GET['DATAINICIAL']; 
    $dataFinal = $_GET['DATAFINAL'];
    $extraFiltro = extraFiltro();		

    $grid = array(
        array('colunaValor' => 'T015_Id', 'colunaTitulo' => 'id_pagar'),
        array('colunaValor' => 'format(T015_Valor_Juros,2,"pt_BR")', 'colunaTitulo' => 'valor_juros_pagar'),
        array('colunaValor' => 'format(T015_Valor_Duplicata,2,"pt_BR")', 'colunaTitulo' => 'valor_sem_juros_pagar'),
        array('colunaValor' => 'format(T015_Valor_Desconto,2,"pt_BR")', 'colunaTitulo' => 'valor_desconto_pagar'),
        array('colunaValor' => 'if(T015_Data_Previsao_Pagamento="0000-00-00","",T015_Data_Previsao_Pagamento)', 'colunaTitulo' => 'data_previsao_pagamento'),
    );

    //montando clausula where
    $where = $API001->montarWhere($where,"T015_Data_Emissao",">=",$dataInicio);
    $where = $API001->montarWhere($where,"T015_Data_Emissao","<=",$dataFinal);
    $where = $API001->montarWhere($where,"T015_Flag_Cancelada","=","N");
    $where = $API001->montarWhere($where,"IF(T015_T015_Id_Agrupado > 0,'S','N')","=","N");
    $where = gInsertExtraWhere($where, "$extraFiltro", true);

    //  Definição da SQL
    $from = 'T015';

    $extra = <<<EOT
        {$where}
EOT;
    list($geradoSql, $geradoDados) = $g['sqlAuto']->gerarSQLGrid($grid, $from, $extra,0.00,false,false,false,45000,true);

    //validando se retornaram dados da query
    $semDados = $API001->tratarRetornoVazio($geradoDados);
    if(is_array($semDados)){
        echo json_encode($semDados, JSON_UNESCAPED_UNICODE);
        die();
    }

    echo json_encode($geradoDados, JSON_UNESCAPED_UNICODE);

    die();
