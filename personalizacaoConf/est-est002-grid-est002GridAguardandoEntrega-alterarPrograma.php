<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /est-est002-grid-est002GridAguardandoEntrega/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

    // Felipe Kadanos - 05/02/2026 - Chamado 46452
    // Personalizado a pedido do Miguel para que seja feita confimação da entrega automaticamente depois de Gerar o romaneio. Linha 278

        global $g;

		$exibirCorreios = false;
		if(!(empty($g['C004']['C004_Codigo_Agencia_Correios'])) && !(empty($g['C004']['C004_Login_Portal_Postal'])) && !(empty($g['C004']['C004_Senha_Portal_Postal']))){
			$exibirCorreios = true;
		}

		$habilitaMDFe = false;
		if(isset($g['c029Ids'][242])){
			$habilitaMDFe = true;
		}

		$grid = array(
            array('colunaValor' => 'campoOpcoesAguardandoEntrega1', 'colunaTipo' => 'livre', 'callback' => 'campoOpcoesAguardandoEntrega1', 'style' => 'width:18px; padding: 3px 10px 3px 10px;', 'colunaTitulo' => 'CE', 'colunaAlinhar' => 'center'),
            array('colunaValor' => 'retornarPedidoViaCupom', 'colunaTitulo' => 'CF', 'colunaTipo' => 'livre', 'callback' => 'retornarPedidoViaCupom', 'style' => 'width:18px; padding: 3px 10px 3px 10px;'),
            array('colunaValor' => 'campoRetornarExpedicao', 'colunaTipo' => 'livre', 'callback' => 'campoRetornarExpedicao', 'style' => 'width:18px; padding: 3px 10px 3px 10px; background-color: #ececec;', 'colunaTitulo' => 'RE', 'colunaAlinhar' => 'center', 'colunaExibirNivel' => 2, 'colunaExibirCallbackNivel' => 2),
            array('colunaValor' => "T007_Flag_Imprimir_Romaneio", 'colunaTitulo'=>'-', 'callback' => 'validaCampoCheckboxRomaneio', 'colunaAlinhar' => 'center', 'style'=>'width:1%;', 'colunaExibir' => true, 'colunaEditarTodosTriggerPHP' => false, 'colunaEditarTodos' => true, 'colunaEditarTipo' => 'flag', 'colunaEditarExtra' => 'S|N'),
            array('colunaValor' => 'T284_T276_Id', 'colunaTitulo' => 'T284_T276_Id', 'colunaExibir' => false),
            array('colunaValor' => 'T007_Flag_Cancelada', 'colunaTitulo' => 'T276_Flag_Cancelada', 'colunaExibir' => false),
            array('colunaValor' => 'T007_Flag_Marcado', 'colunaTitulo' => 'MDF-e', 'callback' => 'validaFlagNotaMarcada', 'colunaEditarTodos' => 'S', 'colunaEditarTipo' => 'flag', 'colunaEditarExtra' => 'N|S','colunaExibir' => $habilitaMDFe,'colunaAlinhar' => 'center'),
            array('colunaValor' => 'campoOpcoesAguardandoEntrega2','colunaTitulo' => '', 'colunaTitulo' => 'Pedido','colunaTipo' => 'livre', 'callback' => 'campoOpcoesAguardandoEntrega2', 'style' => 'width:18px; padding: 3px 20px 3px 20px;'),
            array('colunaValor' => 'imprimirMinutaDeDespacho', 'colunaTipo' => 'livre', 'colunaTitulo' => 'Minuta', 'callback' => 'imprimirMinutaDeDespacho', 'style' => 'width:30px; padding: 3px 10px 3px 20px;', 'colunaAlinhar' => 'center',),
            array('colunaValor' => 'impressaoEtiqueta', 'colunaTipo' => 'livre', 'callback' => 'abrirNotificacao', 'callbackParameter' => array('Imprimir Etiquetas', '/sistema/impressao/etiquetas/notafiscal/', 'T007_Id', 'right', false, '', '<span class="ui-icon ui-icon-tag"></span>', 420), 'style' => 'width:18px; padding: 3px 10px 3px 10px;', 'colunaTitulo' => 'IE', 'colunaAlinhar' => 'center', 'colunaExibir' => false),
            array('colunaValor' => 'coletaEtiquetaCorreio', 'colunaTitulo' => 'Correio', 'colunaTipo' => 'livre', 'callback' => 'correioPostaEtiqueta', 'style' => 'width:18px; padding: 3px 17px;','colunaExibir' => $exibirCorreios, 'colunaAlinhar' => center),
            array('colunaValor' => '', 'colunaTipo' => 'livre', 'colunaTitulo' => 'Opções', 'callback' => 'campoOpcoesAguardandoEntrega', 'colunaExibir' => false),
            array('colunaValor' => 'T005_Id', 'colunaTitulo' => 'Pedido', 'chavePrimaria' => true),
            array('colunaValor' => 'T007_Id', 'colunaTitulo' => 'T007_Id', 'chavePrimaria' => true, 'colunaExibir' => false),
            array('colunaValor' => 'T005_T275_Id', 'colunaTitulo' => 'Romaneio', 'callback' => 'retornaImpressaoRomaneio', 'colunaAlinhar' => center),
            array('colunaValor' => 'T005_Estoque_BOX', 'colunaTitulo' => 'Box'),
            array('colunaValor' => 'T007_Quantidade_Volumes', 'colunaTitulo' => 'Volumes', 'style' => 'width:1%', 'colunaAlinhar' => 'center'),
        	array('colunaValor' => 'UFCliente.D018_UF', 'colunaTitulo' => 'UF','colunaExibir' => $habilitaMDFe),
        	array('colunaValor' => 'UFClienteEntrega.D018_UF', 'colunaTitulo' => 'UF Entrega', 'colunaAlinhar' => 'center','colunaExibir' => $habilitaMDFe),
            array('colunaValor' => 'D024_Nome_Empresa', 'colunaTitulo' => 'Cliente'),
            array('colunaValor' => 'T005_Data_Emissao', 'colunaTitulo' => 'Emissão', 'callback' => 'gCorrigeData'),
            array('colunaValor' => 'T005_Data_Saida', 'colunaTitulo' => 'Saída', 'callback' => 'gCorrigeData'),
            array('colunaValor' => 'T005_Data_Entrega', 'colunaTitulo' => 'Prazo entrega', 'callback' => 'gCorrigeData'),
			array('colunaValor' => 'if(T005_Flag_ACP=\'1\',\'NOTA FISCAL\',if(T005_Flag_ACP=\'3\',\'ACP\',if(T005_Flag_ACP=\'5\',\'NOTA D1\',if(T005_Flag_ACP=\'2\',\'NÃO FATURAR\',if(T005_Flag_ACP=\'6\',\'NF-e\',if(T005_Flag_ACP=\'7\',\'NFS-e\',if(T005_Flag_ACP=\'4\',\'CUPOM FISCAL\',\'<font color=red>FALTA</font>\'))))))) as Tipo_Documento', 'colunaTitulo' => 'Tipo'),
            array('colunaValor' => 'T007_Numero_Nota_Fiscal', 'colunaTitulo' => 'NF', 'colunaAlinhar' => 'center', 'callback' => 'validaCampoNFOrcamentos'),
            array('colunaValor' => 'T276_Numero_MDFe', 'colunaTitulo' => 'MDF-e', 'colunaAlinhar' => 'left','colunaExibir' => $habilitaMDFe, 'callback' => 'abrirJanela', 'callbackParameter' => array('Editar MDF-e', '/fis/fis012/content/fis012MdfeCadastro/', 'T284_T276_Id', '1000,583'), 'colunaExibir' => false),
            array('colunaValor' => 'T276_Flag_Cancelada', 'colunaTitulo' => 'T276_Flag_Cancelada', 'colunaExibir' => false),
            array('colunaValor' => 'T005_Observacao_1', 'colunaTitulo' => 'Observação', 'style' => 'color: var(--BGVermelho);max-width:200px;white-space:normal;'),
            array('colunaValor' => 'D022_Id', 'colunaTitulo' => 'Id_Transportadora', 'colunaExibir' => false),
            array('colunaValor' => 'D022_Nome_Empresa', 'colunaTitulo' => 'Transportadora'),
            array('colunaValor' => 'D006_Descricao_Comercial', 'colunaExibir' => false, 'colunaTitulo' => 'CFOP'),
            array('colunaValor' => 'D006_Codigo_CFOP', 'colunaTitulo' => 'CFOP', 'colunaAlinhar' => 'center'),
            array('colunaValor' => 'T005_Data_Hora_Almoxarifado', 'colunaTitulo' => 'Hora Almox', 'callback' => 'gCorrigeData'),
            array('colunaValor' => '(SELECT GROUP_CONCAT(DISTINCT(C008_Tipo)) AS Tipo
        										FROM T006 
        										LEFT JOIN D009 ON D009_Id=T006_D009_Id 
        										LEFT JOIN D049 ON D049_Id=D009_D049_Id 
        										LEFT JOIN D001 ON D001_Id=D049_D001_Id 
        										LEFT JOIN C008 ON C008_Id=D001_C008_Id 
        										WHERE T006_T005_Id=T005_Id) as Tipos', 'colunaTitulo' => 'Tipos', 'colunaExibir' => false),
            array('colunaValor' => 'Liberou.C007_Primeiro_Nome as Nome_Liberou', 'colunaTitulo' => 'Liberou'),
            array('colunaValor' => 'Vendedor.C007_Primeiro_Nome as Nome_Vendedor', 'colunaTitulo' => 'Vendedor'),
            array('colunaValor' => 'D020_Nome_Cidade', 'colunaTitulo' => 'Cidade', 'colunaExibir' => false),
            array('colunaValor' => 'T005_Status_Pedido(T005_Flag_Status,2) as T005_Status', 'colunaTitulo' => 'Status', 'colunaExibir' => false),
            array('colunaValor' => 'T005_Flag_Status', 'colunaTitulo' => 'Status', 'colunaExibir' => false),
            array('colunaValor' => 'T005_Flag_Reenviado_Almoxarifado', 'colunaTitulo' => 'Reenvio', 'colunaExibir' => false),
            array('colunaValor' => 'T005_D024_Id', 'colunaTitulo' => 'IdCliente', 'colunaExibir' => false),
            array('colunaValor' => 'D036_Tipo_Transporte', 'colunaTitulo' => 'TipoTransp', 'colunaExibir' => false),
            array('colunaValor' => 'Separou.C007_Primeiro_Nome as Nome_Separou', 'colunaTitulo' => 'Separou', 'colunaExibir' => false),
            array('colunaValor' => 'T005_Valor_Total', 'colunaTitulo' => 'Total', 'colunaExibir' => false),
            array('colunaValor' => 'T005_Prazos(T005_Id) as Prazo', 'colunaTitulo' => 'Prazo', 'colunaExibir' => false),
            array('colunaValor' => 'C004_Emite_NF_Caixa', 'colunaTitulo' => 'Emite NFE', 'colunaExibir' => false),
            array('colunaValor' => 'Conferiu.C007_Primeiro_Nome as Nome_Conferiu', 'colunaTitulo' => 'Conferiu', 'colunaExibir' => 's'),
            array('colunaValor' => 'T005_Flag_Expedicao', 'colunaTitulo' => 'T005_Flag_Expedicao', 'colunaExibir' => false),
            array('colunaValor' => 'sum(T006_Quantidade)', 'colunaTitulo' => 'Qtde', 'colunaCasasDecimais' => '2', 'colunaExibir' => false),
			array('colunaValor' => 'T007_Valor_Total', 'colunaTitulo' => 'Total', 'colunaAlinhar' => 'right', 'colunaCasasDecimais' => '2', 'colunaMetrica' => 'S', 'colunaExibir' => false),
			array('colunaValor' => 'T007_Flag_ACP', 'colunaTitulo' => 'Tipo', 'callback' => 'validaTipo', 'colunaExibir' => false),
			array('colunaValor' => 'CCe', 'colunaTitulo' => 'CC-e', 'colunaTipo' => 'livre', 'callback' => 'enviarCCe', 'colunaExibir' => false),  			  
        );
		
		/**
		 * Definição da SQL
		 */
		$from = "T005";
		
		$extra = <<<EOT
			left join T006 on T005_Id=T006_T005_Id
			left join T007 on T007_T005_Id=T005_Id and T007_Flag_Cancelada!='S'
			left join D024 on D024_Id=T005_D024_Id
			left join D020 on D020_Id=D024_D020_Id
			left join D018 as UFCliente on UFCliente.D018_Id=D024_D018_Id
			left join D021 on D021_Id=T005_D021_Id
			left join D022 on D022_Id=T007_D022_Id
			left join D006 on D006_Id=T005_D006_Id
			left join C007 as Vendedor on Vendedor.C007_Id=T005_C007_Id_Vendedor_Interno
			left join C004 on C004_Id=T005_C004_Id
			left join D036 on T005_D036_Id=D036_Id 
			left join D148 on D148_Id=T007_D148_Id_Entrega
            left join D018 as UFClienteEntrega on UFClienteEntrega.D018_Id=D148_D018_Id
			LEFT JOIN T268 ON T268_T007_Id=T007_Id
			left join C007 as Liberou on Liberou.C007_Id=T005_Liberacao_Credito_Id
			left join C007 as Separou on Separou.C007_Id=T005_Usuario_Pecas
			left join C007 as Conferiu on Conferiu.C007_Id=T005_C007_Id_Conferiu
			left join T284 on T284_T007_Id=T007_Id
			left join T276 on T276_Id=T284_T276_Id
			where (T005_C004_Id={$g['empresaAtual']}
			and T005_Flag_Status='4')
			group by T005_Id 
			order by T005_Id
EOT;

		$extraTotal = <<<EOT
			left join T007 on T007_T005_Id=T005_Id and T007_Flag_Cancelada!='S'
			left join D024 on D024_Id=T005_D024_Id
			left join D020 on D020_Id=D024_D020_Id
			left join D018 on D018_Id=D024_D018_Id
			left join D021 on D021_Id=T005_D021_Id
			left join D022 on D022_Id=T007_D022_Id
			left join D006 on D006_Id=T005_D006_Id
			left join C007 as Vendedor on Vendedor.C007_Id=T005_C007_Id_Vendedor_Interno
			left join C004 on C004_Id=T005_C004_Id
			left join D036 on T005_D036_Id=D036_Id 
			LEFT JOIN T268 ON T268_T007_Id=T007_Id
			left join C007 as Liberou on Liberou.C007_Id=T005_Liberacao_Credito_Id
			left join C007 as Separou on Separou.C007_Id=T005_Usuario_Pecas
			left join C007 as Conferiu on Conferiu.C007_Id=T005_C007_Id_Conferiu
			where (T005_C004_Id={$g['empresaAtual']}
			and T005_Flag_Status='4')
EOT;

		/**
		* Filtro
		*/
		$gridFiltro = array(
            'T005_Id' => array('titulo' => 'Pedido', 'tipo' => 'numero'),
            'T005_Estoque_BOX' => array('titulo' => 'Box', 'tipo' => 'texto'),
            'D024_Nome_Empresa' => array('titulo' => 'Cliente', 'tipo' => 'texto'),
            'UFCliente.D018_UF' => array('titulo' => 'UF', 'tipo' => 'texto'),
            'UFClienteEntrega.D018_UF' => array('titulo' => 'UF Entrega', 'tipo' => 'texto'),
            'T276_Numero_MDFe' => array('titulo' => 'Número MDF-e', 'tipo' => 'texto'),
            'T005_Data_Emissao' => array('titulo' => 'Emissão', 'tipo' => 'data'),
            'T005_Data_Saida' => array('titulo' => 'Saída', 'tipo' => 'data'),
            'D022_Nome_Empresa' => array('titulo' => 'Transportadora', 'tipo' => 'texto'),
            'D006_Codigo_CFOP' => array('titulo' => 'CFOP', 'tipo' => 'texto'),
            'T007_Flag_Marcado' => array('titulo' => 'Gerar MDFe', 'tipo' => 'select', 'select' => array(
                array('title' => 'Todas', 'value' => ''),
                array('title' => 'Sim', 'value' => 'S'),
                array('title' => 'Não', 'value' => 'N'),
              	),
        	),
            'Liberou.C007_Primeiro_Nome' => array('titulo' => 'Liberou', 'tipo' => 'texto'),
            '(SELECT GROUP_CONCAT(DISTINCT(C008_Tipo)) AS Tipo
                                        FROM T006 
                                        LEFT JOIN D009 ON D009_Id=T006_D009_Id 
                                        LEFT JOIN D049 ON D049_Id=D009_D049_Id 
                                        LEFT JOIN D001 ON D001_Id=D049_D001_Id 
                                        LEFT JOIN C008 ON C008_Id=D001_C008_Id 
                                        WHERE T006_T005_Id=T005_Id)' => array('titulo' => 'Tipo', 'tipo' => 'texto'),
			'T005_T275_Id' => array('titulo' => 'Romaneio', 'tipo' => 'texto'),
			'Vendedor.C007_Primeiro_Nome' => array('titulo' => 'Vendedor', 'tipo' => 'texto'),
			'T007_Numero_Nota_Fiscal' => array('titulo' => 'Nota Fiscal', 'tipo' => 'numero'),			
        );
		// Gera o filtro e acrescenta ao $extra
		$extra = $g['ui']->gridFiltroExtra(true, $gridFiltro, $extra);

		$extra_ = base64_encode(gRetirarGroupBy(gRetirarOrderBy($extra)));

		/**
		* Botões (Versão Acima do GRID)
		*/
/*
		echo <<<EOT
			<div class="buttonsBottomTop">
				<button type="button" id="est002GridRomaneioImprimir">Gerar romaneio</button-->				
			
				<button type="button" id="est002GridEntregaPedidosSelecionados">Confirmar coleta</button>
EOT;

		if($exibirCorreios){
			echo <<<EOT
				<button type="button" id="est002GridPostarEtiquetaCorreio">Imprimir correio</button>
EOT;
		}
		if($habilitaMDFe){
			echo <<<EOT
				<button type="button" id="est002GerarMdfe">Gerar MDF-e</button>
EOT;
		}
*/
	

        // Não executar quando houver o refresh de linha
        if (empty($r_linhaGridId)) {
            echo <<<EOT
			</div>

			<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {

				$('#est002GridEntregaPedidosSelecionados').bind('click', function(data) {
					var \$buttons = {
						"Sim": function() {
							idProgressBar = dialogAlert('Coletar pedidos',"<span id='progressBar'>Processando 0%</span><img src='/hardness3/static/img/loading.gif' style='width: 24px; height: 24px; margin: 6px; float: left;' />");
				            ven002ColetarPedidosLoop(idProgressBar,1); 
				            $(this).dialog("destroy").remove();
						},
						"Não": function() { $(this).dialog("destroy").remove(); }
					};
					dialogConfirm('Confirmar entrega', 'Confirma a coleta dos pedidos selecionados?? ', \$buttons);
				});

				$('#est002GerarMdfe').unbind('click').bind('click', function(data) {
                    $('#{$g['divId']}').showLoading();
                    $.getJSON('/fis/fis012/grid_func-ajax/gerarMDFeCabecalho/?ajax=true&callback=?', function(request) {
                        if (request.code) {
                            $('#{$g['divId']}').hideLoading();
                            var T276_Id=request.T276_Id;
                            abrirJanela(data, '{$g['divIdRoot']}', '{$g['divIdParent']}', unique(), '', 'Editar MDF-e - ID: ' + T276_Id, '/fis/fis012/content/fis012MdfeCadastro/', '&tab=geral&acaoId=' + encodeURIComponent(T276_Id), [1000,550]);
                            divRefresh('{$g['divIdParent']}', true);
                            divRefresh('{$g['divId']}', true);
                        } else {
                            $('#{$g['divId']}').hideLoading();
                            dialogConfirm('Erro', request.data);
                        }                                           
                    });
                });

				function ven002ColetarPedidosLoop(idProgressBar, inicio, resolve){
                	$.getJSON('/est/est002/grid_func-ajax/confirmarEntregaVariosPedidos/?ajax=true&extra=' + encodeURIComponent('{$extra_}') + '&inicio=' + inicio + '&callback=?', function(request) {
	                    if (request.concluido == 0) {

	                    	if(request.erroIntegracao){
	                    		$('#progressBar').html('<b>Processado '+ request.percentual + '%</b>' + '</b><br/><br/>Pedidos coletados: <b>' + request.ok + '</b><br/>Pedidos não coletados: <b> ' + request.erro + '</b><br/><br/><b><font color="red"> ' + request.erroSistema + '</font></br>Os pedidos abaixo<font color="blue"> foram coletados</font>, mas houveram erros ao enviar os dados de faturamento.<br/></br></b>' + request.erroIntegracao);
	                    	}else{
	                    		$('#progressBar').html('<b>Processado '+ request.percentual + '%</b>' + 'Pedidos coletados','<b>Processado '+ request.percentual + '%</b>' + '</b><br/><br/>Pedidos coletados: <b>' + request.ok + '</b><br/>Pedidos não coletados: <b> ' + request.erro + '</b><br/><br/><font color="red"><b>' + request.erroSistema + '</b></font>');
	                    	}
	                    	ven002ColetarPedidosLoop(idProgressBar,0,resolve);
	                    }else{
	                    	$('#dc-' + idProgressBar).dialog("destroy").remove();
	                    	
	                    	if(request.erroIntegracao){
	                    		dialogConfirm('Pedidos coletados','<b>Processado '+ request.percentual + '%</b>' + '</b><br/><br/>Pedidos coletados: <b>' + request.ok + '</b><br/>Pedidos não coletados: <b> ' + request.erro + '</b><br/><br/><b><font color="red"> ' + request.erroSistema + '</font></br>Os pedidos abaixo<font color="blue"> foram coletados</font>, mas houveram erros ao enviar os dados de faturamento.<br/></br></b>' + request.erroIntegracao);
	                    	}else{
	                    		dialogConfirm('Pedidos coletados','<b>Processado '+ request.percentual + '%</b>' + '</b><br/><br/>Pedidos coletados: <b>' + request.ok + '</b><br/>Pedidos não coletados: <b> ' + request.erro + '</b><br/><br/><font color="red"><b>' + request.erroSistema + '</b></font>');
	                    	}

	                    	if (request.code) {
	                            divRefresh('{$g['divId']}');
	                			$(this).dialog("destroy").remove();     
	                        } 
	                    } 
						resolve(request);
	                });	                  
		        }
					
				async function executarColetaPedidos(dialogElement) {
					const idProgressBar = dialogAlert('Coletar pedidos',"<span id='progressBar'>Processando 0%</span><img src='/hardness3/static/img/loading.gif' style='width: 24px; height: 24px; margin: 6px; float: left;' />");
					await new Promise(resolve => {
						ven002ColetarPedidosLoop(idProgressBar, 1, resolve);
					});
					$(dialogElement).dialog("destroy").remove();
				}

				$('#est002GridRomaneioImprimir').unbind('click').bind('click', function(data) {
					var \$buttons = {
						"Sim": function() {
							$('#{$g['divId']}').showLoading();
						    $.getJSON('/est/est002/grid_func-ajax/gerarNumeroRomaneio/?ajax=true&extra=' + encodeURIComponent('{$extra_}') + '&callback=?', function(request) {
								if (request.code) {
									var \$buttons = { "Fechar": function() { 
										$(this).dialog("destroy").remove(); 
										// Personalizado para confirmar a entraga dos pedidos após gerar o romaneio
											executarColetaPedidos(this);
										//										
										nova_janela('/est/est002/outro/est002ImprimirRomaneioDeColeta/?ajax=true&T275_Id=' + encodeURIComponent(request.data) + '&extra=' + encodeURIComponent('{$extra_}') ,0,0,850,700,'yes','no','no','no','yes','no','Impressao_Romaneio');
									} }
						    		dialogConfirm('Ok', 'Número de romaneio gerado com sucesso!', \$buttons);
								} else {
									var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
						    		dialogConfirm('Erro', request.data, \$buttons);
								}
								$('#{$g['divId']}').hideLoading();
								divRefresh('{$g['divId']}');
							});
								$(this).dialog("destroy").remove();
							},
							"Não": function() { $(this).dialog("destroy").remove(); }
					};
					dialogConfirm('Gerar romaneio', 'Gerar número de romaneio de coleta?  ', \$buttons);
				});

				$('#est002GridPostarEtiquetaCorreio').bind('click', function(data) {
					var \$buttons = {
						"Sim": function() {
								idProgressBar = dialogAlert('Processando',"<span id='progressBar'><b> Processando 0% </b></span><img src='/hardness3/static/img/loading.gif' style='width: 24px; height: 24px; margin: 6px; float: left;' />");
					            est002GridPostarEtiquetaCorreioLoop(idProgressBar,1);
					            $(this).dialog("destroy").remove();
							},
							"Não": function() { $(this).dialog("destroy").remove(); }
						};
						dialogConfirm('Correio', 'Confirmar a postagem e impressão das etiquetas dos pedidos selecionados?<br><br><font color="red"><b>**Pedidos com código de rastreio preenchido não serão postados novamente.</b></font>', \$buttons);
				});

				function est002GridPostarEtiquetaCorreioLoop(idProgressBar, inicio){
                	$.getJSON('/est/est002/grid_func-ajax/postarEtiquetasCorreiosPedidosMarcados/?ajax=true&extra=' + encodeURIComponent("{$extra_}") + '&inicio=' + inicio + '&callback=?', function(request) {
	                    if (request.concluido == 0) {
	                    	$('#progressBar').html('<b>Processado '+ request.percentual + '%</b>' + '</b><br/><br/>Pedidos postado: <b>' + request.ok + '</b><br/>Pedidos com código de rastreio: <b>' + request.possuiCodigoRastreio + '</b><br/>Pedidos com erro: <b> ' + request.erro + '</b><br/>Pedidos com transportadora errada: <b> ' + request.transportadoraErrada + '</b><br /><br/><b> <font color="red">' + request.erros + '</font></b>');
	                    	est002GridPostarEtiquetaCorreioLoop(idProgressBar,0);
	                    }else{
	                    	var ok = request.ok;
	                    	var percentual = request.percentual;
	                    	var erro = request.erro;
	                    	var erros = request.erros;
	                    	var transportadoraErrada = request.transportadoraErrada;
	                    	var codigosRastreio = request.codigosRastreio;
	                    	var possuiCodigoRastreio = request.possuiCodigoRastreio;

	                    	if (request.code) {
	                            divRefresh('{$g['divId']}');
	                			$.getJSON('/est/est002/grid_func-ajax/gerarEtiquetasCorreio/?ajax=true&codigosRastreio=' + encodeURIComponent(codigosRastreio) + '&callback=?', function(request) {
									dialogConfirm('Pedidos atualizados','<b>Processado '+ percentual + '%</b>' + '</b><br/><br/>Pedidos postados: <b>' + ok + '</b><br/>Pedidos com código de rastreio: <b>' + possuiCodigoRastreio + '</b><br/>Pedidos com erro: <b> ' + erro + '</b><br/>Pedidos com transportadora errada: <b> ' + transportadoraErrada + '</b><br /><br/><b> <font color="red">' + erros + '</font></b>' + '</b><br/>' + request.retornoEtiquetas);
	                    			$('#dc-' + idProgressBar).dialog("destroy").remove();				                
	                    		}); 
	                        } 
	                    } 
	                });	                  
		        }

			} </script></div>
EOT;
        }


		// Modificações no extra para suportar um SELECT T007 em vez de T005
		$modExtra = str_replace("left join T007 on T007_T005_Id=T005_Id and T007_Flag_Cancelada!='S'", "", $extra);
		$modExtra = "left join T005 on T007_T005_Id=T005_Id and T007_Flag_Cancelada!='S'" . $modExtra;
		$modExtra = gRetirarGroupBy($modExtra);
		$modExtra = gRetirarOrderBy($modExtra);

		$filtroExtra_ = base64_encode($modExtra);

		/**
         * Totais Almir
         */
        $totais = array(
            // sql
            array('nome' => 'Count', 'titulo' => 'Registros', 'totalizador' => 'count', 'callbackParameter' => 0),
        );

		/**
		 * Geração: Monta o SQL e retorna o dados
		 */
		list($geradoSql, $geradoDados) = $g['sqlAuto']->gerarSQLGrid($grid, $from, $extra);	
		
		// Botões
		$botoes = array(
			array('titulo' => 'Gerar romaneio', 'id' => 'est002GridRomaneioImprimir'),
			array('titulo' => 'Confirmar coleta', 'id' => 'est002GridEntregaPedidosSelecionados'),
			array('titulo' => 'Imprimir correio', 'id' => 'est002GridPostarEtiquetaCorreio', 'exibirCallback' => "return $exibirCorreios;"),
			array('titulo' => 'Gerar MDF-e', 'id' => 'est002GerarMdfe', 'exibirCallback' => 'return isset($g["c029Ids"][242]);'),
			array('titulo' => 'Imprimir nota fiscal', 'id' => 'est002BtnImprimirNotaFiscal')
		);
		echo Botoes::processar($botoes);

		// Imprime o filtro do GRID (normalmente executado acima do GRID)
		echo $g['ui']->gridFiltroPrint($gridFiltro);
		
		$novoIdTelaForm = uniqid();
		
		/**
		 * Totalizadores
		 */
		/*if (!personalizacaoTotais(false, true)) { 
			$extraTotal = $g['ui']->gridFiltroExtra(false, $gridFiltro, $extraTotal, false, false, true);
		    $sqlTotal = $g['sqlAuto']->pegarSqlCampos("COUNT(distinct T005_Id)",
		        	        	                       $from,
		        	            	                   $extraTotal);

			list($numeroRegistros) = $sqlTotal;

			echo <<<EOT
				<div class="buttonsBottomTop">
					<span class="item" style="text-align:right">Pedidos<br><input type="text" class="whiteBg" id="inputQtePedidos" size="11" value="{$numeroRegistros}" readonly></span>
					<div style="clear:both;"></div>
				</div>
EOT;
		}
		fimTotais();*/
		
/* 		echo <<<EOT
		<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
			$('#{$g['divId']} .grid tbody tr').unbind('click').unbind('click').bind('click', function(data) {
				if (data.target.nodeName != 'TD' && data.target.nodeName != 'TR') { return true; }
				var id = $(this).closest('tr').attr('id').split('|');
				var T005_Id = id[0].replace(/T005_Id-/, '');
				abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}' ,'{$novoIdTelaForm}', '', 'Pedido: ' + T005_Id  + ' - ' + $(this).closest('tr').find('td:eq(3)').text(), '/est/est002/content/est002ContentPedido/', '&acaoId=' + encodeURIComponent(T005_Id) + '&tabela={$from}', [700,500]);
			});
		} </script></div>
EOT; */

		echo  <<<EOT
			<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
				acaoClickLinha('{$g['divId']}', function(data, array) {
					abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}' ,'{$novoIdTelaForm}', '', 'Pedido: ' + array['todosCampos']['T005_Id'], '/est/est002/content/est002ContentPedido/', '&acaoId=' + encodeURIComponent(array['todosCampos']['T005_Id']) + '&tabela=T005_Id', [700,500]);
				});
			} </script></div>
EOT;

        // Não executar quando houver o refresh de linha
        if (empty($r_linhaGridId)) 
		{
			$flagType = base64_encode("T007_Flag_Imprimir_Romaneio = 'S'");

            echo <<<EOT
			<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
				$('#est002BtnImprimirNotaFiscal').unbind('click').bind('click', function(data) {
					nova_janela('/ven/ven003/grid_func-ajax/ven003ImprimirMarcadas/?ajax=true&flagType={$flagType}&extra=' + encodeURIComponent('{$filtroExtra_}'),0,0,750,500,'yes','no','no','no','yes','no','Impressao Danfe');
                });
			} </script></div>
EOT;
        }


		
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
		if (isset($r_generateHTML)) { ob_end_clean(); ob_start(); $g['smarty']->display('gridHTML.tpl'); } else { $g['smarty']->display('grid.tpl'); }

