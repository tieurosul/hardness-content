<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /api-rel-get-contas/
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
			array('colunaValor' => 'D014_Id', 'colunaTitulo' => 'id_conta'),
			array('colunaValor' => 'D014_SubConta', 'colunaTitulo' => 'nome_conta'),
		);

		//montando clausula where
		$where = $API001->montarWhere($where,"D014_SubConta","!=",' ');

		//  Definição da SQL
		$from = 'D014';

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
