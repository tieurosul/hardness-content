<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /api-rel-get-notasSaidaProdutos/
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
		array('colunaValor' => 'T008_Id', 'colunaTitulo' => 'id_item_nf_saida'),
		array('colunaValor' => 'T008_T007_Id', 'colunaTitulo' => 'id_nf_saida'),
		array('colunaValor' => 'D001_Id', 'colunaTitulo' => 'id_produto'),
		array('colunaValor' => 'format(T008_Quantidade,5,"pt_BR")', 'colunaTitulo' => 'quantidade_nf_saida'),
		array('colunaValor' => 'format(T008_Valor_Preco_Unitario,10,"pt_BR")', 'colunaTitulo' => 'valor_unitario_nf_saida'),
		array('colunaValor' => 'format(T008_Percentual_Margem,2,"pt_BR")', 'colunaTitulo' => 'percentual_margem_nf_saida'),
		array('colunaValor' => 'format(T008_Valor_Total_Preco,2,"pt_BR")', 'colunaTitulo' => 'valor_total_nf_saida'),
		array('colunaValor' => 'format(T008_IPV,4,"pt_BR")', 'colunaTitulo' => 'ipv_produto'),
		array('colunaValor' => 'D006_Flag_Venda_Compra_Outros', 'colunaTitulo' => 'tipo_cfop'),
		array('colunaValor' => 'D006_Id', 'colunaTitulo' => 'id_cfop'),
		array('colunaValor' => 'T008_T006_Id', 'colunaTitulo' => 'id_item_pedido'),
		array('colunaValor' => 'format(T008_Valor_COFINS,2,"pt_BR")', 'colunaTitulo' => 'cofins_produto'),
		array('colunaValor' => 'format(T008_Valor_CSLL,2,"pt_BR")', 'colunaTitulo' => 'csll_produto'),
		array('colunaValor' => 'format(T008_Valor_ICMS,2,"pt_BR")', 'colunaTitulo' => 'icms_produto'),
		array('colunaValor' => 'format(T008_Valor_IRPJ,2,"pt_BR")', 'colunaTitulo' => 'irpj_produto'),
		array('colunaValor' => 'format(T008_Valor_PIS,2,"pt_BR")', 'colunaTitulo' => 'pis_produto'),
	);

	//montando clausula where
	$where = $API001->montarWhere($where,"T007_Data_Emissao",">=",$dataInicio);
	$where = $API001->montarWhere($where,"T007_Data_Emissao","<=",$dataFinal);
	$where = $API001->montarWhere($where,"T007_Flag_Entrada_Saida","=",'S');
	$where = $API001->montarWhere($where,"T007_Flag_Cancelada","!=",'S');
	$where = gInsertExtraWhere($where, "$extraFiltro", true);

	//  Definição da SQL
	$from = 'T008';

	$extra = <<<EOT
		LEFT JOIN T007 ON T007_Id=T008_T007_Id
		LEFT JOIN D009 ON D009_Id=T008_D009_Id
		LEFT JOIN D049 ON D049_Id=D009_D049_Id 
		LEFT JOIN D001 ON D001_Id=D049_D001_Id
		LEFT JOIN D006 ON D006_Id=T008_D006_Id
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

