<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /api-rel-get-tiposCFOP/
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
			array('colunaValor' => 'D006_Flag_Venda_Compra_Outros', 'colunaTitulo' => 'tipo_cfop'),
			array('colunaValor' => 'D006_Descricao', 'colunaTitulo' => 'descricao_tipo_cfop'),
		);

		//montando clausula where
		$where = $API001->montarWhere($where,"IFNULL(D006_Flag_Venda_Compra_Outros,'')","!="," ");

		//  Definição da SQL
		$from = 'D006';

		$extra = <<<EOT
			{$where}
			GROUP BY D006_Flag_Venda_Compra_Outros
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
