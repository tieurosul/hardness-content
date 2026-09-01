<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /api-rel-get-produtos/
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

    $grid = array(
        array('colunaValor' => 'D001_Id', 'colunaTitulo' => 'id_produto'),
        array('colunaValor' => 'D001_Codigo_Produto', 'colunaTitulo' => 'codigo_produto'),
        array('colunaValor' => 'D001_Codigo_Barras', 'colunaTitulo' => 'ean_produto'),
        array('colunaValor' => 'D001_Descricao_Produto', 'colunaTitulo' => 'descricao_produto'),
        array('colunaValor' => 'D082_Marca', 'colunaTitulo' => 'marca_produto'),
        array('colunaValor' => 'C008_Tipo', 'colunaTitulo' => 'departamento_produto'),
        array('colunaValor' => 'D060_Descricao_Grupo', 'colunaTitulo' => 'setor_produto'),
        array('colunaValor' => 'D003_Nome_Linha', 'colunaTitulo' => 'grupo_produto'),
        array('colunaValor' => 'D002_Descricao_Produto', 'colunaTitulo' => 'sub_grupo_produto'),
        array('colunaValor' => 'if(D049_Data_Cadastro="0000-00-00","",D049_Data_Cadastro)', 'colunaTitulo' => 'data_cadastro_produto'),
        array('colunaValor' => 'D001_Descricao_Ingles', 'colunaTitulo' => 'descricao_produto_ingles'),
        array('colunaValor' => 'D001A_Codigo_IMPA', 'colunaTitulo' => 'codigo_impa'),
        array('colunaValor' => 'D005_Classificacao_Fiscal', 'colunaTitulo' => 'classificacao_fiscal'),
        array('colunaValor' => 'format(D009_Preco_1,2,"pt_BR")', 'colunaTitulo' => 'preco_1'),
        array('colunaValor' => 'IF(D001_Flag_Ativo != "N","SIM","NAO")', 'colunaTitulo' => 'ativo_produto'),
        array('colunaValor' => 'IF(D001_Flag_Pre_Cadastro != "N","SIM","NAO")', 'colunaTitulo' => 'pre_cadastro'),
        array('colunaValor' => 'IF(D001A_Flag_Validade != "N","SIM","NAO")', 'colunaTitulo' => 'tem_validade'),
        array('colunaValor' => 'IF(D049_Flag_Nao_Comprar = "N","SIM","NAO")', 'colunaTitulo' => 'comprar_produto'),
        array('colunaValor' => 'D001_Flag_Tipo_Item', 'colunaTitulo' => 'categoria_produto', 'callback' => 'validaCampoCategoriaProduto'),
        array('colunaValor' => 'D049_Flag_Tipo', 'colunaTitulo' => 'tipo_produto', 'callback' => 'validaCampoTipoProduto'),
        array('colunaValor' => 'format(D009_Quantidade_Estoque,2,"pt_BR")', 'colunaTitulo' => 'estoque_produto'),
        array('colunaValor' => 'format(D009_Quantidade_OC,2,"pt_BR")', 'colunaTitulo' => 'oc_produto'),
        array('colunaValor' => 'format(D009_Valor_Custo_Ultima_Compra,4,"pt_BR")', 'colunaTitulo' => 'custo_ultima_compra'),
        array('colunaValor' => 'if(D009_Data_Ultima_Entrada="0000-00-00","",D009_Data_Ultima_Entrada)', 'colunaTitulo' => 'data_ultima_compra'),
        // array('colunaValor' => 'if(T066A_Data_Validade="0000-00-00","",T066A_Data_Validade)', 'colunaTitulo' => 'data_ultima_validade'),
        // array('colunaValor' => "IF(DATEDIFF(T066A_Data_Validade, CURDATE()) > 0 AND T066A_Data_Validade > '0000-00-00', DATEDIFF(T066A_Data_Validade, CURDATE()), IF(T066A_Data_Validade = '0000-00-00' OR T066A_Data_Validade IS NULL, '-', 'VENCIDO' ) )", 'colunaTitulo' => 'dias_venc'),
        // array('colunaValor' => '(SELECT T066A_Data_Validade FROM T066 WHERE T066_D009_Id = D009_Id AND (T066A_Data_Validade!="0000-00-00" AND T066A_Data_Validade!=null) LIMIT 1)', 'colunaTitulo' => 'data_validade'),
        array('colunaValor' => 'T066A_Data_Validade', 'colunaTitulo' => 'data_validade'),
        array('colunaValor' => 'T066_Quantidade_Estoque', 'colunaTitulo' => 'estoque_vencido'),
        array('colunaValor' => "CASE D049_Origem_Mercadoria WHEN ''  THEN '...' WHEN '0' THEN '0 - Nacional - exceto as indicadas nos códigos 3 a 5' WHEN '1' THEN '1 - Estrangeira - Importação direta, exceto a indicada no código 6' WHEN '2' THEN '2 - Estrangeira - Adquirida no mercado interno, exceto a indicada no código 7' WHEN '3' THEN '3 - Nacional - mercadoria ou bem com Conteúdo de Importação superior a 40%' WHEN '4' THEN '4 - Nacional - cuja produção tenha sido feita em conformidade com os processos produtivos básicos de que tratam as legislações citadas nos Ajustes' WHEN '5' THEN '5 - Nacional - mercadoria ou bem com Conteúdo de Importação inferior ou igual a 40%' WHEN '6' THEN '6 - Estrangeira - Importação direta, sem similar nacional, constante em lista da CAMEX' WHEN '7' THEN '7 - Estrangeira - Adquirida no mercado interno, sem similar nacional, constante em lista da CAMEX' WHEN '8' THEN '8 - Nacional - Mercadoria ou bem com Conteúdo de Importação superior a 70% (setenta por cento)' ELSE NULL END", 'colunaTitulo' => 'origem_mercadoria'),
        array('colunaValor' => 'D001A_Flag_Doc_Num_Serie', 'colunaTitulo' => 'doc_num_serie'),
        array('colunaValor' => 'D001_Especificacoes', 'colunaTitulo' => 'especificacoes_produtos'),
        array('colunaValor' => 'D001_Peso_Unitario_Kg', 'colunaTitulo' => 'peso_liquido_produtos'),
        array('colunaValor' => 'D001_Peso_Unitario_Bruto', 'colunaTitulo' => 'peso_bruto_produtos'),
        array('colunaValor' => 'D001A_Altura', 'colunaTitulo' => 'altura_produtos'),
        array('colunaValor' => 'D001A_Largura', 'colunaTitulo' => 'largura_produtos'),
        array('colunaValor' => 'D001A_Comprimento', 'colunaTitulo' => 'comprimento_produtos'),
        array('colunaValor' => 'D001_Cubagem_Unitaria', 'colunaTitulo' => 'cubagem_produtos'),
        
    );

    //montando clausula where
    $where = $API001->montarWhere($where,"D001_Codigo_Produto","!=",' ');
    $where = $API001->montarWhere($where,"D001_Descricao_Produto","!="," ");
    $where = $API001->montarWhere($where,"D001_Flag_Ativo","=","S");

    //  DefiniÃ§Ã£o da SQL
    $from = 'D001';

    $extra = <<<EOT
        LEFT JOIN D049 ON D049_D001_Id=D001_Id
        LEFT JOIN D082 ON D082_id=D049_D082_Id
        LEFT JOIN C008 ON C008_Id=D001_C008_Id
        LEFT JOIN D060 ON D060_Id=D001_D060_Id
        LEFT JOIN D003 ON D003_Id=D001_D003_Id    
        LEFT JOIN D002 ON D002_Id=D001_D002_Id
        LEFT JOIN D001A ON D001A_D001_Id = D001_Id
        LEFT JOIN D005 ON D001_D005_Id = D005_Id
        LEFT JOIN D009 ON D049_Id = D009_D049_Id AND D009_C004_Id = 1
        LEFT JOIN T066 ON D009_Id = T066_D009_Id AND T066_D004_Id > 0
        LEFT JOIN T066A ON T066_Id = T066A_T066_Id AND T066A_Data_Validade > 0
        {$where}
        GROUP BY D001_Codigo_Produto
    EOT;

    log("sql: ".json_encode($grid,JSON_PRETTY_PRINT)."\n{$from}\n{$extra}");
    list($geradoSql, $geradoDados) = $g['sqlAuto']->gerarSQLGrid($grid, $from, $extra,0.00,false,false,false,120000,true);

    //validando se retornaram dados da query
    $semDados = $API001->tratarRetornoVazio($geradoDados);
    if(is_array($semDados)){
        echo json_encode($semDados, JSON_UNESCAPED_UNICODE);
        die();
    }

    echo json_encode($geradoDados, JSON_UNESCAPED_UNICODE);

    die();

















