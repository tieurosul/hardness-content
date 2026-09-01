<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /api-rel-get-lancamentosBancarios/
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
        array('colunaValor' => 'T019_Id', 'colunaTitulo' => 'id_lancamento'),
        array('colunaValor' => 'T019_D024_Id', 'colunaTitulo' => 'codigo_cliente'),
        array('colunaValor' => 'T019_D014_Id', 'colunaTitulo' => 'id_conta'),
        array('colunaValor' => 'T019_C004_Id', 'colunaTitulo' => 'id_empresa'),
        array('colunaValor' => 'T019_D027_Id', 'colunaTitulo' => 'id_portador_receber'),
        array('colunaValor' => 'T019_D031_Id', 'colunaTitulo' => 'id_portador_pagar'),
        array('colunaValor' => 'T019_Referente', 'colunaTitulo' => 'descricao_lancamento'),
        array('colunaValor' => 'T019_Complemento', 'colunaTitulo' => 'complemento_lancamento'),
        array('colunaValor' => 'if(T019_Data_Lancamento="0000-00-00","",T019_Data_Lancamento)', 'colunaTitulo' => 'data_lancamento'),
        array('colunaValor' => 'format(T019_Valor_Lancamento,2,"pt_BR")', 'colunaTitulo' => 'valor_lancamento'),
        array('colunaValor' => 'D007_Nome_Banco', 'colunaTitulo' => 'nome_banco'),
    );

    //montando clausula where
    $where = $API001->montarWhere($where,"T019_Data_Lancamento",">=",$dataInicio);
    $where = $API001->montarWhere($where,"T019_Data_Lancamento","<=",$dataFinal);
    $where = gInsertExtraWhere($where, "$extraFiltro", true);

    //  Definição da SQL
    $from = 'T019';

    $extra = <<<EOT
        LEFT JOIN D007 ON D007_Id = T019_D007_Id
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
