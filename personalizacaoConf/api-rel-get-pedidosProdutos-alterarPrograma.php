<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /api-rel-get-pedidosProdutos/
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
		array('colunaValor' => 'T006_T005_Id', 'colunaTitulo' => 'numero_pedido'),
		array('colunaValor' => 'T006_T004_Id', 'colunaTitulo' => 'id_item_orcamento'),
		array('colunaValor' => 'T006_Id', 'colunaTitulo' => 'id_item_pedido'),
		array('colunaValor' => 'D001_Id', 'colunaTitulo' => 'id_produto'),
		array('colunaValor' => 'format(T006_Quantidade,5,"pt_BR")', 'colunaTitulo' => 'quantidade_pedido'),
		array('colunaValor' => 'format(T006_Valor_Preco_Unitario,10,"pt_BR")', 'colunaTitulo' => 'valor_unitario_pedido'),
		array('colunaValor' => 'format(T006_Valor_Total_Preco,2,"pt_BR")', 'colunaTitulo' => 'valor_total_pedido'),
		array('colunaValor' => 'format(T006_Valor_Frete_Unitario,10,"pt_BR")', 'colunaTitulo' => 'valor_frete_unitario_pedido'),
		array('colunaValor' => 'format(T006_Valor_Total_Custo,2,"pt_BR")', 'colunaTitulo' => 'valor_custo_total_pedido'),
		array('colunaValor' => 'format(T006_Valor_Custo_Unitario,5,"pt_BR")', 'colunaTitulo' => 'valor_custo_unitario_pedido'),
		array('colunaValor' => 'format(T006_Valor_Preco_Original ,10,"pt_BR")', 'colunaTitulo' => 'valor_preco_tabela_pedido'),
		array('colunaValor' => 'format(T006_Percentual_Desconto,2,"pt_BR")', 'colunaTitulo' => 'percentual_desconto_produto_pedido'),
		array('colunaValor' => 'format(T006_Valor_Custo_Unitario,5,"pt_BR")', 'colunaTitulo' => 'valor_custo_unitario_pedido'),
		array('colunaValor' => 'format(T006_Preco_Venda_Ecommerce,5,"pt_BR")', 'colunaTitulo' => 'valor_preco_venda_ecommerce_pedido'),
		array('colunaValor' => 'format(T006_Preco_Tabela_Ecommerce,5,"pt_BR")', 'colunaTitulo' => 'valor_preco_tabela_ecommerce_pedido'),
		array('colunaValor' => 'format(T006_Valor_Desconto_Ecommerce,5,"pt_BR")', 'colunaTitulo' => 'valor_desconto_ecommerce_pedido'),
		array('colunaValor' => 'format(T006_IPV,4,"pt_BR")', 'colunaTitulo' => 'ipv_produto'),
		array('colunaValor' => 'format(T006_Percentual_Margem,2,"pt_BR")', 'colunaTitulo' => 'percentual_margem'),
		array('colunaValor' => 'D006_Flag_Venda_Compra_Outros', 'colunaTitulo' => 'tipo_cfop'),
		array('colunaValor' => 'D006_Id', 'colunaTitulo' => 'id_cfop'),
	);

	//montando clausula where
	$where = $API001->montarWhere($where,"T005_Data_Emissao",">=",$dataInicio);
	$where = $API001->montarWhere($where,"T005_Data_Emissao","<=",$dataFinal);
	$where = gInsertExtraWhere($where, "$extraFiltro", true);

	//  Definição da SQL
	$from = 'T006';

	$extra = <<<EOT
		LEFT JOIN T005 ON T005_Id=T006_T005_Id
		LEFT JOIN D009 ON D009_Id=T006_D009_Id
		LEFT JOIN D049 ON D049_Id=D009_D049_Id
		LEFT JOIN D001 ON D001_Id=D049_D001_Id
		LEFT JOIN D006 ON D006_Id=T006_D006_Id
		{$where}
EOT;
	list($geradoSql, $geradoDados) = $g['sqlAuto']->gerarSQLGrid($grid, $from, $extra,0.00,false,false,false,180000,true);

	//validando se retornaram dados da query
	$semDados = $API001->tratarRetornoVazio($geradoDados);
	if(is_array($semDados)){
		echo json_encode($semDados, JSON_UNESCAPED_UNICODE);
		die();
	}

	echo json_encode($geradoDados, JSON_UNESCAPED_UNICODE);

	die();

