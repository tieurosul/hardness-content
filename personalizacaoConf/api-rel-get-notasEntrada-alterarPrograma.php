<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /api-rel-get-notasEntrada/
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

	//--------------------------------- Notas de entrada no T013------------------------------
	$grid = array(
		array('colunaValor' => '(T013_Id+10000000)', 'colunaTitulo' => 'id_nf_entrada'),
		array('colunaValor' => 'T013_Numero_Nota_Fiscal', 'colunaTitulo' => 'num_nf_entrada'),
		array('colunaValor' => 'T013_D024_Id', 'colunaTitulo' => 'codigo_cliente'),
		array('colunaValor' => 'codigo_usuario.C007_Id', 'colunaTitulo' => 'codigo_usuario'),
		array('colunaValor' => 'T013_C004_Id', 'colunaTitulo' => 'id_empresa'),
		array('colunaValor' => 'if(T013_Data_Emissao="0000-00-00","",T013_Data_Emissao)', 'colunaTitulo' => 'data_emissao_nf_entrada'),
		array('colunaValor' => 'if(T013_Data_Entrada="0000-00-00","",T013_Data_Entrada)', 'colunaTitulo' => 'data_entrada_nf_entrada'),
		array('colunaValor' => 'IFNULL(VendedorSaida.C007_Id, vendedor.C007_Id)', 'colunaTitulo' => 'id_vendedor'),
		array('colunaValor' => 'format(T013_Valor_Total,2,"pt_BR")', 'colunaTitulo' => 'valor_total_nf_entrada'),
		array('colunaValor' => 'T013_D006_Id', 'colunaTitulo' => 'id_cfop_cabecalho'),
		array('colunaValor' => 'format(T013_Peso_Bruto,3,"pt_BR")', 'colunaTitulo' => 'peso_bruto_kg_nf_entrada'),
		array('colunaValor' => 'T007_Numero_Nota_Fiscal', 'colunaTitulo' => 'devolucao_nf'),
	);

	/*  left join T014 on T014_T013_Id = T013_Id
		left join T008 on T008_Id = T014_T008_Id
		left join T007 on T007_Id = T008_T007_Id
		left join C007 as VendedorSaida on VendedorSaida.C007_Id=T007_C007_Id_Vendedor_Interno */
	// group_concat(distinct substr(IFNULL(VendedorSaida.C007_Primeiro_Nome, Vendedor.C007_Primeiro_Nome),1,10))

	//montando clausula where
	$where = "";
	$where = $API001->montarWhere($where,"T013_Data_Emissao",">=",$dataInicio);
	$where = $API001->montarWhere($where,"T013_Data_Emissao","<=",$dataFinal);
	$where = $API001->montarWhere($where,"T013_Flag_Cancelada","=",'N');
	$where = gInsertExtraWhere($where, "$extraFiltro", true);

	//  DefiniÃ§Ã£o da SQL
	$from = 'T013';

	$extra = <<<EOT
		LEFT JOIN C007 AS codigo_usuario ON codigo_usuario.C007_Id=T013_C007_Id
		LEFT JOIN D006 ON D006_Id=T013_D006_Id
		LEFT JOIN T014 ON T014_T013_Id=T013_Id
		LEFT JOIN T008 ON T008_Id=T014_T008_Id
		LEFT JOIN T007 ON T007_Id=T008_T007_Id
		LEFT JOIN T225 ON T225_Id=T014_T225_Id
		LEFT JOIN T219 ON T225_T219_Id=T219_Id
		LEFT JOIN T004 ON T004_Id=T219_T004_Id
		LEFT JOIN T003 ON T003_Id=T004_T003_Id
		LEFT JOIN D024 ON D024_Id=T013_D024_Id
		LEFT JOIN C007 AS vendedor ON vendedor.C007_Id=D024_C007_Vendedor_Interno
		LEFT JOIN C007 AS VendedorSaida ON VendedorSaida.C007_Id = T007_C007_Id_Vendedor_Interno
		{$where}
		GROUP BY T013_Id
EOT;
	list($geradoSqlT013, $geradoDadosT013) = $g['sqlAuto']->gerarSQLGrid($grid, $from, $extra,0.00,false,false,false,45000,true);

	//--------------------------------- Notas de entrada no T007------------------------------
	$grid = array(
		array('colunaValor' => 'T007ENT.T007_Id', 'colunaTitulo' => 'id_nf_entrada'),
		array('colunaValor' => 'T007ENT.T007_Numero_Nota_Fiscal', 'colunaTitulo' => 'num_nf_entrada'),
		array('colunaValor' => 'T007ENT.T007_D024_Id', 'colunaTitulo' => 'codigo_cliente'),
		array('colunaValor' => 'codigo_usuario.C007_Id', 'colunaTitulo' => 'codigo_usuario'),
		array('colunaValor' => 'T007ENT.T007_C004_Id', 'colunaTitulo' => 'id_empresa'),
		array('colunaValor' => 'if(T007ENT.T007_Data_Emissao="0000-00-00","",T007ENT.T007_Data_Emissao)', 'colunaTitulo' => 'data_emissao_nf_entrada'),
		array('colunaValor' => 'if(T007ENT.T007_Data_Emissao="0000-00-00","",T007ENT.T007_Data_Emissao)', 'colunaTitulo' => 'data_entrada_nf_entrada'),
		array('colunaValor' => 'vendedor.C007_Id', 'colunaTitulo' => 'id_vendedor'),
		array('colunaValor' => "(SELECT GROUP_CONCAT(T132_Numero_Nota_Fiscal SEPARATOR ', ') FROM T132 WHERE T132_T007_Id = T007ENT.T007_Id)", 'colunaTitulo' => 'devolucao_nf'),
	);

	//montando clausula where
	$where = "";
	$where = $API001->montarWhere($where,"T007ENT.T007_Flag_Entrada_Saida","=",'E');
	$where = $API001->montarWhere($where,"T007ENT.T007_Data_Emissao",">=",$dataInicio);
	$where = $API001->montarWhere($where,"T007ENT.T007_Data_Emissao","<=",$dataFinal);
	$where = $API001->montarWhere($where,"T007ENT.T007_Flag_Cancelada","=",'N');
	$where = gInsertExtraWhere($where, "$extraFiltro", true);

	//  DefiniÃ§Ã£o da SQL
	$from = 'T007 as T007ENT';

	$extra = <<<EOT
		LEFT JOIN C007 AS codigo_usuario ON codigo_usuario.C007_Id=T007ENT.T007_C007_Id
		LEFT JOIN D006 ON D006_Id=T007ENT.T007_D006_Id
		LEFT JOIN T008 AS T008ENT ON T008ENT.T008_T007_Id=T007ENT.T007_Id
		LEFT JOIN T008 AS T008VEN ON T008VEN.T008_Id=T008ENT.T008_T008_Id
		LEFT JOIN T007 AS T007VEN ON T007VEN.T007_Id=T008VEN.T008_T007_Id
		LEFT JOIN C007 AS vendedor ON vendedor.C007_Id=T007VEN.T007_C007_Id_Vendedor_Interno
		{$where}
		GROUP BY T007ENT.T007_Id
EOT;
	list($geradoSqlT007, $geradoDadosT007) = $g['sqlAuto']->gerarSQLGrid($grid, $from, $extra,0.00,false,false,false,120000,true);

	$geradoDados = array_merge($geradoDadosT013,$geradoDadosT007);

	//validando se retornaram dados da query
	$semDados = $API001->tratarRetornoVazio($geradoDados);
	if(is_array($semDados)){
		echo json_encode($semDados, JSON_UNESCAPED_UNICODE);
		die();
	}

	echo json_encode($geradoDados, JSON_UNESCAPED_UNICODE);

	die();




