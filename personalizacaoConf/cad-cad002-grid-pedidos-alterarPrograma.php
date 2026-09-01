<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /cad-cad002-grid-pedidos/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/


/**
		 * Definição do grid tabela
		 */
		$grid = array(
            array('colunaValor' => 'T006_Id', 'colunaTitulo' => 'ID', 'chavePrimaria' => true),
            array('colunaValor' => 'T006_D009_Id', 'colunaTitulo' => 'D009_Id', 'colunaExibir' => false, 'colunaAlinhar' => 'center'),
            array('colunaValor' => 'T005_Data_Emissao', 'colunaTitulo' => 'Data', 'callback' => 'gCorrigeData', 'colunaAlinhar' => 'center'),
            array('colunaValor' => 'T005_Id', 'colunaTitulo' => 'Nº Pedido', 'colunaAlinhar' => 'center', 'callback' => 'abrirJanela', 'callbackParameter' => array('Pedido', '/ven/ven002/content/ven002content02/', 'T005_Id', '1000,615')),
            array('colunaValor' => 'D024_Nome_Fantasia', 'colunaTitulo' => 'Cliente'),
            array('colunaValor' => 'D082_Marca', 'colunaTitulo' => 'Marca'),
            array('colunaValor' => 'T006_Flag_Tipo_Produto', 'colunaTitulo' => 'Tp', 'colunaAlinhar' => 'center', 'colunaExibir' => false),
            array('colunaValor' => 'T006_Quantidade', 'colunaTitulo' => 'Quantidade', 'colunaAlinhar' => 'right'),
            array('colunaValor' => 'T006_Quantidade_Separacao', 'colunaTitulo' => 'Separado', 'colunaAlinhar' => 'right'),
            array('colunaValor' => 'T006_Quantidade_Estoque', 'colunaTitulo' => 'Estoque', 'colunaAlinhar' => 'right'),
            array('colunaValor' => 'T006_Quantidade_OC', 'colunaTitulo' => 'OC', 'colunaAlinhar' => 'right', 'callback' => 'abrirJanela', 'callbackParameter' => array('Ordens de Compra', '/ven/ven001/content/ven001contentProdutoOC/', 'T006_D009_Id', '700,400', true), 'style' => 'width:1%'),
            //array('colunaValor' => 'T006_Flag_Reservar_Estoque', 'colunaTitulo' => 'Reservado', 'colunaAlinhar' => 'center', 'callback' => 'gCheckbox', 'callbackParameter' => array('T006_Flag_Reservar_Estoque', 'N|S')),
            array('colunaValor' => 'T006_Prioridade', 'colunaTitulo' => 'Prioridade', 'style' => 'width: 1%'),
            array('colunaValor' => 'T006_Prioridade', 'colunaTitulo' => 'Prioridade', 'style' => 'width: 1%', 'colunaEditar' => true, 'colunaExibirNivel' => 2),
            array('colunaValor' => 'T006_Valor_Custo_Unitario', 'colunaTitulo' => 'IPV', 'callback' => 'validaIpvPedidos', 'colunaAlinhar' => 'right', 'colunaExibir' => false),
            array('colunaValor' => 'group_concat(distinct T007_Id) as NF', 'colunaTitulo' => 'NF'),
            array('colunaValor' => 'GROUP_CONCAT(DISTINCT(IF(T006_T075_Id>0,"MP","PE"))) as Estoque', 'colunaTitulo' => 'Est', 'style' => 'width:1%;'),
            array('colunaValor' => 'C007_Primeiro_Nome', 'colunaTitulo' => 'Vendedor'),
            array('colunaValor' => 'T005_Status_Pedido(T005_Flag_Status,1) as Status', 'colunaTitulo' => 'Status'),
            array('colunaValor' => 'T005_Flag_Status', 'colunaTitulo' => '', 'colunaExibir' => false),
            array('colunaValor' => 'T006_Valor_Preco_Unitario', 'colunaTitulo' => '', 'colunaExibir' => false),
            array('colunaValor' => 'T006_T075_Id', 'colunaTitulo' => 'DI', 'colunaAlinhar' => 'center'),
            array('colunaValor' => 'T005_D024_Id', 'colunaTitulo' => 'T005_D024_Id', 'colunaAlinhar' => 'center', 'colunaExibir' => false),
            array('colunaValor' => 'T006_T006_Id_Dividir', 'colunaTitulo' => 'Id Div', 'colunaAlinhar' => 'center'),
            array('colunaValor' => 'T005_Valor_Desconto', 'colunaTitulo' => 'R$ Descto', 'colunaAlinhar' => 'right', 'colunaCasasDecimais' => '2', 'style' => 'width:1%', 'colunaExibirNivel' => '2'),
            array('colunaValor' => 'totalDescontoPedidosCliente', 'colunaTitulo' => 'R$ Descto Pendentes', 'callback' => 'totalDescontoPedidosCliente', 'colunaTipo' => 'livre', 'colunaAlinhar' => 'right', 'colunaExibirNivel' => '2'),
        );

		$from = 'T005';

		$extra = <<<EOT
			left join D024 on D024_Id=T005_D024_Id
    		left join T006 on T006_T005_Id=T005_Id
    		left join D009 on D009_Id=T006_D009_Id
    		left join D049 on D049_Id=D009_D049_Id
    		left join D082 on D082_Id=D049_D082_Id
    		left join T008 on T008_T006_Id=T006_Id
   			left join D006 ON D006_Id=T006_D006_Id
       		left join T007 on T007_Id=T008_T007_Id and T007_Flag_Cancelada!='S'
   	     	left join C007 as Vendedor on Vendedor.C007_Id=T005_C007_Id_Vendedor_Interno
        	where T006_D009_Id='{$r_acaoId}'
			AND IFNULL(T006_T075_Id,0) <= 0
			AND (T005_T005_Id_Agrupado<=0 OR T005_T005_Id_Agrupado IS NULL)
			AND IFNULL(T006_C004_Id_Estoque, D009_C004_Id) = D009_C004_Id
			AND ((T006_Flag_Status!='3'
			AND (T005_Flag_Status!='4' OR (T005_Flag_Status='4' AND T005_Flag_Expedicao = '2' AND T007_Id is null))
			AND T005_Flag_Status!='5'
			AND T005_Flag_Status!='7'
			AND T005_Flag_Status!='11'
			AND T005_Flag_Status!='8'
			AND T005_Flag_Status!='26')
			 OR T005_Flag_Status='')
			AND D006_Flag_Estoque='D'    
       group by T006_Id
       ORDER BY IF(T008_Id IS NULL,1,0) ASC, T006_Prioridade DESC
EOT;

/* CLAUSULA ANTERIORMENTE USADA NESTE GRID, ALTEREI PARA USAR A MESMA CLAUSLA DA FUNCTION D009_Quantidade_Pedido (Marcelo-06-05-2019)

    		and (T006_Quantidade>0 or T006_Quantidade_Separacao>0)
    		and (T005_T005_Id_Agrupado<=0 or T005_T005_Id_Agrupado is null)
    		and IF(IFNULL(T006_C004_Id_Estoque,0) > 0, T006_C004_Id_Estoque, D009_C004_Id) = D009_C004_Id
    		and T007_Id is null
*/

		echo  <<<EOT
			<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
				// Modelo: NÂO permitir ORDERNAÇÃO
				$('#{$g['divId']} .grid th').removeAttr('onclick').css({'cursor': 'auto'});
			} </script></div>
EOT;

		/**
		* Filtro
		*/
		$gridFiltro = array(
			'T005_Id' => array('titulo' => 'Pedido', 'tipo' => 'numero'),
			'T005_Data_Emissao' => array('titulo' => 'Emissão', 'tipo' => 'data'),
			'D024_Nome_Fantasia' => array('titulo' => 'Cliente', 'tipo' => 'texto'),		
			'vendedor.C007_Primeiro_Nome' => array('titulo' => 'Vendedor', 'tipo' => 'texto'),	
			'T005_Nome_Status' => array('titulo' => 'Status', 'tipo' => 'texto'),
		);
                 
		// Gera o filtro e acrescenta ao $extra
		$extra = $g['ui']->gridFiltroExtra(true, $gridFiltro, $extra);
		// Imprime o filtro do GRID (normalmente executado acima do GRID)
		echo $g['ui']->gridFiltroPrint($gridFiltro);
		
		function gridLinha($array) {
		    if($array['todosCampos']['T005_Flag_Status']=='5' or $array['todosCampos']['T005_Flag_Status']=='4'){
		        return 'linhaCinza';
		    } else if($array['todosCampos']['T005_Flag_Status']=='8'){
		        return 'linhaVermelho';
		    } else if($array['todosCampos']['T005_Flag_Status']=='26'){
		        return 'linhaLaranja';
		    } else if($array['todosCampos']['T005_Flag_Status']=='14'){
		        return 'linhaVerde';
		    } else if($array['todosCampos']['T005_Flag_Status']<=0 || $array['todosCampos']['T005_Flag_Status']=='9'){
		       return 'linhaAzul';
		    }
		}

		// Botões
		$botoes = array(
		);
		echo Botoes::processar($botoes);

		/**
		 * Geração: Monta o SQL e retorna o dados
		 */	

		list($geradoSql, $geradoDados) = $g['sqlAuto']->gerarSQLGrid($grid, $from, $extra);
		log("geradoDados: ".json_encode($geradoDados));

		// Consulta para ver se a algum produto em requisição interna

		$sql1 = "SELECT 
					'',
					T206_D009_Id,
					T206_Data,
					'',
					'REQUISIÇÃO INTERNA',
					D082_Marca,
					'',
					T206_Quantidade,
					'',
					D009_Quantidade_Estoque,
					'',
					'',
					'',
					'',
					'',
					'PE',
					C007_Primeiro_Nome,
					T206_Flag_Finalizado,
					T206_Flag_Finalizado,
					'',
					'',
					'',
					'',
					''
				FROM T206 
					LEFT JOIN D009 ON D009_Id = T206_D009_Id
					LEFT JOIN D049 ON D049_Id = D009_D049_Id
					LEFT JOIN D082 ON D082_Id = D049_D082_Id
   	     			LEFT JOIN C007 ON C007_Id = T206_C007_Id
					LEFT JOIN T006 ON T006_T206_Id = T206_Id
				WHERE T206_D009_Id = '{$r_acaoId}' 
					AND T206_Flag_Finalizado = 'A'
					AND (T006_Id <= 0 OR T006_Id IS NULL)";

		$T206 = mysql_query($sql1);
		if (mysql_num_rows($T206) > 0) {
			while ($row = mysql_fetch_assoc($T206)) {
				$linha = array_fill(0, 25, ''); // cria 25 vazios
				$linha[1]  = $row['T206_D009_Id'];
				$linha[2]  = $row['T206_Data'];
				$linha[4]  = 'REQUISIÇÃO INTERNA';
				$linha[5]  = $row['D082_Marca'];
				$linha[7]  = $row['T206_Quantidade'];
				$linha[9]  = $row['D009_Quantidade_Estoque'];
				$linha[15] = 'PE';
				$linha[16] = $row['C007_Primeiro_Nome'];
				$linha[17] = "ABERTO";
				$linha[18] = $row['T206_Flag_Finalizado'];
			}
			$geradoDados[] = $linha;
		}
		
		/* $sql2 = "SELECT 
					T006_Id AS `ID`,
					T006_D009_Id AS `D009_Id`,
					T005_Data_Emissao AS `Data`,
					T005_Id AS `Nº Pedido`,
					D024_Nome_Fantasia AS `Cliente`,
					D082_Marca AS `Marca`,
					T006_Flag_Tipo_Produto AS `Tp`,
					T006_Quantidade AS `Quantidade`,
					T006_Quantidade_Separacao AS `Separado`,
					T006_Quantidade_Estoque AS `Estoque`,
					T006_Quantidade_OC AS `OC`,
					T006_Prioridade AS `Prioridade`,
					T006_Prioridade AS `Prioridade`,
					T006_Valor_Custo_Unitario AS `IPV`,
					GROUP_CONCAT(DISTINCT T007_Id) AS `NF`,
					GROUP_CONCAT(DISTINCT(IF(T006_T075_Id>0,'MP','PE'))) AS `Est`,
					C007_Primeiro_Nome AS `Vendedor`,
					T005_Status_Pedido(T005_Flag_Status,1) AS `Status`,
					T005_Flag_Status,
					T006_Valor_Preco_Unitario,
					T006_T075_Id AS `DI`,
					T005_D024_Id AS `T005_D024_Id`,
					T006_T006_Id_Dividir AS `Id Div`,
					T005_Valor_Desconto AS `R$ Descto`,
					0 AS `R$ Descto Pendentes`
				FROM T006
					LEFT JOIN T206 ON T206_Id = T006_T206_Id
					LEFT JOIN T005 ON T005_Id = T006_T005_Id
					LEFT JOIN D024 ON D024_Id = T005_D024_Id
					LEFT JOIN D009 ON D009_Id = T006_D009_Id
					LEFT JOIN D049 ON D049_Id = D009_D049_Id
					LEFT JOIN D082 ON D082_Id = D049_D082_Id
					LEFT JOIN T008 ON T008_T006_Id = T006_Id
					LEFT JOIN T007 ON T007_Id = T008_T007_Id AND T007_Flag_Cancelada != 'S'
					LEFT JOIN C007 ON C007_Id = T005_C007_Id_Vendedor_Interno
				WHERE D009_Id = '{$r_acaoId}'
					AND T206_Flag_Finalizado = 'A'
					AND (T006_Id > 0 OR T006_Id IS NOT NULL)
					AND (T005_T005_Id_Agrupado <= 0 OR T005_T005_Id_Agrupado IS NULL)
					AND ((T005_Flag_Status != '8'
							AND T005_Flag_Status != '5'
							AND T005_Flag_Status != '7'
							AND T005_Flag_Status != '11'
							AND T005_Flag_Status != '8'
							AND T005_Flag_Status != '26'
						)
						OR T005_Flag_Status = ''
					)";

		$T006 = mysql_query($sql2);
		if (mysql_num_rows($T006) > 0) {
			while ($row = mysql_fetch_assoc($T006)) {
				$linha = array();
				foreach ($row as $key => $value) {
					$linha[] = $value;
				}
			}
			$geradoDados[] = $linha;
		} */
		//
		log("geradoDados2: ".json_encode($geradoDados));
		
		/**
		 * Geração: Grid
		 */
		$gridTabela = $g['ui']->grid($grid, $geradoDados);
		$g['smarty']->assign('thead', $gridTabela['thead']);
		$g['smarty']->assign('tbody', $gridTabela['tbody']);
		$g['smarty']->assign('tfoot', $gridTabela['tfoot']);
		$g['smarty']->assign('gridId', md5($g['r_']));
		$g['smarty']->assign('gridPersonalizacao', json_encode(array($g['r_'], $g['divIdRoot'], $g['divId']), JSON_FORCE_OBJECT));
		if($r_acaoId){
			if (isset($r_generateHTML)) { ob_end_clean(); ob_start(); $g['smarty']->display('gridHTML.tpl'); } else { $g['smarty']->display('grid.tpl'); }
		}else{
			echo  '<div align="center"><br /><br /><br />Selecione uma Marca.</div>';		
		}
