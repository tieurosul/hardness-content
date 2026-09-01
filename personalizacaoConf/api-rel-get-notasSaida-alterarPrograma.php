<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /api-rel-get-notasSaida/
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
		array('colunaValor' => 'T007_C004_Id', 'colunaTitulo' => 'id_empresa'),
		array('colunaValor' => 'T007_Id', 'colunaTitulo' => 'id_nf_saida'),
		array('colunaValor' => 'T007_Numero_Nota_Fiscal', 'colunaTitulo' => 'numero_nf_saida'),
		array('colunaValor' => 'codigo_usuario.C007_Id', 'colunaTitulo' => 'codigo_usuario'),
		array('colunaValor' => 'codigo_vendedor_interno.C007_Id', 'colunaTitulo' => 'codigo_vendedor_interno'),
		array('colunaValor' => 'codigo_vendedor_externo.C007_Id', 'colunaTitulo' => 'codigo_vendedor_externo'),
		array('colunaValor' => 'codigo_supervisor.C007_Id', 'colunaTitulo' => 'codigo_supervisor'),
		array('colunaValor' => 'codigo_supervisor2.C007_Id', 'colunaTitulo' => 'codigo_supervisor2'),
		array('colunaValor' => 'codigo_vendedor_substituto.C007_Id', 'colunaTitulo' => 'codigo_vendedor_substituto'),
		array('colunaValor' => 'T007_D024_Id', 'colunaTitulo' => 'codigo_cliente'),
		array('colunaValor' => 'if(T007_Data_Emissao="0000-00-00","",T007_Data_Emissao)', 'colunaTitulo' => 'data_emissao_nf_saida'),
		array('colunaValor' => 'format(T007_Valor_Total,2,"pt_BR")', 'colunaTitulo' => 'valor_total_nf_saida'),
		array('colunaValor' => 'if(T005_Data_Entrega="0000-00-00","",T005_Data_Entrega)', 'colunaTitulo' => 'data_prazo_entrega'),
		array('colunaValor' => 'if(T007_Data_Entrega="0000-00-00","",T007_Data_Entrega)', 'colunaTitulo' => 'data_entrega_nf_saida'),
		array('colunaValor' => 'T007_Nome_Transportadora', 'colunaTitulo' => 'transportadora_nf_saida'),
		array('colunaValor' => "CASE WHEN (IF(T005A_Data_Agendamento = '0000-00-00' OR T005A_Data_Agendamento = '' OR T005A_Data_Agendamento IS NULL, T007A_Data_Prazo_Transporte,T005A_Data_Agendamento) = '0000-00-00') OR (T007_Data_Entrega = '0000-00-00') OR (IF(T005A_Data_Agendamento = '0000-00-00' OR T005A_Data_Agendamento = '' OR T005A_Data_Agendamento IS NULL, T007A_Data_Prazo_Transporte,T005A_Data_Agendamento) IS NULL) OR (T007_Data_Entrega IS NULL) THEN '-' WHEN T007_Data_Entrega <= IF(T005A_Data_Agendamento = '0000-00-00' OR T005A_Data_Agendamento = '' OR T005A_Data_Agendamento IS NULL, T007A_Data_Prazo_Transporte,T005A_Data_Agendamento) THEN 'DENTRO DO PRAZO' ELSE 'FORA DO PRAZO' END", 'colunaTitulo' => 'status_entrega_nf_saida'),
		array('colunaValor' => "CASE WHEN T007A_Data_Prazo_Transporte = '0000-00-00' OR T007_Data_Entrega = '0000-00-00' OR T007A_Data_Prazo_Transporte IS NULL OR T007_Data_Entrega IS NULL THEN '-' WHEN T007_Data_Entrega <= T007A_Data_Prazo_Transporte THEN 'DENTRO DO PRAZO' ELSE 'FORA DO PRAZO' END", 'colunaTitulo' => 'antigo_status_entrega_nf_saida'),
		array('colunaValor' => "CASE WHEN T007_Flag_Frete = '0' THEN '0 - Contratação por conta do Remetente' WHEN T007_Flag_Frete = '1' THEN '1 - Contratação por conta do Destinatário' WHEN T007_Flag_Frete = '2' THEN '2 - Contratação por conta de Terceiros' WHEN T007_Flag_Frete = '3' THEN '3 - Próprio por conta do Remetente' WHEN T007_Flag_Frete = '4' THEN '4 - Próprio por conta do Destinatário' WHEN T007_Flag_Frete = '9' THEN '9 - Sem frete' ELSE '-' END", 'colunaTitulo' => 'frete_nf_saida'),
		array('colunaValor' => 'format(T007_Peso_Bruto,3,"pt_BR")', 'colunaTitulo' => 'peso_bruto_kg_nf_saida'),
		array('colunaValor' => 'D024_CNPJ_Matriz', 'colunaTitulo' => 'cnpj_matriz'),
		array('colunaValor' => 'cidade.D020_Nome_Cidade', 'colunaTitulo' => 'cidade'),
		array('colunaValor' => 'T007_Cliente_Endereco', 'colunaTitulo' => 'endereco'),
		array('colunaValor' => 'T007_Cliente_Endereco_Numero', 'colunaTitulo' => 'numero_endereco'),
		array('colunaValor' => 'T007_Cliente_Bairro', 'colunaTitulo' => 'bairro'),
		array('colunaValor' => 'T007_Cliente_CEP', 'colunaTitulo' => 'cep'),
	);

	//montando clausula where
	$where = $API001->montarWhere($where,"T007_Data_Emissao",">=",$dataInicio);
	$where = $API001->montarWhere($where,"T007_Data_Emissao","<=",$dataFinal);
	$where = $API001->montarWhere($where,"T007_Flag_Entrada_Saida","=",'S');
	$where = $API001->montarWhere($where,"T007_Flag_Cancelada","=",'N');
	$where = gInsertExtraWhere($where, "$extraFiltro", true);

	//Definição da SQL
	$from = 'T007';


	$extra = <<<EOT
		LEFT JOIN C007 AS codigo_usuario ON codigo_usuario.C007_Id=T007_C007_Id
		LEFT JOIN C007 AS codigo_vendedor_interno ON codigo_vendedor_interno.C007_Id=T007_C007_Id_Vendedor_Interno
		LEFT JOIN C007 AS codigo_supervisor ON codigo_supervisor.C007_Id=codigo_vendedor_interno.C007_C007_Id
		LEFT JOIN C007 AS codigo_supervisor2 ON codigo_supervisor2.C007_Id=codigo_vendedor_interno.C007_C007_Id_2
		LEFT JOIN C007 AS codigo_vendedor_externo ON codigo_vendedor_externo.C007_Id=T007_C007_Id_Vendedor_Externo
		LEFT JOIN C007 AS codigo_vendedor_substituto ON codigo_vendedor_substituto.C007_Id=codigo_vendedor_interno.C007_Id_Vendedor_Substituto
		LEFT JOIN T005 ON T005_Id = T007_T005_Id
		LEFT JOIN T005A ON T005_Id = T005A_T005_Id
		LEFT JOIN T007A ON T007A_T007_Id = T007_Id
		LEFT JOIN D024 ON D024_Id=T007_D024_Id
		LEFT JOIN D020 AS cidade ON cidade.D020_Id=D024_D020_Id
		{$where}
EOT;

	log("SQL: ".json_encode($grid).$from.$extra);

	list($geradoSql, $geradoDados) = $g['sqlAuto']->gerarSQLGrid($grid, $from, $extra,0.00,false,false,false,120000,true);

	//validando se retornaram dados da query
	$semDados = $API001->tratarRetornoVazio($geradoDados);
	if(is_array($semDados)){
		echo json_encode($semDados, JSON_UNESCAPED_UNICODE);
		die();
	}

	echo json_encode($geradoDados, JSON_UNESCAPED_UNICODE);

	die();











