<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /est/est007/content/est007ContentMontagensEditar/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

/*
		* Divisão da Area em Conteúdos
		*/
		// Como será dividio o conteúdo? (veja em: /smarty/templates/content-*.tpl)
		$content1 = uniqid();
		$content2 = uniqid();
		$g['smarty']->assign('content1', $content1);
		$g['smarty']->assign('content2', $content2);
		$g['smarty']->assign('content1Height', '30px');
		$g['smarty']->assign('tipo', '2linhas');
		// O que será carregado em cada conteúdo?
		$actions = array(
			// identificador, url, paramentros get (opcional)
			array($content1, '/est/est007/form/est007TabMontagensFormIncluirProduto/', '&tab=geral&acaoId=' . urlencode($r_acaoId) . '&divReload=' . urlencode($content2)),
			array($content2, '/est/est007/tab/est007TabMontagensEditar/', '&acaoId=' . urlencode($r_acaoId) . '&divReload=' . urlencode($content1))
		);
		echo gProcessaAcoes($actions);
		
		
		/**
		 * Botões
		 */
		// Definição dos botões
		$botoesMid = $botoesEsq = $botoesDir = '';
		$botoesEsq .= '<button type="submit" id="est007ImprimirMontagem">Imprimir</button>';
		$botoesEsq .= '<button type="submit" id="est007ProcessarMontagem">Processar Operação</button>';
		$botoesDir .= '<button onclick="fecharJanela(\''.$g['divId'].'\'); return false;" class="btnFechar">Fechar</button>';
		$g['smarty']->assign('botoesId', $g['divId']);
		$g['smarty']->assign('botoesEsq', $botoesEsq);
		$g['smarty']->assign('botoesMid', $botoesMid);
		$g['smarty']->assign('botoesDir', $botoesDir);
		$g['smarty']->display('buttons-floatBottom.tpl');
		
		$novoIdTelaForm = uniqid();
		echo <<<EOT
		<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
			$('#est007ImprimirMontagem').unbind('click').bind('click', function(data) {
				// nova_janela('/hardness3/hardness/montagem/imprimir.php?T047_Id=' + encodeURIComponent("{$r_acaoId}") + '&validarStatus=N',0,0,750,500,'yes','no','no','no','yes','no','Nota_Emitida');
				nova_janela('/sistema/personalizacao/outro/20ec3e37ea6245803562cd7f3d1824d9/?ajax=true&T047_Id=' + encodeURIComponent("{$r_acaoId}") + '&validarStatus=N',0,0,750,500,'yes','no','no','no','yes','no','Nota_Emitida');
			});
			
			$('#est007ProcessarMontagem').unbind('click').bind('click', function(data) {
				var buttons = {
					"Sim": function() {
						var T029_Id = $('#{$g['divId']} #T029_Id').val();
						$('#{$g['divId']}').showLoading();
						$.getJSON('/est/est007/form_func-ajax/est007ProcessarMontagem/?ajax=true&T047_Id=' + encodeURIComponent("{$r_acaoId}") + '&callback=?', function(request) {
							if (request.code) {
								var buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
					    		dialogConfirm('Ok', request.data, buttons);
							} else {
								var buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
					    		dialogConfirm('Erro', request.data, buttons);
							}
							$('#{$g['divId']}').hideLoading();
							divRefresh('{$g['divId']}', true);
						});
						$(this).dialog("destroy").remove();
					},
					"Não": function() { $(this).dialog("destroy").remove(); } 
				};
				dialogConfirm('Atenção!', 'Você tem certeza? ', buttons);
			});
		} </script></div>
EOT;


