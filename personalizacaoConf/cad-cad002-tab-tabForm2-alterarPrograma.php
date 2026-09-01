<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /cad-cad002-tab-tabForm2/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/


		global $g;
		if(!empty($r_acaoId)){
			/**
			* Cabeçalho (Versão Acima da TAB)
			*/
			$cabecalhoId = uniqid();
			echo '<div id="'.$cabecalhoId.'" class="contentCabecalho" style="height: 38px;"></div>';
			$actions = array(
				// identificador, url, paramentros get (opcional)
				array($cabecalhoId, '/cad/cad002/outro/cad002CabecalhoProduto/', '&acaoId=' . urlencode($r_acaoId), true),
			);
			echo gProcessaAcoes($actions);
		}
		// Form (caso seja o mesmo form em multiplas tabs)
		// $formId = uniqid();
		// $g['smarty']->assign('formId', $formId);
	
		// Definição das Tabs
		$tabs = array();
		$tabsList = array();
		//array(uniqid(), '/cad/cad002/content/detalhes/', 'Marcas', "&acaoId=" . urlencode($r_acaoId)),
		//array(uniqid(), '/cad/cad002/form/form2/', 'Produto', "&tab=geral&acaoId=" . urlencode($r_acaoId)),			
		$sql = mysql_query("SELECT * FROM D001 LEFT JOIN D049 ON D049_D001_Id = D001_Id LEFT JOIN D009 ON D009_D049_Id = D049_Id LEFT JOIN D001A ON D001A_D001_Id = D001_Id WHERE D001_Id = '{$r_acaoId}' AND D009_C004_Id = '{$g['empresaAtual']}'");		
		$res = mysql_fetch_array($sql);
		$idTabela = uniqid();
		if($g['C031']['multimarcas'] == 'S'){
			$tabsList[] = array(uniqid(), '/cad/cad002/content/cad002ContentCabecalhoProduto/', 'Produto', "&tab=geral&acaoId=" . urlencode($r_acaoId) . '&divIdMain=' . urldecode($r_divIdMain));			
			$tabsList[] = array(uniqid(), '/cad/cad002/content/certificados/', 'Documentos', "&tab=geral&acaoId=" . urlencode($r_acaoId). '&D024_Id=' . urlencode($r_D024_Id));
			$tabsList[] = array(uniqid(), '/cad/cad002/content/cad002ContentUploadFotoProduto/', 'Fotos', "&D001_Id=" . urlencode($r_acaoId));
			//$tabsList[] = array(uniqid(), '/cad/cad002/form/cad002FormEspecificacoes/', 'Especificações', "&tab=geral&acaoId=" . urlencode($r_acaoId));
			$tabsList[] = array(uniqid(), '/etc/etc001/grid/etc001_grid_urls/', 'Links', "&D001_Id=" . urlencode($r_acaoId));
			//$tabsList[] = array(uniqid(), '/cad/cad002/content/cad002ContentServicos/', 'Serviços', "&D001_Id=" . urlencode($r_acaoId));
			$tabsList[] = array(uniqid(), '/cad/cad002/grid/empresas/', 'Referências', "&tab=geral&D001_Id=" . urlencode($r_acaoId) . "&acaoId=" . urlencode($res['D049_Id']));
		} else if($g['C031']['multimarcas'] == 'N'){
			$tabsList[] = array(uniqid(), '/cad/cad002/content/cad002ContentCabecalhoProdutoComum/', 'Produto', "&tab=geral&acaoId=" . urlencode($r_acaoId) . '&divIdMain=' . urldecode($r_divIdMain));		
			if(!empty($r_acaoId)){
				$tabsList[] = array($idTabela, '/cad/cad002/content/tabela/', 'Tabelas', "&tab=geral&acaoId=" . urlencode($res['D009_Id']));
				$tabsList[] = array(uniqid(), '/cad/cad002/grid/historico/', 'Histórico', "&tab=geral&tabela=T001&acaoId=" . urlencode($res['D009_Id']));
				$tabsList[] = array(uniqid(), '/cad/cad002/grid/cad002FIFO/', 'FIFO', "&tab=geral&acaoId=" . urlencode($res['D009_Id']));
				$tabsList[] = array(uniqid(), '/cad/cad002/content/cad002ContentUploadFotoProduto/', 'Fotos', "&D001_Id=" . urlencode($r_acaoId));
				//$tabsList[] = array(uniqid(), '/cad/cad002/form/cad002FormEspecificacoes/', 'Especificações', "&tab=geral&acaoId=" . urlencode($r_acaoId));
				$tabsList[] = array(uniqid(), '/etc/etc001/grid/etc001_grid_urls/', 'Links', "&D001_Id=" . urlencode($r_acaoId));
				//$tabsList[] = array(uniqid(), '/cad/cad002/content/cad002ContentServicos/', 'Serviços', "&D001_Id=" . urlencode($r_acaoId));
				//$tabsList[] = array(uniqid(), '/cad/cad002/form/substituicao/', 'Substituição', "&tab=geral&acaoId=" . urlencode($res['D049_Id']));
				$tabsList[] = array(uniqid(), '/cad/cad002/grid/empresas/', 'Referências', "&tab=geral&D001_Id=" . urlencode($r_acaoId) . "&acaoId=" . urlencode($res['D049_Id']));
				$tabsList[] = array(uniqid(), '/cad/cad002/content/cad002ContentMontagem/', 'Montagem', "&acaoId=" . urlencode($res['D009_Id']) . "&D001_Id=" . urlencode($res['D001_Id']));
				$tabsList[] = array(uniqid(), '/cad/cad002/grid/locacao/', 'Lote', "&tab=geral&acaoId=" . urlencode($res['D009_Id']));
                //$tabsList[] = array(uniqid(), '/sistema/personalizacao/tab/f01684cae690fed317d4ca8e1abc2ce6/', 'Locação', "&tab=geral&acaoId=" . urlencode($res['D009_Id']));
				//$tabsList[] = array(uniqid(), '/cad/cad002/form/tributos/', 'Tributos', "&tab=geral&acaoId=" . urlencode($res['D049_Id']));
				$tabsList[] = array(uniqid(), '/cad/cad002/content/certificados/', 'Documentos', "&tab=geral&acaoId=" . urlencode($r_acaoId). '&D024_Id=' . urlencode($r_D024_Id));
				$tabsList[] = array(uniqid(), '/cad/cad002/grid/orcamentosProduto/', 'Orçamentos', "&tab=geral&acaoId=" . urlencode($res['D009_Id']), false, true);
				$tabsList[] = array(uniqid(), '/cad/cad002/grid/pedidosProduto/', 'Pedidos', "&tab=geral&acaoId=" . urlencode($res['D009_Id']), false, true);
				$tabsList[] = array(uniqid(), '/cad/cad002/grid/cad002GridCotacoesNovo/', 'Cotações', "&tab=geral&acaoId=" . urlencode($res['D009_Id']), false, true);
				$tabsList[] = array(uniqid(), '/cad/cad002/grid/cad002GridOrdemCompraNovo/', 'OC', "&tab=geral&acaoId=" . urlencode($res['D009_Id']), false, true);	
				$tabsList[] = array(uniqid(), '/cad/cad002/grid/cad002GridOrdemProducao/', 'OP', "&tab=geral&acaoId=" . urlencode($res['D009_Id']), false, true);	
				$tabsList[] = array(uniqid(), '/cad/cad002/grid/cad002GridFornecedoresProduto/', 'Fornecedores', "&tab=geral&acaoId=" . urlencode($r_acaoId), false, true);	
				$tabsList[] = array(uniqid(), '/cad/cad002/grid/cad002GridSimilares/', 'Similares', "&tab=geral&acaoId=" . urlencode($res['D009_Id']), false, true);
				$tabsList[] = array(uniqid(), '/cad/cad002/grid/cad002GridRegraConversao', 'Conversão de Unidade', "&tab=geral&acaoId=" . urlencode($res['D001_Id']), false, true); // confirmar	
				if ($g["C031"]["plataformaECommerce"] == "nuvemshop" || $g["C031"]["plataformaECommerce2"] == "nuvemshop") {
					$tabsList[] = array(uniqid(), '/cad/cad002/content/cad002ContentNuvemShop/', 'Nuvemshop', "&D001A_Id=" . urlencode($res['D001A_Id']) . "&D001_Id=" . urlencode($res['D001_Id']));
				}
			}
		} else {
			if($res['D001_Flag_Multimarcas'] != 'N') {
				$tabsList[] = array(uniqid(), '/cad/cad002/content/cad002ContentCabecalhoProduto/', 'Produto', "&tab=geral&acaoId=" . urlencode($r_acaoId) . '&divIdMain=' . urldecode($r_divIdMain));			
				$tabsList[] = array(uniqid(), '/cad/cad002/content/certificados/', 'Documentos', "&tab=geral&acaoId=" . urlencode($r_acaoId). '&D024_Id=' . urlencode($r_D024_Id));
				$tabsList[] = array(uniqid(), '/cad/cad002/content/cad002ContentUploadFotoProduto/', 'Fotos', "&D001_Id=" . urlencode($r_acaoId));
				//$tabsList[] = array(uniqid(), '/cad/cad002/form/cad002FormEspecificacoes/', 'Especificações', "&tab=geral&acaoId=" . urlencode($r_acaoId));
				$tabsList[] = array(uniqid(), '/etc/etc001/grid/etc001_grid_urls/', 'Links', "&D001_Id=" . urlencode($r_acaoId));
				//$tabsList[] = array(uniqid(), '/cad/cad002/content/cad002ContentServicos/', 'Serviços', "&D001_Id=" . urlencode($r_acaoId));
				$tabsList[] = array(uniqid(), '/cad/cad002/grid/empresas/', 'Referências', "&tab=geral&D001_Id=" . urlencode($r_acaoId) . "&acaoId=" . urlencode($res['D049_Id']));
			} else {
				$tabsList[] = array(uniqid(), '/cad/cad002/content/cad002ContentCabecalhoProdutoComum/', 'Produto', "&tab=geral&acaoId=" . urlencode($r_acaoId) . '&divIdMain=' . urldecode($r_divIdMain));		
				$tabsList[] = array(uniqid(), '/cad/cad002/grid/historico/', 'Histórico', "&tab=geral&tabela=T001&acaoId=" . urlencode($res['D009_Id']));
				$tabsList[] = array($idTabela, '/cad/cad002/content/tabela/', 'Tabelas', "&tab=geral&acaoId=" . urlencode($res['D009_Id']));
				//$tabsList[] = array(uniqid(), '/cad/cad002/grid/locacao/', 'Locação', "&tab=geral&acaoId=" . urlencode($res['D009_Id']));
                $tabsList[] = array(uniqid(), '/sistema/personalizacao/content/f01684cae690fed317d4ca8e1abc2ce6/', 'Locação', "&tab=geral&acaoId=" . urlencode($res['D009_Id']));
				$tabsList[] = array(uniqid(), '/cad/cad002/content/certificados/', 'Documentos', "&tab=geral&acaoId=" . urlencode($r_acaoId). '&D024_Id=' . urlencode($r_D024_Id));
				$tabsList[] = array(uniqid(), '/cad/cad002/content/cad002ContentUploadFotoProduto/', 'Fotos', "&D001_Id=" . urlencode($r_acaoId));
				//$tabsList[] = array(uniqid(), '/cad/cad002/form/cad002FormEspecificacoes/', 'Especificações', "&tab=geral&acaoId=" . urlencode($r_acaoId));
				$tabsList[] = array(uniqid(), '/etc/etc001/grid/etc001_grid_urls/', 'Links', "&D001_Id=" . urlencode($r_acaoId));
				//$tabsList[] = array(uniqid(), '/cad/cad002/content/cad002ContentServicos/', 'Serviços', "&D001_Id=" . urlencode($r_acaoId));
				//$tabsList[] = array(uniqid(), '/cad/cad002/form/substituicao/', 'Substituição', "&tab=geral&acaoId=" . urlencode($res['D049_Id']));
				$tabsList[] = array(uniqid(), '/cad/cad002/grid/empresas/', 'Referências', "&tab=geral&D001_Id=" . urlencode($r_acaoId) . "&acaoId=" . urlencode($res['D049_Id']));
				$tabsList[] = array(uniqid(), '/cad/cad002/content/cad002ContentMontagem/', 'Montagem', "&acaoId=" . urlencode($res['D009_Id']) . "&D009_Id=" . urlencode($res['D009_Id']) . "&D001_Id=" . urlencode($res['D001_Id']) );
				//$tabsList[] = array(uniqid(), '/cad/cad002/form/tributos/', 'Tributos', "&tab=geral&acaoId=" . urlencode($res['D049_Id']));
				$tabsList[] = array(uniqid(), '/cad/cad002/grid/orcamentosProduto/', 'Orçamentos', "&tab=geral&acaoId=" . urlencode($res['D009_Id']), false, true);
				$tabsList[] = array(uniqid(), '/cad/cad002/grid/pedidosProduto/', 'Pedidos', "&tab=geral&acaoId=" . urlencode($res['D009_Id']), false, true);
				$tabsList[] = array(uniqid(), '/cad/cad002/grid/cad002GridCotacoesNovo/', 'Cotações', "&tab=geral&acaoId=" . urlencode($res['D009_Id']), false, true);
				$tabsList[] = array(uniqid(), '/cad/cad002/grid/cad002GridOrdemCompraNovo/', 'OC', "&tab=geral&acaoId=" . urlencode($res['D009_Id']), false, true);
			}
		}
		// Processa Tabs
		$g['conteudo']['pagina'] = gProcessaTabs($tabsList, $tabs);

		// Quando vier do botão (Incluir Contatos, diretamente no combo)
		if (isset($r_abrirTab) && $r_abrirTab == 'tabela') {
			echo <<<EOT
				<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
					setTimeout(function() {
						$('#{$g['divId']} .tabs').tabs('select', '#{$idTabela}');
					}, 100);
				} </script></div>
EOT;
		}
		
		// Carrega template
		$g['smarty']->assign('tabs', $tabs);
		$g['smarty']->display('tab.tpl');


