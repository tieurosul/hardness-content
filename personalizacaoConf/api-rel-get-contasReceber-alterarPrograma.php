<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /api-rel-get-contasReceber/
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
        array('colunaValor' => 'T002_D024_Id', 'colunaTitulo' => 'codigo_cliente'),
        array('colunaValor' => 'T002_C004_Id', 'colunaTitulo' => 'id_empresa'),
        array('colunaValor' => 'T002_T007_Id', 'colunaTitulo' => 'id_NF'),
        array('colunaValor' => 'T007_C007_Id_Vendedor_Interno', 'colunaTitulo' => 'codigo_vendedor_interno'),
        array('colunaValor' => 'T002_D014_Id', 'colunaTitulo' => 'id_conta'),
        array('colunaValor' => 'T002_D027_Id', 'colunaTitulo' => 'id_portador_receber'),
        array('colunaValor' => 'T002_Numero_Duplicata', 'colunaTitulo' => 'num_duplicata_receber'),
        array('colunaValor' => 'if(T002_Data_Emissao="0000-00-00","",T002_Data_Emissao)', 'colunaTitulo' => 'data_emissao_receber'),
        array('colunaValor' => 'if(T002_Data_Vencimento="0000-00-00","",T002_Data_Vencimento)', 'colunaTitulo' => 'data_vencimento_receber'),
        array('colunaValor' => 'if(T002_Data_Recebimento="0000-00-00","",T002_Data_Recebimento)', 'colunaTitulo' => 'data_recebimento'),
        array('colunaValor' => 'format(T002_Valor_Total,2,"pt_BR")', 'colunaTitulo' => 'valor_receber'),
        array('colunaValor' => 'D032_Descricao', 'colunaTitulo' => 'grupo_receber'),
        array('colunaValor' => 'D014_SubConta', 'colunaTitulo' => 'conta_receber'),
        array('colunaValor' => 'REPLACE(D085_Mensagem,"\n","")', 'colunaTitulo' => 'mensagem_cobranca'),
        array('colunaValor' => 'IF(T002_Flag_Status = "1", "NORMAL", IF(T002_Flag_Status = "2", "PERDIDO", IF(T002_Flag_Status = "3", "COBRANCA", "DEVOLUCAO/REFATURAMENTO")))', 'colunaTitulo' => 'status_receber'),
        array('colunaValor' => 'T002_Observacao', 'colunaTitulo' => 'observacao_cobranca'),        
        // array('colunaValor' => 'datediff(if(T002_Data_Recebimento=\'0000-00-00\',curdate(),T002_Data_Recebimento),T002_Data_Vencimento)', 'colunaTitulo' => 'atraso_recebimento'),
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
        LEFT JOIN D032 ON D032_Id=T002_D032_Id
        LEFT JOIN D014 ON D014_Id=T002_D014_Id
        LEFT JOIN D085 ON T002_Id=D085_T002_Id AND D085_Id = (SELECT MAX(D085_Id) FROM D085 Where D085_T002_Id = T002_Id)
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

