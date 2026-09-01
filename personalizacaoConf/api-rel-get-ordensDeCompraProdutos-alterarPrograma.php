<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /api-rel-get-ordensDeCompraProdutos/
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
        array('colunaValor' => 'T225_T224_Id', 'colunaTitulo' => 'numero_oc'),
        array('colunaValor' => 'T225_Id', 'colunaTitulo' => 'id_item_oc'),
        array('colunaValor' => 'D001_Id', 'colunaTitulo' => 'id_produto'),
        array('colunaValor' => 'format(T225_Quantidade,5,"pt_BR")', 'colunaTitulo' => 'quantidade_oc'),
        array('colunaValor' => 'format(T225_Valor_Preco_Unitario,5,"pt_BR")', 'colunaTitulo' => 'valor_unitario_oc'),
        array('colunaValor' => 'format(T225_Valor_Total_Preco,2,"pt_BR")', 'colunaTitulo' => 'valor_total_oc'),
        array('colunaValor' => 'D006_Flag_Venda_Compra_Outros', 'colunaTitulo' => 'tipo_cfop'),
        array('colunaValor' => 'D006_Id', 'colunaTitulo' => 'id_cfop'),
        array('colunaValor' => 'T225_T219_Id', 'colunaTitulo' => 'id_item_cotacao'),
        array('colunaValor' => 'T003_C007_Id_Vendedor_Interno', 'colunaTitulo' => 'id_vendedor'),
        array('colunaValor' => 'T225_Observacao_Entrega', 'colunaTitulo' => 'entrega_dias'),
    );

    //montando clausula where
    $where = $API001->montarWhere($where,"T224_Data_Emissao",">=",$dataInicio);
    $where = $API001->montarWhere($where,"T224_Data_Emissao","<=",$dataFinal);
    $where = $API001->montarWhere($where,"T225_Flag_Cancelada","=","N");
    $where = gInsertExtraWhere($where, "$extraFiltro", true);

    //  Definição da SQL
    $from = 'T225';

    $extra = <<<EOT
        LEFT JOIN T224 ON T224_Id=T225_T224_Id
        LEFT JOIN D009 ON D009_Id=T225_D009_Id
        LEFT JOIN D049 ON D049_Id=D009_D049_Id 
        LEFT JOIN D001 ON D001_Id=D049_D001_Id
        LEFT JOIN D006 ON D006_Id=T225_D006_Id
        LEFT JOIN T219 ON T225_T219_Id=T219_Id
        LEFT JOIN T004 ON T004_Id=T219_T004_Id
        LEFT JOIN T003 ON T003_Id=T004_T003_Id
        {$where}
        GROUP BY T225_Id
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

