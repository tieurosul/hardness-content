<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /api-rel-get-orcamentosProdutos/
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
		array('colunaValor' => 'T004_Id', 'colunaTitulo' => 'id_item_orcamento'),
		array('colunaValor' => 'T004_T003_Id', 'colunaTitulo' => 'numero_orcamento'),
		array('colunaValor' => 'D001_Id', 'colunaTitulo' => 'id_produto'),
		array('colunaValor' => 'format(T004_Quantidade,5,"pt_BR")', 'colunaTitulo' => 'quantidade_orcamento'),
		array('colunaValor' => 'format(T004_Quantidade_Confirmada,3,"pt_BR")', 'colunaTitulo' => 'quantidade_confirmada_orcamento'),
		array('colunaValor' => 'format(T004_Valor_Preco_Unitario,10,"pt_BR")', 'colunaTitulo' => 'valor_unitario_orcamento'),
		array('colunaValor' => 'format(T004_Valor_Total_Preco,2,"pt_BR")', 'colunaTitulo' => 'valor_total_orcamento'),
		array('colunaValor' => 'format(T004_Valor_Custo_Unitario,5,"pt_BR")', 'colunaTitulo' => 'valor_custo_unitario_orcamento'),
		array('colunaValor' => 'D006_Flag_Venda_Compra_Outros', 'colunaTitulo' => 'tipo_cfop'),
		array('colunaValor' => 'D006_Id', 'colunaTitulo' => 'id_cfop'),
		array('colunaValor' => 'format(T004_Valor_ICMS,5,"pt_BR")', 'colunaTitulo' => 'valor_icms_orcamento'),
		array('colunaValor' => 'format(T004_Valor_IPI,5,"pt_BR")', 'colunaTitulo' => 'valor_ipi_orcamento'),
		array('colunaValor' => 'format(T004_Valor_ST,5,"pt_BR")', 'colunaTitulo' => 'valor_st_orcamento'),
		array('colunaValor' => 'format(T004_Valor_PIS,5,"pt_BR")', 'colunaTitulo' => 'valor_pis_orcamento'),
		array('colunaValor' => 'format(T004_Valor_COFINS,5,"pt_BR")', 'colunaTitulo' => 'valor_cofins_orcamento'),
		array('colunaValor' => 'format(T004_Valor_IRPJ,5,"pt_BR")', 'colunaTitulo' => 'valor_irpj_orcamento'),
		array('colunaValor' => 'format(T004_Valor_CSLL,5,"pt_BR")', 'colunaTitulo' => 'valor_clss_orcamento'),
	);

	//montando clausula where
	$where = $API001->montarWhere($where,"T003_Data_Emissao",">=",$dataInicio);
	$where = $API001->montarWhere($where,"T003_Data_Emissao","<=",$dataFinal);
	$where = gInsertExtraWhere($where, "$extraFiltro", true);

	//  Definição da SQL
	$from = 'T004';

	$extra = <<<EOT
		LEFT JOIN T003 ON T003_Id=T004_T003_Id
		LEFT JOIN D009 ON D009_Id=T004_D009_Id
		LEFT JOIN D049 ON D049_Id=D009_D049_Id
		LEFT JOIN D001 ON D001_Id=D049_D001_Id
		LEFT JOIN D006 ON D006_Id=T004_D006_Id
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

