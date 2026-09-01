<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /api-rel-get-contasPagar/
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
        array('colunaValor' => 'T015_D024_Id', 'colunaTitulo' => 'codigo_cliente'),
        array('colunaValor' => 'T015_C004_Id', 'colunaTitulo' => 'id_empresa'),
        array('colunaValor' => 'T015_T013_Id', 'colunaTitulo' => 'id_nf_recebida'),
        array('colunaValor' => 'T015_D031_Id', 'colunaTitulo' => 'id_conta'),
        array('colunaValor' => 'T015_D014_Id', 'colunaTitulo' => 'id_portador_pagar'),
        array('colunaValor' => 'T015_D073_Id', 'colunaTitulo' => 'id_centro_custo'),
        array('colunaValor' => 'T015_Numero_Duplicata', 'colunaTitulo' => 'numero_duplicata_pagar'),
        array('colunaValor' => 'if(T015_Data_Emissao="0000-00-00","",T015_Data_Emissao)', 'colunaTitulo' => 'data_emissao_pagar'),
        array('colunaValor' => 'if(T015_Data_Vencimento="0000-00-00","",T015_Data_Vencimento)', 'colunaTitulo' => 'data_vencimento_pagar'),
        array('colunaValor' => 'if(T015_Data_Pagamento="0000-00-00","",T015_Data_Pagamento)', 'colunaTitulo' => 'data_pagamento'),
        array('colunaValor' => 'format(T015_Valor_Total,2,"pt_BR")', 'colunaTitulo' => 'valor_pagar'),
        array('colunaValor' => 'D032_Descricao', 'colunaTitulo' => 'grupo_pagar'),
        array('colunaValor' => 'D014_SubConta', 'colunaTitulo' => 'conta_pagar'),
        array('colunaValor' => 'datediff(if(T015_Data_Pagamento=\'0000-00-00\',curdate(),T015_Data_Pagamento),T015_Data_Vencimento)', 'colunaTitulo' => 'atraso_pagamento'),
        array('colunaValor' => 'D024_Nome_Empresa', 'colunaTitulo' => 'fornecedor_conta_pagar'),

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
        LEFT JOIN D032 ON T015_D032_Id=D032_Id
        LEFT JOIN D014 ON T015_D014_Id=D014_Id
        LEFT JOIN D024 ON D024_Id=T015_D024_Id
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

