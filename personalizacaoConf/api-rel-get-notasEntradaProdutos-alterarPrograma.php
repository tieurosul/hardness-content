<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /api-rel-get-notasEntradaProdutos/
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
			
	//--------------------------------- Produtos das Notas de entrada no T013------------------------------

	$grid = array(
		array('colunaValor' => '(T014_T013_Id+10000000)', 'colunaTitulo' => 'id_nf_entrada'),
		array('colunaValor' => '(T014_Id+10000000)', 'colunaTitulo' => 'id_item_nf_entrada'),
		array('colunaValor' => 'D001_Id', 'colunaTitulo' => 'id_produto'),
		array('colunaValor' => 'format(T014_Quantidade,3,"pt_BR")', 'colunaTitulo' => 'quantidade_nf_entrada'),
		array('colunaValor' => 'format(T014_Valor_Custo_Unitario,10,"pt_BR")', 'colunaTitulo' => 'valor_unitario_nf_entrada'),
		array('colunaValor' => 'format(T014_Valor_Total_Custo,2,"pt_BR")', 'colunaTitulo' => 'valor_total_nf_entrada'),
		array('colunaValor' => 'format(T014_Valor_Frete,3,"pt_BR")', 'colunaTitulo' => 'valor_frete_produto_nf_entrada'),
		array('colunaValor' => 'D006_Flag_Venda_Compra_Outros', 'colunaTitulo' => 'tipo_cfop'),
		array('colunaValor' => 'D006_Id', 'colunaTitulo' => 'id_cfop'),
		array('colunaValor' => 'vendedor.C007_Id', 'colunaTitulo' => 'id_vendedor'),
	);

	//montando clausula where
	$where = "";
	$where = $API001->montarWhere($where,"T013_Data_Emissao",">=",$dataInicio);
	$where = $API001->montarWhere($where,"T013_Data_Emissao","<=",$dataFinal);
	$where = $API001->montarWhere($where,"T013_Flag_Cancelada","=",'N');
	//$where = $API001->montarWhere($where,"ifnull(D001_D060_Id,0)",">",0);
	//$where = $API001->montarWhere($where,"ifnull(D001_C008_Id,0)",">",0);
	$where = gInsertExtraWhere($where, "$extraFiltro", true);

	//  Definição da SQL
	$from = 'T014';

	$extra = <<<EOT
		LEFT JOIN T013 ON T013_Id=T014_T013_Id
		LEFT JOIN D009 ON D009_ID=T014_D009_Id
		LEFT JOIN D049 ON D049_ID=D009_D049_Id
		LEFT JOIN D001 ON D001_ID=D049_D001_Id
		LEFT JOIN D006 ON D006_Id=T014_D006_Id
		LEFT JOIN T225 ON T225_Id=T014_T225_Id
		LEFT JOIN T219 ON T225_T219_Id=T219_Id
		LEFT JOIN T004 ON T004_Id=T219_T004_Id
		LEFT JOIN T003 ON T003_Id=T004_T003_Id
		LEFT JOIN C007 AS vendedor ON vendedor.C007_Id=T003_C007_Id
		{$where}
		GROUP BY T014_Id
EOT;
	
	list($geradoSqlT013, $geradoDadosT013) = $g['sqlAuto']->gerarSQLGrid($grid, $from, $extra,0.00,false,false,false,180000,true);

	//---------------------------------Produtos das Notas de entrada no T007------------------------------
	$grid = array(
		array('colunaValor' => 'T007ENT.T007_Id', 'colunaTitulo' => 'id_nf_entrada'),
		array('colunaValor' => 'T008ENT.T008_Id', 'colunaTitulo' => 'id_item_nf_entrada'),
		array('colunaValor' => 'D001_Id', 'colunaTitulo' => 'id_produto'),
		array('colunaValor' => 'format(T008ENT.T008_Quantidade,3,"pt_BR")', 'colunaTitulo' => 'quantidade_nf_entrada'),
		array('colunaValor' => 'format(T008ENT.T008_Valor_Preco_Sem_Desconto_Unitario,10,"pt_BR")', 'colunaTitulo' => 'valor_unitario_nf_entrada'),
		array('colunaValor' => 'format(T008ENT.T008_Valor_Total_Preco_Sem_Desconto,2,"pt_BR")', 'colunaTitulo' => 'valor_total_nf_entrada'),
		array('colunaValor' => 'format(T008ENT.T008_Valor_Total_Frete,3,"pt_BR")', 'colunaTitulo' => 'valor_frete_produto_nf_entrada'),
		array('colunaValor' => 'D006_Flag_Venda_Compra_Outros', 'colunaTitulo' => 'tipo_cfop'),
		array('colunaValor' => 'D006_Id', 'colunaTitulo' => 'id_cfop'),
		array('colunaValor' => 'vendedor.C007_Id', 'colunaTitulo' => 'id_vendedor'),
	);

	//montando clausula where
	$where = "";
	$where = $API001->montarWhere($where,"T007ENT.T007_Flag_Entrada_Saida","=",'E');
	$where = $API001->montarWhere($where,"T007ENT.T007_Data_Emissao",">=",$dataInicio);
	$where = $API001->montarWhere($where,"T007ENT.T007_Data_Emissao","<=",$dataFinal);
	$where = $API001->montarWhere($where,"T007ENT.T007_Flag_Cancelada","=",'N');
	//$where = $API001->montarWhere($where,"D006_Flag_Entrada_Saida ","=",'E');
	//$where = $API001->montarWhere($where,"ifnull(D001_D060_Id,0)",">",0);
	//$where = $API001->montarWhere($where,"ifnull(D001_C008_Id,0)",">",0);
	$where = gInsertExtraWhere($where, "$extraFiltro", true);

	//  Definição da SQL
	$from = 'T008 AS T008ENT';

	$extra = <<<EOT
		LEFT JOIN T007 AS T007ENT ON T007ENT.T007_Id=T008ENT.T008_T007_Id
		LEFT JOIN D009 ON D009_Id=T008ENT.T008_D009_Id
		LEFT JOIN D049 ON D049_Id=D009_D049_Id
		LEFT JOIN D001 ON D001_Id=D049_D001_Id
		LEFT JOIN D006 ON D006_Id=T008ENT.T008_D006_Id
		LEFT JOIN T008 AS T008VEN ON T008VEN.T008_Id=T008ENT.T008_T008_Id
		LEFT JOIN T007 AS T007VEN ON T007VEN.T007_Id=T008VEN.T008_T007_Id
		LEFT JOIN C007 AS vendedor ON vendedor.C007_Id=T007VEN.T007_C007_Id_Vendedor_Interno
		{$where}
		GROUP BY T008ENT.T008_Id
EOT;
	list($geradoSqlT007, $geradoDadosT007) = $g['sqlAuto']->gerarSQLGrid($grid, $from, $extra,0.00,false,false,false,90000,true);

	$geradoDados = array_merge($geradoDadosT013,$geradoDadosT007);

	//validando se retornaram dados da query
	$semDados = $API001->tratarRetornoVazio($geradoDados);
	if(is_array($semDados)){
		echo json_encode($semDados, JSON_UNESCAPED_UNICODE);
		die();
	}

	echo json_encode($geradoDados, JSON_UNESCAPED_UNICODE);

	die();

