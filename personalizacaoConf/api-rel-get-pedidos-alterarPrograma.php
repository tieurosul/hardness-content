<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /api-rel-get-pedidos/
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
        array('colunaValor' => 'T005_Id', 'colunaTitulo' => 'numero_pedido'),
        array('colunaValor' => 'T005_D024_Id', 'colunaTitulo' => 'codigo_cliente'),
        array('colunaValor' => 'T005_C004_Id', 'colunaTitulo' => 'id_empresa'),
        array('colunaValor' => 'T005_D022_Id', 'colunaTitulo' => 'codigo_transportadora'),
        array('colunaValor' => 'D036_Tipo_Transporte', 'colunaTitulo' => 'tipo_transporte_pedido'),
        array('colunaValor' => 'codigo_usuario.C007_Id', 'colunaTitulo' => 'codigo_usuario'),
        array('colunaValor' => 'codigo_vendedor_interno.C007_Id', 'colunaTitulo' => 'codigo_vendedor_interno'),
        array('colunaValor' => 'codigo_vendedor_externo.C007_Id', 'colunaTitulo' => 'codigo_vendedor_externo'),
        array('colunaValor' => 'codigo_supervisor.C007_Id', 'colunaTitulo' => 'codigo_supervisor'),
        array('colunaValor' => 'codigo_supervisor2.C007_Id', 'colunaTitulo' => 'codigo_supervisor2'),
        array('colunaValor' => 'codigo_vendedor_substituto.C007_Id', 'colunaTitulo' => 'codigo_vendedor_substituto'),
        array('colunaValor' => 'if(T005_Data_Emissao="0000-00-00","",T005_Data_Emissao)', 'colunaTitulo' => 'data_emissao_pedido'),
        array('colunaValor' => 'T005_Canal_Vendas_Ecommerce', 'colunaTitulo' => 'canal_venda_pedido'),
        array('colunaValor' => 'T005_Nome_Status', 'colunaTitulo' => 'status_pedido'),
        array('colunaValor' => 'format(T005_Valor_Total,2,"pt_BR")', 'colunaTitulo' => 'valor_total_pedido'),
        array('colunaValor' => 'T005_Ordem_Compra', 'colunaTitulo' => 'ordem_compra_cliente'),
        array('colunaValor' => 'T005_Percentual_Estoque_Disponivel', 'colunaTitulo' => 'percentual_estoque_disponivel'),
        array('colunaValor' => 'T005_Observacao_Comercial', 'colunaTitulo' => 'observacao_comercial'),
    );

    //montando clausula where
    $where = $API001->montarWhere($where,"T005_Data_Emissao",">=",$dataInicio);
    $where = $API001->montarWhere($where,"T005_Data_Emissao","<=",$dataFinal);
    $where = $API001->montarWhere($where,"(T005_T005_Id_Agrupado<=0 OR T005_T005_Id_Agrupado IS NULL)",false);
    $where = gInsertExtraWhere($where, "$extraFiltro", true);

    //  Definição da SQL
    $from = 'T005';

    $extra = <<<EOT
        LEFT JOIN D036 ON T005_D036_Id=D036_Id
        LEFT JOIN C007 AS codigo_usuario ON codigo_usuario.C007_Id=T005_C007_Id
        LEFT JOIN C007 AS codigo_vendedor_interno ON codigo_vendedor_interno.C007_Id=T005_C007_Id_Vendedor_Interno
        LEFT JOIN C007 AS codigo_supervisor ON codigo_supervisor.C007_Id=codigo_vendedor_interno.C007_C007_Id
        LEFT JOIN C007 AS codigo_supervisor2 ON codigo_supervisor2.C007_Id=codigo_vendedor_interno.C007_C007_Id_2
        LEFT JOIN C007 AS codigo_vendedor_externo ON codigo_vendedor_externo.C007_Id=T005_C007_Id_Vendedor_Externo
        LEFT JOIN C007 AS codigo_vendedor_substituto ON codigo_vendedor_substituto.C007_Id=codigo_vendedor_interno.C007_Id_Vendedor_Substituto
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
