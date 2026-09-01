<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /api-rel-get-contasReceberDetalhes/
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
        array('colunaValor' => 'T002_Id', 'colunaTitulo' => 'id_receber'),
        array('colunaValor' => 'format(T002_Valor_Juros,2,"pt_BR")', 'colunaTitulo' => 'valor_juros_receber'),
        array('colunaValor' => 'format(T002_Valor_Taxas,2,"pt_BR")', 'colunaTitulo' => 'valor_taxas_receber'),
        array('colunaValor' => 'format(T002_Valor_Desconto,2,"pt_BR")', 'colunaTitulo' => 'valor_desconto_receber'),
        array('colunaValor' => 'format(T002_Valor_Saldo,2,"pt_BR")', 'colunaTitulo' => 'valor_saldo_receber'),
        array('colunaValor' => 'format(T002_Percentual_Comissao,2,"pt_BR")', 'colunaTitulo' => 'percentual_comissao_receber'),
        array('colunaValor' => 'format(T002_Valor_Comissao,5,"pt_BR")', 'colunaTitulo' => 'valor_comissao_receber'),
        array('colunaValor' => 'format(T002_Valor_Duplicata,2,"pt_BR")', 'colunaTitulo' => 'valor_sem_juros_receber'),
        array('colunaValor' => 'format(T002_Valor_Recebido,2,"pt_BR")', 'colunaTitulo' => 'valor_recebido_receber'),
        array('colunaValor' => 'if(T002_Data_Previsao_Recebimento="0000-00-00","",T002_Data_Previsao_Recebimento) ', 'colunaTitulo' => 'data_previsao_recebimento'),
        array('colunaValor' => 'datediff(if(T002_Data_Recebimento=\'0000-00-00\',curdate(),T002_Data_Recebimento),T002_Data_Vencimento)', 'colunaTitulo' => 'dias_atraso_receber'),
    );

    //montando clausula where
    $where = $API001->montarWhere($where,"T002_Data_Emissao",">=",$dataInicio);
    $where = $API001->montarWhere($where,"T002_Data_Emissao","<=",$dataFinal);
    $where = $API001->montarWhere($where,"T002_Flag_Cancelada","=","N");
    $where = $API001->montarWhere($where,"IF(T002_T002_Id_Agrupado > 0,'S','N')","=","N");
    $where = gInsertExtraWhere($where, "$extraFiltro", true);

    //  Definição da SQL
    $from = 'T002';

    $extra = <<<EOT
        LEFT JOIN T007 ON T007_Id=T002_T007_Id
        LEFT JOIN C007 ON C007_Id=T007_C007_Id_Vendedor_Interno
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
