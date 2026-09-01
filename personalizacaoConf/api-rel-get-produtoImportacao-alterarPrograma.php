<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /api-rel-get-produtoImportacao/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

    $retorno = $API003->auth($_GET['API_AUTH'], false);

    $dataInicio = $_GET['DATAINICIAL']; 
    $dataFinal = $_GET['DATAFINAL'];
    $extraFiltro = extraFiltro();		

    if(is_array($retorno)){
        echo json_encode($retorno, JSON_UNESCAPED_UNICODE);
        die();
    }

    $grid = array(
        array('colunaValor' => 'D001_Id', 'colunaTitulo' => 'id_produto'),
        array('colunaValor' => 'D001_Codigo_Produto', 'colunaTitulo' => 'sku_produto'),
        array('colunaValor' => 'D001_Descricao_Produto', 'colunaTitulo' => 'descricao_produto'),
        array('colunaValor' => 'D009_C004_Id', 'colunaTitulo' => 'id_empresa'),
        array('colunaValor' => 'D009_Id', 'colunaTitulo' => 'id_produto_empresa'),
        array('colunaValor' => "T007_Numero_Nota_Fiscal", 'colunaTitulo' => 'numero_nota_fiscal_importacao'),
        array('colunaValor' => "T007_Data_Emissao", 'colunaTitulo' => 'emissao_nota_fiscal_importacao'),
        array('colunaValor' => "T075_Numero_DI", 'colunaTitulo' => 'numero_declaracao_importacao'),
        array('colunaValor' => 'format(T075_cotacao_ME,4,"pt_BR")', 'colunaTitulo' => 'cotacao_me'),
        array('colunaValor' => 'format(T055_Valor_Custo_Unitario,4,"pt_BR")', 'colunaTitulo' => 'valor_unitario_me'),
        array('colunaValor' => 'format(T001_Valor_Custo_Unitario,4,"pt_BR")', 'colunaTitulo' => 'valor_custo_compra'),
        array('colunaValor' => 'format(D009_Valor_Custo_Ultima_Compra,4,"pt_BR")', 'colunaTitulo' => 'valor_custo_ultima_compra'),
        array('colunaValor' => 'format(D009_Valor_Custo_Medio_Unitario,4,"pt_BR")', 'colunaTitulo' => 'valor_custo_medio_unitario'),
        array('colunaValor' => 'format(D009_Valor_Preco_Tabela,4,"pt_BR")', 'colunaTitulo' => 'valor_custo_manual'),
        array('colunaValor' => 'format(T055_Valor_Total_Frete,4,"pt_BR")', 'colunaTitulo' => 'valor_frete'),
        array('colunaValor' => 'format(T055_Peso_Kg_Liquido_Unitario,4,"pt_BR")', 'colunaTitulo' => 'peso_produto_declaracao_importacao'),
        array('colunaValor' => 'format( T001_Quantidade,2,"pt_BR")', 'colunaTitulo' => 'quantidade_produto'),
    );

    //montando clausula where
    $where = $API001->montarWhere($where,"T001_Data_Lancamento",">=",$dataInicio);
    $where = $API001->montarWhere($where,"T001_Data_Lancamento","<=",$dataFinal);
    $where = $API001->montarWhere($where,"T001_Flag_Operacao","=","E");

    $where = $API001->montarWhere($where,"T001_T055_Id","!="," ");
    $where = $API001->montarWhere($where,"D001_Flag_Ativo","=","S");
    $where = $API001->montarWhere($where,"D001_Codigo_Produto","!="," ");
    $where = $API001->montarWhere($where,"D001_Descricao_Produto","!="," ");

    //  Definição da SQL
    $from = 'T001';

    $extra = <<<EOT
        LEFT JOIN D009 ON T001_D009_Id = D009_Id
        LEFT JOIN D049 ON D009_D049_Id = D049_Id
        LEFT JOIN D001 ON D049_D001_Id = D001_Id
        LEFT JOIN T055 ON T001_T055_Id = T055_Id
        LEFT JOIN T075 ON T055_T075_Id = T075_Id
        LEFT JOIN T007 ON T007_T075_Id = T075_Id
        {$where}
        GROUP BY T001_Id
        ORDER BY D009_Id ASC
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
