<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /api-rel-get-vendedoresInternos/
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
			array('colunaValor' => 'C007_Id', 'colunaTitulo' => 'codigo_vendedor_interno'),
			array('colunaValor' => 'C007_Nome', 'colunaTitulo' => 'nome_vendedor_interno'),
			array('colunaValor' => 'C007_Flag_Vendedor', 'colunaTitulo' => 'tipo_cadastro'),
			array('colunaValor' => 'format(C007_Cota_Vendas_Mensal,2,"pt_BR")', 'colunaTitulo' => 'cota_vendas_mensal'),
			array('colunaValor' => 'C007A_Ramal', 'colunaTitulo' => 'ramal_vendedor_interno'),
		);

		//  Definição da SQL
		$from = 'C007';

		$extra = <<<EOT
			LEFT JOIN C007A ON C007A_C007_Id=C007_Id
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
