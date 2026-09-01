<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /rel-fin-grid-fluxoCaixa/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

        $filtroObrigatorio = true;
		$g['cacheCount'] = true;
		/**
		* Definição da SQL
		*/
		$from = 'T002';
		$extra = <<<EOT
EOT;
		/**
         * Botões Almir
         */
        $botoes = array(
		);
		echo Botoes::processar($botoes);

		/**
		* Filtro
		*/
		$gridFiltro = array(
			'T002_Data_Vencimento' => array('titulo' => 'Data', 'tipo' => 'data'),
            'T002_C004_Id' => array('titulo' => 'Empresa', 'tipo' => 'select', 'select' =>
                array(
                    array('title' => 'Todas', 'value' => ''),
                    array('title' => 'Atual', 'value' => $g['empresaAtual']),
                )
            ),
            'Saldo_Bancario' => array('titulo' => 'Incluir saldo bancário', 'tipo' => 'select', 'select' =>
                array(
                    array('title' => 'SIM', 'value' => 'S', 'selected' => true),
                    array('title' => 'NÃO', 'value' => 'N'),
                ), 'naoGerarExtra' => true
            ),
            'Titulos_Cobranca' => array('titulo' => 'Incluir títulos cobrança', 'tipo' => 'select', 'select' =>
                array(
                    array('title' => 'Todas', 'value' => ''),
                    array('title' => 'SIM', 'value' => 'S'),
                    array('title' => 'NÃO', 'value' => 'N'),
                ), 'naoGerarExtra' => true
            ),
		);
		// Gera o filtro e acrescenta ao $extra
		$extra = $g['ui']->gridFiltroExtra(true, $gridFiltro, $extra);
		// Imprime o filtro do GRID (normalmente executado acima do GRID)
		echo $g['ui']->gridFiltroPrint($gridFiltro);

		/**
         * Totais Almir
         */
        $totais = array(
            // sql
            array('nome' => 'Count', 'titulo' => 'Registros', 'totalizador' => 'count', 'callbackParameter' => 0),
        );


		/**
		* Definição do header
		*/
		$grid = array(
			array('colunaValor' => 'T002_Id as Campo', 'colunaTitulo' => 'Vencimento', 'callback' => 'gCorrigeData', 'colunaOrdenar' => false, 'colunaAlinhar' => 'center'),
			array('colunaValor' => '0 as Campo2', 'colunaTitulo' => 'Dia', 'colunaOrdenar' => false, 'colunaAlinhar' => 'center'),
			array('colunaValor' => '0 as Campo3', 'colunaTitulo' => 'A Receber (+)', 'colunaCasasDecimais' => '2', 'colunaOrdenar' => false, 'colunaAlinhar' => 'right'),
			array('colunaValor' => '0 as Campo4', 'colunaTitulo' => 'Projeção (+)', 'colunaCasasDecimais' => '2', 'colunaOrdenar' => false, 'colunaAlinhar' => 'right'),
			array('colunaValor' => '0 as Campo5', 'colunaTitulo' => 'A Pagar (-)', 'colunaOrdenar' => false, 'colunaAlinhar' => 'right'),
			array('colunaValor' => '0 as Campo6', 'colunaTitulo' => 'Projeção (-)', 'colunaCasasDecimais' => '2', 'colunaOrdenar' => false, 'colunaAlinhar' => 'right'),
			array('colunaValor' => '0 as Campo7', 'colunaTitulo' => 'Saldo Proj', 'colunaCasasDecimais' => '2', 'colunaOrdenar' => false, 'colunaAlinhar' => 'right'),
			array('colunaValor' => '0 as Campo8', 'colunaTitulo' => 'Saldo Real', 'colunaCasasDecimais' => '2', 'colunaOrdenar' => false, 'colunaAlinhar' => 'right'),
			array('colunaValor' => '0 as Campo9', 'colunaTitulo' => 'Recebido (+)', 'colunaCasasDecimais' => '2', 'colunaOrdenar' => false, 'colunaAlinhar' => 'right'),
			array('colunaValor' => '0 as Campo10', 'colunaTitulo' => 'Pago (-)', 'colunaCasasDecimais' => '2', 'colunaOrdenar' => false, 'colunaAlinhar' => 'right'),
			array('colunaValor' => '0 as Campo11', 'colunaTitulo' => 'Saldo', 'colunaCasasDecimais' => '2', 'colunaOrdenar' => false, 'colunaAlinhar' => 'right'),
		);
		$gridOriginal = $grid;
		list($geradoSql, $geradoDados) = $g['sqlAuto']->gerarSQLGrid($grid, $from, $extra);

		/**
		 *	Monta o array do grid do relatório
		 */
		$geradoDados = array();

		//Imprime o saldo das contas caso selecionado no filtro
		$Saldo_Bancario = $g['ui']->getGridFiltroCampoBusca('Saldo_Bancario');
		$Titulos_Cobranca = $g['ui']->getGridFiltroCampoBusca('Titulos_Cobranca');
		$extraC004 = $g['ui']->getGridFiltroCampoExtra('T002_C004_Id');

		if($Saldo_Bancario == 'S'){
			$extraD007 = str_replace("T002_C004_Id", "D007_C004_Id", $extraC004);
			$sqlD007 = mysql_query("SELECT * FROM D007 WHERE D007_Flag_Ativo = 'S' {$extraD007}");
			while ($mD007 = mysql_fetch_array($sqlD007)) {
			 	if ($mD007['D007_Flag_Fluxo_Caixa'] == 'S') {
			    	$geradoDados[] = array('', '', '', '', 'Saldo ' . $mD007['D007_Nome_Banco'], '', $mD007['D007_Valor_Saldo'], '', '', '', '');
			    	$Saldo_Real += $mD007['D007_Valor_Saldo'];
			    	$Saldo_Projetado+= $mD007['D007_Valor_Saldo'];
			  	}
			}
		}
		//$extra = gRetirarGroupBy(gRetirarOrderBy($extra));
		//Extra T002
		$extraT002 = $extra;
		$extraT002 = gInsertExtraWhere($extraT002, "T002_Data_Recebimento='0000-00-00' AND (T002_T002_Id_Agrupado<=0 OR T002_T002_Id_Agrupado IS NULL) AND T002_Flag_Status!='2'");
		$extraT002 = ($Titulos_Cobranca == 'N') ? gInsertExtraWhere($extraT002, "T002_Flag_Status!='3'") : $extraT002;
		//Extra T005
		$extraT005 = str_replace("T002_C004_Id", "T005_C004_Id", $extra);
		$extraT005 = str_replace("T002_Data_Vencimento", "T005_Data_Emissao", $extraT005);
		$extraT005 = gInsertExtraWhere($extraT005, "T005_Flag_Status = '7'");
		//Extra T015
		$extraT015 = str_replace("T002_C004_Id", "T015_C004_Id", $extra);
		$extraT015 = str_replace("T002_Data_Vencimento", "T015_Data_Vencimento", $extraT015);
		$extraT015 = gInsertExtraWhere($extraT015, "T015_Data_Pagamento='0000-00-00' AND (T015_T015_Id_Agrupado <= 0 OR T015_T015_Id_Agrupado IS NULL)");
		//Extra T019
		$extraT019 = str_replace("T002_C004_Id", "T019_C004_Id", $extra);
		$extraT019 = str_replace("T002_Data_Vencimento", "T019_Data_Lancamento", $extraT019);
		//Extra T019 - 1
		$extraT019_1 = gInsertExtraWhere($extraT019, "T019_Valor_Lancamento > 0");
        //Extra T019 - 2
		$extraT019_2 = gInsertExtraWhere($extraT019, "T019_Valor_Lancamento < 0 AND D014_SubConta != 'TRANSFERENCIA ENTRE CONTAS'");
        log("extraT019_2: ". $extraT019_2);
		//Extra T018
        $extraT018 = str_replace("T002_C004_Id", "T018_C004_Id", $extra);
        $extraT018 = str_replace("T002_Data_Vencimento", "CONCAT(T018_Ano,'-',T018_Mes,'-',D014_Dia_Vencimento,' 00:00:00')", $extraT018);
        //Extra T018 - 1
		$extraT018_1 = gInsertExtraWhere($extraT018, "D014_Flag_Tipo='RC' AND T018_Valor_Saldo > 0");
        //Extra T018 - 2
		$extraT018_2 = gInsertExtraWhere($extraT018, "D014_Flag_Tipo='PG' AND ((D014_Flag_Pagamento_Unico!='S' AND T018_Valor_Saldo > 0) OR (D014_Flag_Pagamento_Unico='S' AND (T018_Valor_Pagar+T018_Valor_Pago)<=0 AND T018_Valor_Saldo > 0))");
        //Extra T018 - 3
		$extraT018_3 = gInsertExtraWhere($extraT018, "concat(T018_Ano,'-',T018_Mes,'-',D014_Dia_Vencimento)>=curdate() AND D014_Flag_Tipo='PG' AND T018_Valor_Saldo > 0");

		$sqlGrid = "SELECT T002_Data_Emissao,
                           T002_Data_Vencimento,
                           SUM(T002_Valor_Receber) T002_Valor_Receber,
                           SUM(T002_Valor_Recebido) T002_Valor_Recebido,
                           SUM(T005_Valor_Romaneio) T005_Valor_Romaneio,
                           SUM(T002_Valor_Pagar) T002_Valor_Pagar,
                           SUM(T002_Valor_Pago) T002_Valor_Pago,
                           SUM(Projecao_RC) Projecao_RC,
                           SUM(Projecao_PG) Projecao_PG,
                           SUM(Projecao_PG_Projetado) Projecao_PG_Projetado
                      FROM (

                      	(SELECT T002_Data_Emissao,
                                IF(T117_Data_Vencimento IS NOT NULL,T117_Data_Vencimento,T002_Data_Vencimento) T002_Data_Vencimento,
                                SUM(T002_Valor_Total) T002_Valor_Receber,
                                0 T002_Valor_Recebido,
                                0 T005_Valor_Romaneio,
                                0 T002_Valor_Pagar,
                                0 T002_Valor_Pago,
                                0 Projecao_RC,
                                0 Projecao_PG,
                                0 Projecao_PG_Projetado
                            FROM T002
                       LEFT JOIN T117 on T117_Id=T002_T117_Id
                           	{$extraT002}
                        GROUP BY IF(T117_Data_Vencimento IS NOT NULL,T117_Data_Vencimento,T002_Data_Vencimento)
						ORDER BY IF(T117_Data_Vencimento IS NOT NULL,T117_Data_Vencimento,T002_Data_Vencimento)) 

                      UNION ALL

                         (SELECT T019_Data_Lancamento,
                                 T019_Data_Lancamento,
                                 0 T002_Valor_Receber,
                                 SUM(T019_Valor_Lancamento) T002_Valor_Recebido,
                                 0 T005_Valor_Romaneio,
                                 0 T002_Valor_Pagar,
                                 0 T002_Valor_Pago,
                                 0 Projecao_RC,
                                 0 Projecao_PG,
                                 0 Projecao_PG_Projetado
                            FROM T019
                            {$extraT019_1}
                        GROUP BY T019_Data_Lancamento
						ORDER BY T019_Data_Lancamento)

                      UNION ALL

                         (SELECT T005_Data_Emissao,
                                 T005_Data_Emissao,
                                 0 T002_Valor_Receber,
                                 0 T002_Valor_Recebido,
                                 SUM(T005_Valor_Total-ifnull((SELECT SUM(T019_Valor_Lancamento) FROM T019 WHERE T019_T005_Id=T005_Id),0)) T005_Valor_Romaneio,
                                 0 T002_Valor_Pagar,
                                 0 T002_Valor_Pago,
                                 0 Projecao_RC,
                                 0 Projecao_PG,
                                 0 Projecao_PG_Projetado
                            FROM T005
                           	{$extraT005}
                        GROUP BY T005_Data_Emissao
						ORDER BY T005_Data_Emissao)

                      UNION ALL

                         (SELECT T015_Data_Emissao,
                                 T015_Data_Vencimento,
                                 0 T002_Valor_Receber,
                                 0 T002_Valor_Recebido,
                                 0 T005_Valor_Romaneio,
                                 SUM(T015_Valor_Total) T002_Valor_Pagar,
                                 0 T002_Valor_Pago,
                                 0 Projecao_RC,
                                 0 Projecao_PG,
                                 0 Projecao_PG_Projetado
                            FROM T015
                            {$extraT015}
                        GROUP BY T015_Data_Vencimento
						ORDER BY T015_Data_Vencimento)

                      UNION ALL

                         (SELECT T019_Data_Lancamento,
                                 T019_Data_Lancamento,
                                 0 T002_Valor_Receber,
                                 0 T002_Valor_Recebido,
                                 0 T005_Valor_Romaneio,
                                 0 T002_Valor_Pagar,
                                 SUM(T019_Valor_Lancamento) T002_Valor_Pago,
                                 0 Projecao_RC,
                                 0 Projecao_PG,
                                 0 Projecao_PG_Projetado
                            FROM T019
                            LEFT JOIN D014 ON D014_Id = T019_D014_Id
                            {$extraT019_2}
                        GROUP BY T019_Data_Lancamento
						ORDER BY T019_Data_Lancamento)

                      UNION ALL

                         (SELECT concat(T018_Ano,'-',T018_Mes,'-',D014_Dia_Vencimento) T018_Data_Vencimento,
                                 concat(T018_Ano,'-',T018_Mes,'-',D014_Dia_Vencimento) T018_Data_Vencimento,
                                 0 T002_Valor_Receber,
                                 0 T002_Valor_Recebido,
                                 0 T005_Valor_Romaneio,
                                 0 T002_Valor_Pagar,
                                 0 T002_Valor_Pago,
                                 SUM(T018_Valor_Saldo) Projecao_RC,
                                 0 Projecao_PG,
                                 0 Projecao_PG_Projetado
                            FROM T018
                       LEFT JOIN D014 on D014_Id=T018_D014_Id
                            {$extraT018_1}
                        GROUP BY concat(T018_Ano,'-',T018_Mes,'-',D014_Dia_Vencimento)
						ORDER BY concat(T018_Ano,'-',T018_Mes,'-',D014_Dia_Vencimento))

                      UNION ALL

                         (SELECT concat(T018_Ano,'-',T018_Mes,'-',D014_Dia_Vencimento) T018_Data_Vencimento,
                                 concat(T018_Ano,'-',T018_Mes,'-',D014_Dia_Vencimento) T018_Data_Vencimento,
                                 0 T002_Valor_Receber,
                                 0 T002_Valor_Recebido,
                                 0 T005_Valor_Romaneio,
                                 0 T002_Valor_Pagar,
                                 0 T002_Valor_Pago,
                                 0 Projecao_RC,
                                 0 Projecao_PG,
                                 SUM(T018_Valor_Saldo) Projecao_PG_Projetado
                            FROM T018
                       LEFT JOIN D014 on D014_Id=T018_D014_Id
                            {$extraT018_2}
                        GROUP BY concat(T018_Ano,'-',T018_Mes,'-',D014_Dia_Vencimento)
						ORDER BY concat(T018_Ano,'-',T018_Mes,'-',D014_Dia_Vencimento))

                      UNION ALL

                         (SELECT concat(T018_Ano,'-',T018_Mes,'-',D014_Dia_Vencimento) T018_Data_Vencimento,
                                 concat(T018_Ano,'-',T018_Mes,'-',D014_Dia_Vencimento) T018_Data_Vencimento,
                                 0 T002_Valor_Receber,
                                 0 T002_Valor_Recebido,
                                 0 T005_Valor_Romaneio,
                                 0 T002_Valor_Pagar,
                                 0 T002_Valor_Pago,
                                 0 Projecao_RC,
                                 SUM(T018_Valor_Saldo) Projecao_PG,
                                 0 Projecao_PG_Projetado
                            FROM T018
                       LEFT JOIN D014 on D014_Id=T018_D014_Id
                            {$extraT018_3}
                        GROUP BY concat(T018_Ano,'-',T018_Mes,'-',D014_Dia_Vencimento)
						ORDER BY concat(T018_Ano,'-',T018_Mes,'-',D014_Dia_Vencimento))

                    ) B GROUP BY T002_Data_Vencimento ORDER BY T002_Data_Vencimento";
        log($sqlGrid);
		$sqlGrid = mysql_query($sqlGrid);

		while($row = mysql_fetch_array($sqlGrid)) {
	    	if($row['T002_Data_Vencimento'] >= date("Y-m-d")) {
            	$Saldo_Real  += ($row['T002_Valor_Receber']+$row['T005_Valor_Romaneio']+$row['Projecao_RC'])-($row['T002_Valor_Pagar']+$row['Projecao_PG']);
          	}
          	$Saldo_Projetado += ($row['T002_Valor_Receber']+$row['T005_Valor_Romaneio']+$row['Projecao_RC'])-($row['T002_Valor_Pagar']+$row['Projecao_PG_Projetado']);
          	$Saldo_Realizado += $row['T002_Valor_Recebido']+$row['T002_Valor_Pago'];

          	$geradoDados[] = array(
          		$row['T002_Data_Vencimento'],
          		substr(diaSemana(date("w", strtotime($row['T002_Data_Vencimento']))+1),0,3),
          		$row['T002_Valor_Receber']+$row['T005_Valor_Romaneio'],
          		$row['Projecao_RC'],
				gCorrigeNumero($row['T002_Valor_Pagar']),
				$row['Projecao_PG_Projetado'],
				$Saldo_Projetado,
				$Saldo_Real,
				$row['T002_Valor_Recebido'],
				$row['T002_Valor_Pago'],
				$Saldo_Realizado,
			);
            // log("Dado loop: T002_Data_Vencimento - {$row['T002_Data_Vencimento']}; T002_Valor_Pago - {$row['T002_Valor_Pago']};");
            //   - {$row['']};

        	$mTotal[0]+=$row['T002_Valor_Receber']+$row['T005_Valor_Romaneio'];
        	$mTotal[1]+=$row['T002_Valor_Pagar'];
        	$mTotal[2]+=$row['Projecao_RC'];
        	$mTotal[3]+=$row['Projecao_PG'];
        	$mTotal[4]+=$row['T002_Valor_Recebido'];
        	$mTotal[5]+=$row['T002_Valor_Pago'];
        }
        $geradoDados[] = array(
      		'',
      		'Total',
      		$mTotal[0],
      		$mTotal[2],
			gCorrigeNumero($mTotal[1]),
			$mTotal[3],
			'',
			'',
			$mTotal[4],
			$mTotal[5],
			'',
		);
        log("dados: ".json_encode($geradoDados));

		//Primeira linha grid
		$primeiraLinhaGridHead = "<tr><th colspan=\"4\" class=\"ui-widget-header ui-corner-all\" style=\"text-align: center; z-index: 2;\"></th>";
		$primeiraLinhaGridHead.= "<th colspan=\"6\" class=\"ui-widget-header ui-corner-all\" style=\"text-align: center; z-index: 2;\">A Realizar</th>";
		$primeiraLinhaGridHead.= "<th colspan=\"3\" class=\"ui-widget-header ui-corner-all\" style=\"text-align: center; z-index: 2;\">Realizado</th></tr>";

		/**
		 * Geração: Grid
		 */
		$geradoDados = $g['ui']->processaArrayDados($gridOriginal, $geradoDados);
		$gridTabela = $g['ui']->grid($grid, $geradoDados);
		$g['smarty']->assign('thead', $gridTabela['thead']);
		$g['smarty']->assign('tbody', $gridTabela['tbody']);
		$g['smarty']->assign('tfoot', $gridTabela['tfoot']);
		$g['smarty']->assign('gridId', md5($g['r_']));
		$g['smarty']->assign('gridPersonalizacao', json_encode(array($g['r_'], $g['divIdRoot'], $g['divId']), JSON_FORCE_OBJECT));
		$g['smarty']->assign('primeiraLinhaGridHead', $primeiraLinhaGridHead);
		$g['smarty']->assign('removerScrollLoading', true);
		gBotaoAuditoria($from);
		if (isset($r_generateHTML)) { ob_end_clean(); ob_start(); $g['smarty']->display('gridHTML.tpl'); } else { $g['smarty']->display('grid.tpl'); }

