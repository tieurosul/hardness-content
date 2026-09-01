<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /api-rel-get-cotacoesProdutos/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/


		$retorno = $API003->auth($_GET['API_AUTH'], true);

		if(is_array($retorno)){
			echo json_encode($retorno, JSON_UNESCAPED_UNICODE);
			die();
		}

		$dataInicio = $_GET['DATAINICIAL']; 
		$dataFinal = $_GET['DATAFINAL'];
		$extraFiltro = extraFiltro();		

		$grid = array(
			array('colunaValor' => 'T219_T218_Id', 'colunaTitulo' => 'numero_cotacao'),
			array('colunaValor' => 'T219_Id', 'colunaTitulo' => 'id_item_cotacao'),
			array('colunaValor' => 'D001_Id', 'colunaTitulo' => 'id_produto'),
			array('colunaValor' => 'format(T219_Quantidade,5,"pt_BR")', 'colunaTitulo' => 'quantidade_cotacao'),
			array('colunaValor' => 'format(T219_Valor_Preco_Unitario,5,"pt_BR")', 'colunaTitulo' => 'valor_unitario_cotacao'),
			array('colunaValor' => 'format(T219_Valor_Total_Preco,2,"pt_BR")', 'colunaTitulo' => 'valor_total_cotacao'),
			array('colunaValor' => 'D006_Flag_Venda_Compra_Outros', 'colunaTitulo' => 'tipo_cfop'),
			array('colunaValor' => 'D006_Id', 'colunaTitulo' => 'id_cfop'),
			array('colunaValor' => 'T219_T004_Id', 'colunaTitulo' => 'id_item_orcamento'),
			array('colunaValor' => 'T219_T006_Id', 'colunaTitulo' => 'id_item_pedido'),
			array('colunaValor' => 'T003_C007_Id_Vendedor_Interno', 'colunaTitulo' => 'id_vendedor'),
		);

		//montando clausula where
		$where = $API001->montarWhere($where,"T218_Data_Emissao",">=",$dataInicio);
		$where = $API001->montarWhere($where,"T218_Data_Emissao","<=",$dataFinal);
		$where = $API001->montarWhere($where,"T219_Flag_Cancelado","=","N");
		$where = gInsertExtraWhere($where, "$extraFiltro", true);

		//  Definição da SQL
		$from = 'T219';

		$extra = <<<EOT
			LEFT JOIN T218 ON T218_Id=T219_T218_Id
			LEFT JOIN D009 ON D009_Id=T219_D009_Id
			LEFT JOIN D049 ON D049_Id=D009_D049_Id
			LEFT JOIN D001 ON D001_Id=D049_D001_Id
			LEFT JOIN D006 ON D006_Id=T219_D006_Id
			LEFT JOIN T004 ON T004_Id=T219_T004_Id
			LEFT JOIN T003 ON T003_Id=T004_T003_Id
			{$where}
EOT;
		list($geradoSql, $geradoDados) = $g['sqlAuto']->gerarSQLGrid($grid, $from, $extra,0.00,false,false,false,120000,true);

		//validando se retornaram dados da query
		$semDados = $API001->tratarRetornoVazio($geradoDados);
		if(is_array($semDados)){
			echo json_encode($semDados, JSON_UNESCAPED_UNICODE);
			die();
		}

		echo json_encode($geradoDados, JSON_UNESCAPED_UNICODE);

		die();


