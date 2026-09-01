<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /api-rel-get-clientes/
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
			array('colunaValor' => 'D024_Id', 'colunaTitulo' => 'codigo_cliente'),
			array('colunaValor' => 'D024_Id', 'colunaTitulo' => 'codigo_Fornecedor'),
			array('colunaValor' => 'D024_Nome_Empresa', 'colunaTitulo' => 'nome_cliente'),
			array('colunaValor' => 'D024_Flag_Pessoa_Fisica_Juridica', 'colunaTitulo' => 'tipo_pessoa_cliente'),
			array('colunaValor' => 'D024_Flag_Contribuinte', 'colunaTitulo' => 'tipo_contribuinte_cliente'),
			array('colunaValor' => 'D030_Nome_Pais', 'colunaTitulo' => 'pais_cliente'),
			array('colunaValor' => 'D018_UF', 'colunaTitulo' => 'UF_cliente'),
			array('colunaValor' => 'D018_Estado', 'colunaTitulo' => 'estado_cliente'),
			array('colunaValor' => 'D020_Nome_Cidade', 'colunaTitulo' => 'cidade_cliente'),
			array('colunaValor' => 'D024_Bairro', 'colunaTitulo' => 'bairro_cliente'),
			array('colunaValor' => 'D024_Cep', 'colunaTitulo' => 'cep_cliente', 'callback' => 'tratarCEPCliente'),
			array('colunaValor' => 'D024_Numero', 'colunaTitulo' => 'endereco_numero_cliente'),			
			array('colunaValor' => 'D024_C007_Vendedor_Interno', 'colunaTitulo' => 'codigo_vendedor_interno'),			
			array('colunaValor' => 'D024_Flag_Fornecedor', 'colunaTitulo' => 'flag_fornecedor'),
			array('colunaValor' => 'D024_Flag_Cliente', 'colunaTitulo' => 'flag_cliente'),
			array('colunaValor' => 'D024_Flag_Ativo', 'colunaTitulo' => 'cadastro_ativo'),
			array('colunaValor' => 'REPLACE(REPLACE(REPLACE(D024_CNPJ,".",""),"/",""),"-","")', 'colunaTitulo' => 'numero_cnpj'),
        	array('colunaValor' => 'D046_Nome', 'colunaTitulo' => 'grupo_cliente'),
        	array('colunaValor' => 'if(D085_Data="0000-00-00","",D085_Data)', 'colunaTitulo' => 'ultimo_contato_cliente'),
        	array('colunaValor' => 'D085_Mensagem', 'colunaTitulo' => 'ultima_acao_cliente'),
        	array('colunaValor' => 'D024_Flag_Curva_ABC', 'colunaTitulo' => 'abc_v_cliente'),
        	array('colunaValor' => 'D024_CNPJ_Matriz', 'colunaTitulo' => 'cnpj_matriz'),
			array('colunaValor' => '(SELECT D024_Nome_Empresa FROM D024 as M WHERE D024.D024_Cnpj_Matriz != "" AND M.D024_CNPJ != "" AND M.D024_CNPJ = D024.D024_Cnpj_Matriz LIMIT 1)', 'colunaTitulo' => 'nome_matriz'),
        	array('colunaValor' => 'if(D024_Data_Cadastro="0000-00-00","",D024_Data_Cadastro)', 'colunaTitulo' => 'data_cadastro'),
			array('colunaValor' => 'D024_E_Mail', 'colunaTitulo' => 'Email_cliente'),
		);

		//montando clausula where
		$where = $API001->montarWhere($where,"D024_Nome_Empresa","!=",' ');
		$where = $API001->montarWhere($where,"IFNULL(D024_D020_Id,0)",">","0");
		$where = $API001->montarWhere($where,"IFNULL(D024_D018_Id,0)",">","0");

		//  Definição da SQL
		$from = 'D024';

		$extra = <<<EOT
			LEFT JOIN D018 ON D018_Id = D024_D018_Id
			LEFT JOIN D020 ON D020_Id = D024_D020_Id
			LEFT JOIN D030 ON D030_Id = D024_D030_Id
            LEFT JOIN D046 ON D046_Id = D024_D046_Id
			LEFT JOIN D085 ON D085_Id = D024_D085_Id
			{$where}
EOT;

	log("$grid\n$from\n$extra");

		list($geradoSql, $geradoDados) = $g['sqlAuto']->gerarSQLGrid($grid, $from, $extra,0.00,false,false,false,45000,true);

		//validando se retornaram dados da query
		$semDados = $API001->tratarRetornoVazio($geradoDados);
		if(is_array($semDados)){
			echo json_encode($semDados, JSON_UNESCAPED_UNICODE);
			die();
		}

		echo json_encode($geradoDados, JSON_UNESCAPED_UNICODE);

		die();











