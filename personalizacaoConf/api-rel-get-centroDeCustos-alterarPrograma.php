<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /api-rel-get-centroDeCustos/
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
			array('colunaValor' => 'D073_Id', 'colunaTitulo' => 'id_centro_custo'),
			array('colunaValor' => 'D073_Nome', 'colunaTitulo' => 'nome_centro_custo'),
		);

		//montando clausula where
		$where = $API001->montarWhere($where,"D073_Nome","!=",' ');
		$where = $API001->montarWhere($where,"D073_Flag_Ativo","=",'S');

		//  Definição da SQL
		$from = 'D073';

		$extra = <<<EOT
			{$where}
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
