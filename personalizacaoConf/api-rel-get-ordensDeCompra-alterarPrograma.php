<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /api-rel-get-ordensDeCompra/
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
			array('colunaValor' => 'T224_Id', 'colunaTitulo' => 'numero_oc'),
			array('colunaValor' => 'T224_D024_Id', 'colunaTitulo' => 'codigo_cliente'),
			array('colunaValor' => 'T224_C004_Id', 'colunaTitulo' => 'id_empresa'),
			array('colunaValor' => 'codigo_comprador_interno.C007_Id', 'colunaTitulo' => 'codigo_comprador_interno'),
			array('colunaValor' => 'codigo_comprador_externo.C007_Id', 'colunaTitulo' => 'codigo_comprador_externo'),
            array('colunaValor' => 'codigo_supervisor.C007_Id', 'colunaTitulo' => 'codigo_supervisor'),
            array('colunaValor' => 'codigo_supervisor2.C007_Id', 'colunaTitulo' => 'codigo_supervisor2'),
			array('colunaValor' => 'if(T224_Data_Emissao="0000-00-00","",T224_Data_Emissao)', 'colunaTitulo' => 'data_emissao_oc'),
			array('colunaValor' => 'T003_C007_Id_Vendedor_Interno', 'colunaTitulo' => 'id_vendedor'),
			array('colunaValor' => 'group_concat(distinct T013_Numero_Nota_Fiscal SEPARATOR "|")', 'colunaTitulo' => 'nf_recebida'),
		);

		//montando clausula where
		$where = $API001->montarWhere($where,"T224_Data_Emissao",">=",$dataInicio);
		$where = $API001->montarWhere($where,"T224_Data_Emissao","<=",$dataFinal);
		$where = gInsertExtraWhere($where, "$extraFiltro", true);

		//  Definição da SQL
		$from = 'T224';

		$extra = <<<EOT
			LEFT JOIN C007 AS codigo_comprador_interno ON codigo_comprador_interno.C007_Id=T224_C007_Id_Vendedor_Interno
			LEFT JOIN C007 AS codigo_comprador_externo ON codigo_comprador_externo.C007_Id=T224_C007_Id_Vendedor_Externo
			LEFT JOIN C007 AS codigo_supervisor ON codigo_supervisor.C007_Id=codigo_comprador_interno.C007_C007_Id
			LEFT JOIN C007 AS codigo_supervisor2 ON codigo_supervisor2.C007_Id=codigo_comprador_interno.C007_C007_Id_2
			LEFT JOIN T225 ON T225_T224_Id=T224_Id
			LEFT JOIN T219 ON T225_T219_Id=T219_Id
			LEFT JOIN T004 ON T004_Id=T219_T004_Id
			LEFT JOIN T003 ON T003_Id=T004_T003_Id
			LEFT JOIN T014 FORCE INDEX FOR JOIN (IDX_T014_T225_Id) ON T014_T225_Id=T225_Id AND T014_Flag_Cancelada != 'S'
			LEFT JOIN T013 ON T013_Id=T014_T013_Id and T013_Flag_Cancelada != 'S'
			{$where}
			GROUP BY T224_Id
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


