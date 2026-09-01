<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /est-est017-grid-est017GridMateriais/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

/* 
		$paramlocacaoPadraoProducao = $g['C031']['locacaoPadraoSimulacao'];

		$sqlSaldoLocacao  = " IFNULL((SELECT SUM(T066_Simulacao.T066_Quantidade_Estoque) ";
		$sqlSaldoLocacao .=           " FROM T066 AS T066_Simulacao ";
		$sqlSaldoLocacao .=           " LEFT JOIN D009 ON T066_Simulacao.T066_D009_Id = D009_Id ";
		$sqlSaldoLocacao .=           " LEFT JOIN D001 ON D009_D001_Id = D001_Id ";
		$sqlSaldoLocacao .=           " LEFT JOIN D001A ON D001A_D001_Id = D001_Id ";
		$sqlSaldoLocacao .=          " WHERE T066_Simulacao.T066_D004_Id = '{$paramlocacaoPadraoProducao}' ";
		$sqlSaldoLocacao .=            " AND T066_Simulacao.T066_D009_Id = T264.T264_D009_Id), 0) ";

		$sqlSaldoOP       = " IFNULL((SELECT SUM(T264_Simulacao.T264_Quantidade) ";
		$sqlSaldoOP      .=           " FROM T264 AS T264_Simulacao ";
		$sqlSaldoOP      .=                " LEFT JOIN T255  AS T255_SALDO  ON (T264_Simulacao.T264_T255_Id = T255_SALDO.T255_Id) ";
		$sqlSaldoOP      .=                " LEFT JOIN T255a AS T255a_SALDO ON (T255a_SALDO.T255A_T255_Id   = T255_SALDO.T255_Id) ";
		$sqlSaldoOP      .=                " LEFT JOIN T255                 ON (T255.T255_Id                = T264.T264_T255_Id) ";
		$sqlSaldoOP      .=                " LEFT JOIN T255a                ON (T255a.T255A_T255_Id         = T255.T255_Id) ";
		$sqlSaldoOP      .=                " LEFT JOIN T256                 ON (T256.T256_T255_Id           = T255.T255_Id) ";
		$sqlSaldoOP      .=                " LEFT JOIN D009                 ON (T256.T256_D009_Id           = D009.D009_Id) ";
		$sqlSaldoOP      .=                " LEFT JOIN D001                 ON (D009.D009_D001_Id           = D001.D001_Id) ";
		$sqlSaldoOP      .=                " LEFT JOIN D001A                ON (D001A.D001A_D001_Id         = D001.D001_Id) ";
		$sqlSaldoOP      .=          " WHERE T264_Simulacao.T264_D009_Id               = T264.T264_D009_Id ";
		$sqlSaldoOP      .=          "   AND T255.T255_Flag_Status                    != 99 ";
		$sqlSaldoOP      .=          "   AND T255.T255_Data_Hora_Baixa_Materiais      = '0000-00-00 00:00:00' ";
		$sqlSaldoOP      .=          "   AND T255a_SALDO.T255A_Data_Simulacao_Estoque <= T255a.T255A_Data_Simulacao_Estoque), 0) ";

		$sqlSimulacao     = " ({$sqlSaldoLocacao} - {$sqlSaldoOP}) "; */

		$rowT255 = mysql_fetch_assoc(mysql_query("SELECT * FROM T255 WHERE T255_Id = '{$r_T255_Id}'"));
		/**
		 * Definição do Grid (Tabela)
		 */
		$grid = array(
			array('colunaValor' => 'T264_Id', 'colunaTitulo' => 'T264_Id', 'colunaExibir' => false, 'chavePrimaria' => true),
			array('colunaValor' => 'D001_Id', 'colunaTitulo' => 'D001_Id', 'colunaExibir' => false),
			array('colunaValor' => 'T264_D009_Id', 'colunaTitulo' => 'T264_D009_Id', 'colunaExibir' => false),
			array('colunaValor' => 'D001_Codigo_Produto', 'colunaTitulo' => 'Código', 'colunaAlinhar' => 'left', 'callback' => 'abrirJanela', 'callbackParameter' => array('Produto', '/cad/cad002/content/form2/', 'D001_Id', '1000,583'), 'style' => 'width: 1%'),
			array('colunaValor' => 'D001_Descricao_Produto', 'colunaTitulo' => 'Descrição', 'colunaAlinhar' => 'left'),
			array('colunaValor' => 'T264_Quantidade', 'colunaTitulo' => 'Quantidade', 'colunaAlinhar' => 'right', 'style' => 'width: 1%', 'colunaCasasDecimais' => '2'),
			array('colunaValor' => 'T264_T066_Id', 'colunaTitulo' => 'Lote', 'colunaAlinhar' => 'left', 'style' => 'width: 20%', 'callback' => 'T264_T066_Id_Local'),
			array('colunaValor' => 'IF(T264_T066_Id > 0, T066_Quantidade_Estoque, D009_Quantidade_Estoque_Liquido)', 'colunaTitulo' => 'Estoque', 'colunaAlinhar' => 'right', 'style' => 'width: 1%', 'colunaCasasDecimais' => '2'),
			array('colunaValor' => 'D004_Local', 'colunaTitulo' => 'D004_Local', 'colunaExibir' => false),
			array('colunaValor' => 'T066_Quantidade_Estoque', 'colunaTitulo' => 'T066_Quantidade_Estoque', 'colunaExibir' => false),
			array('colunaValor' => 'T264A_Observacao', 'colunaTitulo' => 'Observação', 'colunaExibir' => true, 'colunaEditar' => true),
            array('colunaValor' => 'T066A_Numero_Lote', 'colunaTitulo' => 'Lote', 'colunaExibir' => false),
		);

/* 		if($paramlocacaoPadraoProducao > 0){
			$grid[] = array('colunaValor' => "{$sqlSaldoLocacao} as SaldoLocacao", 'colunaTitulo' => 'Saldo Locação', 'colunaAlinhar' => 'right', 'style' => 'width: 1%', 'colunaCasasDecimais' => '2');
			$grid[] = array('colunaValor' => "{$sqlSaldoOP} as SaldoOP", 'colunaTitulo' => 'Saldo OP', 'colunaAlinhar' => 'right', 'style' => 'width: 1%', 'colunaCasasDecimais' => '2');
			$grid[] = array('colunaValor' => "{$sqlSimulacao} as Simulacao", 'colunaTitulo' => 'Simulação', 'colunaAlinhar' => 'right', 'style' => 'width: 1%', 'colunaCasasDecimais' => '2');
		} */


		/**
		* Definição da SQL
		*/
		$from = 'T264';
		$extra = <<<EOT
			LEFT JOIN D009 on D009_Id = T264_D009_Id
			LEFT JOIN D049 ON D049_Id = D009_D049_Id
			LEFT JOIN D001 ON D001_Id = D049_D001_Id
			LEFT JOIN D037 ON D037_Id = D001_D037_Id
			LEFT JOIN T066 ON T066_Id = T264_T066_Id
			LEFT JOIN D004 ON D004_Id = T066_D004_Id
			LEFT JOIN T264A ON T264A_T264_Id = T264_Id
			LEFT JOIN D001A ON D001A_D001_Id = D001_Id 
            LEFT JOIN T066A ON T066_Id = T066A_T066_Id
			WHERE T264_T255_Id = '{$r_T255_Id}'
			AND D009_C004_Id='{$g['empresaAtual']}'
			GROUP BY D009_Id
EOT;

		// Gera o filtro e acrescenta ao $extra
		$extra = $g['ui']->gridFiltroExtra(true, $gridFiltro, $extra);
		
		/**
		 * Geração: Monta o SQL e retorna o dados
		 */
		list($geradoSql, $geradoDados) = $g['sqlAuto']->gerarSQLGrid($grid, $from, $extra);
		$sql_ = base64_encode($geradoSql);

		$disabled = ($rowT255['T255_Data_Hora_Baixa_Materiais'] != '0000-00-00 00:00:00') ? 'disabled=disabled' : false;
		$count = $g['sqlAuto']->pegarSqlCampo("count(*)", $from, $extra, true);
		echo <<<EOT
			<div class="buttonsBottomTop">
				<span class="item" style="text-align:right">Produtos<br><input type="text" value="{$count}" class="whiteBg" id="" size="4" readonly></span>
				<button id="processarProdutosBaixa" style="margin-top: 5px;" {$disabled}>Processar</button>
				<button id="baixarEstoqueMateriais" style="margin-top: 5px;" {$disabled}>Baixar Estoque</button>
				<div style="clear:both;"></div>
			</div>
			<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
				$('#processarProdutosBaixa').unbind('click').bind('click', function(data) {
					var \$buttons = {
						"Sim": function() {
							$('#{$g['divId']}').showLoading();
							$.getJSON('/est/est017/grid_func-ajax/processarProdutosBaixa/?ajax=true&T255_Id=' + encodeURIComponent('{$r_T255_Id}') + '&callback=?', function(request) {
								if (request.code) {
									var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
									dialogConfirm('Operação Ok', 'Produtos processados com sucesso!', \$buttons);
									divRefresh('{$g['divId']}', true);
								} else {
									var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
									dialogConfirm('Erro', request.data, \$buttons);
								}
								$('#{$g['divId']}').hideLoading();
							});
						$(this).dialog("destroy").remove();
						},
						"Não": function() { $(this).dialog("destroy").remove(); }
					};
					dialogConfirm('Processar produtos', 'Deseja processar os produtos utilizados nesta ordem de produção?', \$buttons);
				});

				$('#baixarEstoqueMateriais').unbind('click').bind('click', function(data) {
					var \$buttons = {
						"Sim": function() {
							$('#{$g['divId']}').showLoading();
							$.getJSON('/est/est017/grid_func-ajax/baixarEstoqueMateriais/?ajax=true&T255_Id=' + encodeURIComponent('{$r_T255_Id}') + '&sql=' + encodeURIComponent("{$sql_}") + '&callback=?', function(request) {
								if (request.code) {
									var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
									dialogConfirm('Operação Ok', 'Produtos baixados com sucesso!', \$buttons);
									divRefresh('{$g['divId']}', true);
								} else {
									var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
									dialogConfirm('Erro', request.data, \$buttons);
								}
								$('#{$g['divId']}').hideLoading();
							});
							$(this).dialog("destroy").remove();
						},
						"Não": function() { $(this).dialog("destroy").remove(); }
					};
					dialogConfirm('Baixar matéria prima', 'Deseja baixar o estoque da matéria prima? <br><br><span style="color: var(--BGVermelho)">ATENÇÃO: Este processo não poderá ser desfeito, <br>e a ordem de produção não poderá ser alterada!</span>', \$buttons);
				});
			} </script></div>
EOT;

		/**
		* Geração: Grid
		*/
		$gridTabela = $g['ui']->grid($grid, $geradoDados);
        $g['smarty']->assign('thead', $gridTabela['thead']);
        $g['smarty']->assign('tbody', $gridTabela['tbody']);
        $g['smarty']->assign('tfoot', $gridTabela['tfoot']);
		$g['smarty']->assign('gridId', md5($g['r_']));
		$g['smarty']->assign('gridPersonalizacao', json_encode(array($g['r_'], $g['divIdRoot'], $g['divId']), JSON_FORCE_OBJECT));
        gBotaoAuditoria($from);
		$g['smarty']->display('grid.tpl');



