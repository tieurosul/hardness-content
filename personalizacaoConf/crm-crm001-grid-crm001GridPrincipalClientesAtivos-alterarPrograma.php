<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /crm-crm001-grid-crm001GridPrincipalClientesAtivos/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/


global $g;

		$filtroObrigatorio=true;

		$clientePorEmpresa = '';
		if($g['C031']['separarClientePorEmpresa'] == 'S'){
			$clientePorEmpresa = "AND (IFNULL(D024_C004_Id,0) = '{$g['empresaAtual']}' OR IFNULL(D024_C004_Id,0) = 0)";
		}

		/**
		* Definição do Grid (Tabela)
		*/
		$grid = array(
			array('colunaValor' => 'D024_Id', 'colunaTitulo' => 'Cód', 'chavePrimaria' => true, 'colunaExibir' => true, 'style' => 'width:1%;', 'colunaAlinhar' => 'center'),
			array('colunaValor' => 'D024_Nome_Empresa', 'colunaTitulo' => 'Cliente', 'style' => 'width: 1%;'),
			array('colunaValor' => 'D024_Nome_Fantasia', 'colunaTitulo' => 'Nome Fantasia', 'style' => 'width: 1%;', 'colunaExibir' => false),
            array('colunaValor' => 'D024_Flag_Curva_ABC', 'colunaTitulo' => 'ABC', 'colunaAlinhar' => 'center', 'callback' => 'colunaCurvaABC', 'callbackParameter' => array('Estatisticas venda cliente', '/cad/cad001/outro/curvaABCCliente/', 'D024_Id', 'right', false, false, false, 'auto'), 'style' => 'width:1%'),
            array('colunaValor' => 'D024_Flag_Classe', 'colunaTitulo' => 'Cla', 'colunaAlinhar' => 'center', 'callback' => 'colunaClasseCliente', 'callbackParameter' => array('Classe cliente', '/cad/cad001/outro/classeCliente/', 'D024_Id', 'right', false, false, false, 'auto'), 'style' => 'width:1%'),
			array('colunaValor' => 'D024_Flag_Pessoa_Fisica_Juridica', 'colunaTitulo' => 'P', 'style' => 'width: 1%;', 'colunaAlinhar' => 'center'),
			array('colunaValor' => 'D024_Bairro', 'colunaTitulo' => 'Bairro', 'style' => 'width: 1%;'),
			array('colunaValor' => 'D020_Nome_Cidade', 'colunaTitulo' => 'Cidade', 'style' => 'width: 1%;'),
			array('colunaValor' => 'D018_UF', 'colunaTitulo' => 'UF', 'style' => 'width: 1%;', 'colunaAlinhar' => 'center'),
			array('colunaValor' => 'D024_Data_Cadastro', 'colunaTitulo' => 'Cadastro', 'colunaAlinhar' => 'center', 'style' => 'width: 1%;', 'callback' => 'gCorrigeData'),
			array('colunaValor' => 'vendedor.C007_Primeiro_Nome', 'colunaTitulo' => 'Vendedor', 'style' => 'width: 1%;'),
			//array('colunaValor' => 'D024_Nome_Fantasia', 'colunaTitulo' => 'Nome Fantasia'),
			// array('colunaValor' => '(select SUM(T007_Valor_Total) from T007 where T007_D024_Id=D024_Id and T007_Flag_Cancelada!="S" and T007_Data_Emissao>="$inicio" and T007_Data_Emissao<="$fim") as Total', 'colunaTitulo' => 'R$', 'colunaAlinhar' => 'right', 'style' => 'background: var(--celula-amarelo-claro);;width:1%', 'callback' => 'gCorrigeNumero'),
			// array('colunaValor' => '(select SUM(T008_Quantidade) from T007 left join T008 on T008_T007_Id=T007_Id where T007_D024_Id=D024_Id and T007_Flag_Cancelada!="S" and T007_Data_Emissao>="$inicio" and T007_Data_Emissao<="$fim") as Total', 'colunaTitulo' => 'Qte', 'colunaAlinhar' => 'right', 'style' => 'width:1%;background: var(--celula-amarelo-claro);;', 'callback' => 'gCorrigeNumero'),
			// Orç:
			array('colunaValor' => '(select SUM(T003_Valor_Total) from T003 where T003_Data_Emissao>="$inicio" and T003_Data_Emissao<="$fim" and T003_D024_Id=D024_Id and T003_Flag_Perdido!="S") as Vlr_Orc',
				'colunaTitulo' => 'Valor (R$)', 'colunaAlinhar' => 'right', 'style' => 'width: 1%; background: var(--celula-amarelo-claro);', 'callback' => 'gCorrigeNumero'
			),
			array('colunaValor' => '(select COUNT(DISTINCT T003_Id) from T003 where T003_Data_Emissao>="$inicio" and T003_Data_Emissao<="$fim" and T003_D024_Id=D024_Id and T003_Flag_Perdido!="S") as Qte_Orc',
				'colunaTitulo' => 'Quantidade', 'colunaAlinhar' => 'right', 'style' => 'width: 1%; background: #f0efc2ff;', 'callback' => 'gCorrigeNumero'
			),
			// Ped:
			array('colunaValor' => '(select SUM(T005_Valor_Total) from T005 where T005_Data_Emissao>="$inicio" and T005_Data_Emissao<="$fim" and T005_D024_Id=D024_Id and T005_Flag_Status!="8") as Vlr_Ped',
				'colunaTitulo' => 'Valor (R$)', 'colunaAlinhar' => 'right', 'style' => 'width: 1%; background: var(--celula-amarelo-claro);', 'callback' => 'gCorrigeNumero'
			),
			array('colunaValor' => '(select COUNT(DISTINCT T005_Id) from T005 where T005_Data_Emissao>="$inicio" and T005_Data_Emissao<="$fim" and T005_D024_Id=D024_Id and T005_Flag_Status!="8") as Qte_Ped',
				'colunaTitulo' => 'Quantidade', 'colunaAlinhar' => 'right', 'style' => 'width: 1%; background: #f0efc2ff;', 'callback' => 'gCorrigeNumero'
			),
			// NF:
			array('colunaValor' => '(select SUM(T007_Valor_Total) from T007 where T007_Data_Emissao>="$inicio" and T007_Data_Emissao<="$fim" and T007_D024_Id=D024_Id and T007_Flag_Cancelada!="S") as Vlr_NF',
				'colunaTitulo' => 'Valor (R$)', 'colunaAlinhar' => 'right', 'style' => 'width: 1%; background: var(--celula-amarelo-claro);', 'callback' => 'gCorrigeNumero'
			),
			array('colunaValor' => '(select SUM(T007_Valor_Total_Produtos) from T007 where T007_Data_Emissao>="$inicio" and T007_Data_Emissao<="$fim" and T007_D024_Id=D024_Id and T007_Flag_Cancelada!="S") as Vlr_Pdf_NF',
				'colunaTitulo' => 'Valor Pdt (R$)', 'colunaAlinhar' => 'right', 'style' => 'width: 1%; background: var(--celula-amarelo-claro);', 'callback' => 'gCorrigeNumero'
			),
			array('colunaValor' => '(select COUNT(DISTINCT T007_Id) from T007 where T007_Data_Emissao>="$inicio" and T007_Data_Emissao<="$fim" and T007_D024_Id=D024_Id and T007_Flag_Cancelada!="S") as Qte_NF',
				'colunaTitulo' => 'Quantidade', 'colunaAlinhar' => 'right', 'style' => 'width: 1%; background: #f0efc2ff;', 'callback' => 'gCorrigeNumero'
			),
			array('colunaValor' => 'D046_Nome', 'colunaTitulo' => 'Grupo', 'colunaExibir' => false),
			// T007_Valor_Total_Comissao
			// C007_Primeiro_Nome
		);
		/**
		* Definição da SQL
		*/
		$from = "D024";

		$extra = <<<EOT
			left join D018 on D024_D018_Id=D018_Id
			left join D020 on D024_D020_Id=D020_Id
			left join D046 on D024_D046_Id=D046_Id
			left join C007 as vendedor on vendedor.C007_Id=D024_C007_Vendedor_Interno
			left join C007 as supervisor on supervisor.C007_Id=vendedor.C007_C007_Id
			left join C007 as supervisor2 on supervisor2.C007_Id=vendedor.C007_C007_Id_2
			where D024_Flag_Comercial='A'
			and D024_Flag_Cliente = 'S'
			and D024_Flag_Ativo = 'S'
			{$clientePorEmpresa}
			and IF('{$g['C007']['C007_Flag_Vendedor']}' = 'V', vendedor.C007_Id='{$g['usuarioAtual']}',IF('{$g['C007']['C007_Flag_Vendedor']}' = 'S', (supervisor.C007_Id='{$g['usuarioAtual']}' or supervisor2.C007_Id='{$g['usuarioAtual']}' or vendedor.C007_Id='{$g['usuarioAtual']}') ,'1=1'))
EOT;

		/**
         * Botões Almir
         */
        $botoes = array(
			array('titulo' => 'Processar Carteira', 'id' => 'crm001ProcessarCarteira'),
		);
		echo Botoes::processar($botoes);

		/**
		* Botões (Versão Acima do GRID)
		*/
		if (!personalizacaoBotoes()) {
			echo <<<EOT
				<!--<div class="buttonsBottomTop">
					<button type="button" id="crm001ProcessarCarteira">Processar Carteira</button>
				</div>-->
EOT;
		}

		/**
		* Filtro
		*/
		$gridFiltro = array(
			'D024_Id' => array('titulo' => 'Cod Cliente', 'tipo' => 'text'),
			'D024_Nome_Empresa,D024_Nome_Fantasia' => array('titulo' => 'Cliente', 'tipo' => 'text'),
			'T007_Data_Emissao' => array('titulo' => 'Vendas', 'tipo' => 'data', 'naoGerarExtra' => true),
            'vendedor.C007_Primeiro_Nome' => array('titulo' => 'Vendedor', 'tipo' => 'texto'),
            'supervisor.C007_Primeiro_Nome' => array('titulo' => 'Supervisor', 'tipo' => 'texto'),
            'supervisor2.C007_Primeiro_Nome' => array('titulo' => 'Supervisor 2', 'tipo' => 'texto'),
			'D024_Bairro' => array('titulo' => 'Bairro', 'tipo' => 'text'),
			'D020_Nome_Cidade' => array('titulo' => 'Cidade', 'tipo' => 'text'),
			'D018_Uf' => array('titulo' => 'UF', 'tipo' => 'text'),
			'D024_Flag_Pessoa_Fisica_Juridica' => array('titulo' => 'Pessoa (P/F/I)?', 'tipo' => 'text'),
            'D024_Flag_Cliente' => array('titulo' => 'Cliente?', 'tipo' => 'select', 'select' => array(
                	array('title' => 'TODOS', 'value' => '', 'selected' => true),
                	array('title' => 'Sim', 'value' => 'S'),
                	array('title' => 'Não', 'value' => 'N'),
              	),
			),
            'D024_Flag_Fornecedor' => array('titulo' => 'Fornecedor ?', 'tipo' => 'select', 'select' => array(
                	array('title' => 'TODOS', 'value' => '', 'selected' => true),
                	array('title' => 'Sim', 'value' => 'S'),
                	array('title' => 'Não', 'value' => 'N'),
              	),
            ),
            'D024_Nome_Fantasia' => array('titulo' => 'Nome Fantasia', 'tipo' => 'text', 'colunaExibir' => false),
            'D046_Nome' => array('titulo' => 'Grupo', 'tipo' => 'texto'),
		);

		// Gera o filtro e acrescenta ao $extra
		$extra = $g['ui']->gridFiltroExtra(true, $gridFiltro, $extra);
		// Imprime o filtro do GRID (normalmente executado acima do GRID)
		echo $g['ui']->gridFiltroPrint($gridFiltro);

		$primeiraLinhaGridHead = "<tr><th colspan=\"12\" class=\"ui-widget-header ui-corner-all\" style=\"text-align: center;\">Cliente</th>";
		$primeiraLinhaGridHead .= "<th colspan=\"2\" class=\"ui-widget-header ui-corner-all\" style=\"text-align: center;\">Total Orçamento</th>";
		$primeiraLinhaGridHead .= "<th colspan=\"2\" class=\"ui-widget-header ui-corner-all\" style=\"text-align: center;\">Total Ped</th>";
		$primeiraLinhaGridHead .= "<th colspan=\"3\" class=\"ui-widget-header ui-corner-all\" style=\"text-align: center;\">Total NF</th>";

		// Loop para criar colunas com as datas
		$inicio = "0000-00-00";
		$fim = "0000-00-00";
		$T007_Data_Emissao = $g['ui']->getGridFiltroCampoBusca('T007_Data_Emissao');
		if (!empty($T007_Data_Emissao[0]) && !empty($T007_Data_Emissao[1])) {
			$inicio = gCorrigeDataInverte($T007_Data_Emissao[0]);
			$fim = gCorrigeDataInverte($T007_Data_Emissao[1]);
			$data = strtotime($inicio);
			$dataFim = strtotime($fim);
			$dataInicial = $data;
			$dataFinal = $fim;
			while($data < $dataFim){
				if($data == $dataInicial){
					$data1 = $data;
				} else {
					$data1 = strtotime(date('Y-m-1', $data));
				}
				
				if(strtotime(date('Y-m-t', $data)) > $dataFim){
					$data2 = $dataFim;	
				} else {
					$data2 = strtotime(date('Y-m-t', $data));
				}		
				//echo date("d/m/Y", $data1) . ' - ' . date('d/m/Y', $data2) . "<br ></12>";
				
				$data1str = date('Y-m-d', $data1);
				$data2str = date('Y-m-d', $data2);
						
				$subSql = <<<EOT
				IFNULL((SELECT SUM(T007_Valor_Total_Produtos) FROM T007 left join C004 on C004_Id=T007_C004_Id left join D006 on D006_Id=T007_D006_Id WHERE T007_Data_Emissao >= '{$data1str} 00:00:00' AND T007_Data_Emissao <= '{$data2str} 23:59:59' AND T007_D024_Id = D024_Id),0)
EOT;

				$subSqlQte = <<<EOT
				IFNULL((SELECT SUM(T008_Quantidade) FROM T007 left join T008 on T008_T007_Id=T007_Id left join C004 on C004_Id=T007_C004_Id left join D006 on D006_Id=T007_D006_Id WHERE T007_Data_Emissao >= '{$data1str} 00:00:00' AND T007_Data_Emissao <= '{$data2str} 23:59:59' AND T007_D024_Id = D024_Id),0)
EOT;

				$grid[] = array('colunaValor' => $subSql, 'colunaTitulo' => 'Valor (R$)', 'style' => 'border-left:1px solid silver;width:1%', 'colunaAlinhar' => 'right', 'callback' => 'vermelhoSeZero', 'forcarExibirImpressao' => true);
				$grid[] = array('colunaValor' => $subSqlQte, 'colunaTitulo' => 'Quantidade Produtos', 'style' => 'width:1%', 'colunaAlinhar' => 'right', 'callback' => 'vermelhoSeZero', 'forcarExibirImpressao' => true);
				$data = strtotime("+1 month", $data);
				$primeiraLinhaGridHead .= "<th colspan=\"2\" class=\"ui-widget-header ui-corner-all\" style=\"text-align: center;\">".date('m/Y', $data1)."</th>";
			}

			if(stripos($extra, "ORDER BY")){
				$query = explode("ORDER BY", $extra);
				$extra = $query[0];
				$extra .= " AND (IFNULL((SELECT SUM(T003_Valor_Total) FROM T003 WHERE (T003_Data_Emissao >= '{$inicio}' AND T003_Data_Emissao <= '{$fim}') AND T003_D024_Id = D024_Id AND T003_Flag_Perdido != 'S'), 0) != 0";
				$extra .= " OR IFNULL((SELECT SUM(T005_Valor_Total) FROM T005 WHERE (T005_Data_Emissao >= '{$inicio}' AND T005_Data_Emissao <= '{$fim}') AND T005_D024_Id = D024_Id AND T005_Flag_Status != '8'), 0) != 0";
				$extra .= " OR IFNULL((SELECT SUM(T007_Valor_Total_Produtos) FROM T007 WHERE (T007_Data_Emissao >= '{$inicio} 00:00:00' AND T007_Data_Emissao <= '{$fim} 23:59:59') AND T007_D024_Id = D024_Id AND T007_Flag_Cancelada != 'S'), 0) != 0)";
				$extra .= " ORDER BY ".$query[1];
			}else{
				$extra .= " AND (IFNULL((SELECT SUM(T003_Valor_Total) FROM T003 WHERE (T003_Data_Emissao >= '{$inicio}' AND T003_Data_Emissao <= '{$fim}') AND T003_D024_Id = D024_Id AND T003_Flag_Perdido != 'S'), 0) != 0";
				$extra .= " OR IFNULL((SELECT SUM(T005_Valor_Total) FROM T005 WHERE (T005_Data_Emissao >= '{$inicio}' AND T005_Data_Emissao <= '{$fim}') AND T005_D024_Id = D024_Id AND T005_Flag_Status != '8'), 0) != 0";
				$extra .= " OR IFNULL((SELECT SUM(T007_Valor_Total_Produtos) FROM T007 WHERE (T007_Data_Emissao >= '{$inicio} 00:00:00' AND T007_Data_Emissao <= '{$fim} 23:59:59') AND T007_D024_Id = D024_Id AND T007_Flag_Cancelada != 'S'), 0) != 0)";
			}
		}

		$primeiraLinhaGridHead .= "<th colspan=\"1\" class=\"ui-widget-header ui-corner-all\" style=\"text-align: center;\"> </th>";

		
		/**
         * Totais Almir
         */
        $totais = array(
            // sql
            array('nome' => 'Count', 'titulo' => 'Registros', 'totalizador' => 'count', 'callbackParameter' => 0),
			array('nome' => 'TotalVlrOrc', 'valor' => 'Vlr_Orc', 'titulo' => 'R$ Orç', 'totalizador' => 'soma'),
			array('nome' => 'TotalQteOrc', 'valor' => 'Qte_Orc', 'titulo' => 'Qte Orç', 'totalizador' => 'soma'),
			array('nome' => 'TotalVlrPed', 'valor' => 'Vlr_Ped', 'titulo' => 'R$ Ped', 'totalizador' => 'soma'),
			array('nome' => 'TotalQtePed', 'valor' => 'Qte_Ped', 'titulo' => 'Qte Ped', 'totalizador' => 'soma'),
			array('nome' => 'TotalVlrNF', 'valor' => 'Vlr_NF', 'titulo' => 'R$ NF', 'totalizador' => 'soma'),
			array('nome' => 'TotalQteNF', 'valor' => 'Qte_NF', 'titulo' => 'Qte NF', 'totalizador' => 'soma'),
			array('nome' => 'TotalVlrPdtNF', 'valor' => 'Vlr_Pdf_NF', 'titulo' => 'R$ Produtos NF', 'totalizador' => 'soma'),
        );

		/**
		* Geração: Monta o SQL e retorna o dados
		*/
		list($geradoSql, $geradoDados) = $g['sqlAuto']->gerarSQLGrid($grid, $from, str_replace('$inicio',"$inicio",str_replace('$fim',"$fim",$extra)),10000, false, false, false, 180000);

/* 		$extraTotal = gRetirarGroupBy($extra);
		$numeroRegistros = $g['sqlAuto']->pegarSqlCampo("count(distinct d024_id)", $from, $extraTotal); */

		/**
		* Totais(Versão Acima do GRID)
		*/
		/*if (!personalizacaoTotais(false, true)) {
			echo <<<EOT
				<div class="buttonsBottomTop">
					<span class="item" style="text-align:right">Clientes<br><input type="text" value="{$numeroRegistros}" class="whiteBg" id="" size="11" readonly></span>
				</div>
EOT;
		}*/

		/**
		* Define ação do click de abertura (ao clicar em uma linha do grid)
		*/
		echo <<<EOT
			<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
				var dbclickPrevent = false;
				$('#{$g['divId']} .grid tr').unbind('click').unbind('click').bind('click', function(data) {
					if (data.target.nodeName != 'TD' && data.target.nodeName != 'TR' && !$(event.target).hasClass('editar-linha')) { return true; }
					// Previne duplo click (abrir duas janelas)
					if (dbclickPrevent) { return false; }
					dbclickPrevent = true;
					setTimeout(function() { dbclickPrevent = false; }, 500);
					// (substituir pelo mesmo ID da chave primária do grid)
					//if (data.target.nodeName != 'TD' && data.target.nodeName != 'TR') { return true; }
					var id = $(this).closest('tr').attr('id').split('|');
					var acaoId = id[0].replace('D024_Id-', '');
					// Ação: abrir nova janela
		    		abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}' , unique(), '', 'CRM - Cliente ID: ' + acaoId, '/crm/crm001/content/crm001ContentPrincipalCRM/', '&D024_Id=' + encodeURIComponent(acaoId), 'auto');
		        });
				$('#{$g['divId']} .grid .campoEditavel').unbind('click').bind('click', function(event) { return false; });
			} </script></div>
EOT;

		// Não executar quando houver o refresh de linha
        if (empty($r_linhaGridId)) {
            echo <<<EOT
			<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
				$('#crm001ProcessarCarteira').unbind('click').unbind('click').bind('click', function(data) {
					var \$buttons = {
						"Sim": function() {
							idProgressBar = dialogAlert('Processando carteira',"<div style='border:none;width:auto;height:100px;overflow:auto'><span id='progressBar'><b><br>0% Clientes processados</b></span><img src='/hardness3/static/img/loading.gif' style='width: 24px; height: 24px; margin: 6px; float: left;' /></div>");
							crm001ProcessarCarteiraClientes(idProgressBar,1);

							function crm001ProcessarCarteiraClientes(idProgressBar, inicio){
				                $.getJSON('/sistema/ajax/processarCarteiraClientes/?ajax=true&'+ '&extra=' + encodeURIComponent("{$r_extra}") + '&inicio=' + inicio + '&callback=?', function(request) {
				                    if (request.concluido == 0) {
				                    	$('#progressBar').html('<b>'+ request.percentual + '% Clientes processados</b>');
				                    	crm001ProcessarCarteiraClientes(idProgressBar,0);
				                    }else{
				                    	if (request.code) {
				                    		//divRefresh('{$g['divId']}');
				                    		$('#dc-' + idProgressBar).dialog("destroy").remove();
				                    		dialogConfirm('Processando carteira','<b><font color="green">'+ request.percentual + '% Clientes processados</font></b>'); 
				                    		$(this).dialog("destroy").remove();  
				                        } else {
				                        	//divRefresh('{$g['divId']}');
											$('#dc-' + idProgressBar).dialog("destroy").remove();
				                            dialogConfirm('Erro!', request.data);
				                            $(this).dialog("destroy").remove();
				                        }
				                    } 
				                });	                  
					        }
						$(this).dialog("destroy").remove();
						},
						"Não": function() { $(this).dialog("destroy").remove(); }
					};
					dialogConfirm('Processar carteira?', 'Processar carteira?', \$buttons);
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
		$g['smarty']->assign('primeiraLinhaGridHead', $primeiraLinhaGridHead);
		gBotaoAuditoria($from);
		if (isset($r_generateHTML)) { ob_end_clean(); ob_start(); $g['smarty']->display('gridHTML.tpl'); } else { $g['smarty']->display('grid.tpl'); }






