<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /etc-etc017-grid-etc017GridTarefa/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/
$resposta = array('code' => true, 'data' => array());

        $T003_Id = isset($r_T003_Id) ? $r_T003_Id : false;
		$T005_Id = isset($r_T005_Id) ? $r_T005_Id : false;
		$T182_Id = isset($r_T182_Id) ? $r_T182_Id : false;
		$T007_Id = isset($r_T007_Id) ? $r_T007_Id : false;

		if (!$r_acaoId) {

			if ($T007_Id) {
				$sql = "SELECT D085_T259_Id FROM D085 WHERE D085_Mensagem LIKE 'Nota Fiscal {$T007_Id}%'";
			} else if ($T005_Id) {
				$sql = "SELECT D085_T259_Id FROM D085 WHERE D085_Mensagem LIKE 'Pedido {$T005_Id}%'";
			} else if ($T003_Id) {
				$sql = "SELECT D085_T259_Id FROM D085 WHERE D085_Mensagem LIKE 'Orcamento {$T003_Id}%'";
			}

			$r_acaoId = mysql_fetch_assoc(mysql_query($sql))['D085_T259_Id'];
		}

		//Carrega o tipo de visão
		$visao = 'horizontal';
		$file = $g['pathDadosUsuario'] . 'gridFiltro/atividadesTarefa';
		if(file_exists($file)){
			$content = unserialize(file_get_contents($file));
			$visao = $content['visao'];
		}

		//$cssHeaderGrid = "background: #444444 url(/hardness3/static/css/temas/padrao/images/ui-bg_highlight-soft_44_444444_1x100.png) 50% 50% repeat-x; color: #f9f9f9; white-space: nowrap; border: 0px solid #f9f9f9; border-left: 0px !important; -moz-border-radius: 0px; -webkit-border-radius: 0px; -khtml-border-radius: 0px; border-radius: 0px; -moz-border-radius-topleft: 4px; -webkit-border-top-left-radius: 4px; -khtml-border-top-left-radius: 4px; border-top-left-radius: 4px; -moz-border-radius-topright: 4px; -webkit-border-top-right-radius: 4px; -khtml-border-top-right-radius: 4px; border-top-right-radius: 4px;";
		$cssHeaderGrid = "background: #DDD; color: #222; white-space: nowrap; border: 0px solid #f9f9f9; border-left: 0px !important; -moz-border-radius: 0px; -webkit-border-radius: 0px; -khtml-border-radius: 0px; border-radius: 0px; -moz-border-radius-topleft: 4px; -webkit-border-top-left-radius: 4px; -khtml-border-top-left-radius: 4px; border-top-left-radius: 4px; -moz-border-radius-topright: 4px; -webkit-border-top-right-radius: 4px; -khtml-border-top-right-radius: 4px; border-top-right-radius: 4px;";
		require_once('sistema/comunicacaoEmail.php');
		if($r_T260_Id > 0){
			mysql_query("UPDATE T260 SET T260_Flag_Lido = 'S' WHERE T260_Id = '{$r_T260_Id}'");
		} else {
			mysql_query("INSERT INTO T260 (T260_Flag_Lido, T260_C007_Id, T260_T259_Id, T260_Data_Hora) VALUES ('S', '{$g['usuarioAtual']}', '{$r_acaoId}', CURRENT_TIMESTAMP())");
		}
		//$overflow = ($visao == 'horizontal') ? 'overflow-x' : 'overflow-y';
		echo <<<EOT
  			<div id="divTarefas" style="width: auto; box-sizing:border-box; padding: 10px; overflow-y: scroll; white-space: nowrap;">
EOT;
		$sqlD085 = mysql_query("SELECT * FROM D085 LEFT JOIN C007 ON C007_Id=D085_C007_Id LEFT JOIN D013 ON D013_Id=D085_D013_Id WHERE D085_T259_Id = '{$r_acaoId}' ORDER BY CONCAT(D085_Data, ' ', D085_Hora) ASC");
  		while($rowD085 = mysql_fetch_array($sqlD085)){
  			$row = $retornoAgendado = $mensagemOculta = $icones = $imagemCarta = $imagemWhats = '';
			
  			if($rowD085['D085_Entidade'] == '' && $rowD085['D085_T139_Id'] > 0){
  				$sql = mysql_query("SELECT T139_Id, T139_De, T139_Pasta, T139_Mensagem_Corpo, T139_Flag_Lido FROM T139 WHERE T139_Id = '{$rowD085['D085_T139_Id']}'");
  				$row = mysql_fetch_assoc($sql);
  				$rowD085['D085_Entidade'] = 'T139';
  				$rowD085['D085_Id_Entidade'] = $rowD085['D085_T139_Id'];
  				$row['T139_Id'] = 0;
  				$rowD085['C007_Primeiro_Nome'] = strtoupper($row['T139_De']);
  				$mensagemOculta = '';//utf8_decode($row['T139_Mensagem_Corpo']);
  				$contato = '';

	  			$mail = new comunicacaoEmail($rowD085['C007_Email_POP_Servidor'], $rowD085['C007_Email_SMTP_Usuario'], $rowD085['C007_Email_SMTP_Senha'], false, $rowD085['D085_T139_Id'], $rowD085['C007_Email_POP_Porta']);
				$anexos = $mail->listaAnexos($rowD085['D085_T139_Id']);
				$iconeCarta = ($row['T139_Flag_Lido'] == '0') ? 'background-position: -80px -98px;' : 'background-position: -96px -98px;';
				$imagemCarta = "<span class=\"ui-icon\" style=\"width: 13px; float: left; margin-left: -3px; margin-right: 2px; {$iconeCarta}\" />";
				if (sizeof($anexos) > 0) {
					$icones = "{$imagemCarta}<span class=\"ui-icon ui-icon-paper-clip\" style=\"width: 13px; float: left; margin-left: -3px; margin-right: 2px;\" />";
				} else {
					$icones = $imagemCarta;
				}
  			} else if($rowD085['D085_Entidade'] == 'T003' || $rowD085['D085_Entidade'] == 'T005' || $rowD085['D085_Entidade'] == 'T007'){

  				$sql = mysql_query("SELECT T139_Id, 
										   T139_Flag_Lido 
									  FROM T139 
									 WHERE T139_{$rowD085['D085_Entidade']}_Id = '{$rowD085['D085_Id_Entidade']}' 
									   AND T139_Pasta = 'S'
									   AND T139_Para NOT LIKE '%Whatsapp:%'
								  ORDER BY T139_Id DESC LIMIT 1");								  
  				$row = mysql_fetch_assoc($sql);

				if($row['T139_Id'] > 0){
					$imagemCarta = "<span class=\"ui-icon\" style=\"width: 13px; float: left; margin-right: 2px; background-position: -80px -97px;\" />";
				}

				$whats = mysql_query("SELECT T139_Id, 
										     T139_Flag_Lido 
									    FROM T139 
									   WHERE T139_{$rowD085['D085_Entidade']}_Id = '{$rowD085['D085_Id_Entidade']}' 
									     AND T139_Pasta = 'S'
									     AND T139_Para LIKE '%Whatsapp:%'
								    ORDER BY T139_Id DESC LIMIT 1");								  
  				$mWhats = mysql_fetch_assoc($whats);

				if ($mWhats['T139_Id']> 0){
					$imagemWhats = "<span class=\"fab fa-whatsapp\" style=\"width: 13px; float: left; margin-left: 4px;margin-right: 2px; background-position: -80px -97px;\" />";
				}

  				$contato = array($rowD085['D013_Nome_Contato'], $rowD085['D013_E_Mail'], $rowD085['D013_DDD_Telefone_1'], $rowD085['D013_Telefone_1'], $rowD085['D013_DDD_Celular'], $rowD085['D013_Celular']);
  				$rowD085['D085_Mensagem'] = "<span style=\"float: left;\">{$rowD085['D085_Mensagem']}</span>";
				//$icones = "<span class=\"ui-icon\" style=\"width: 13px; float: left; margin-right: 2px; background-position: -80px -99px;\" />";
  				$rowD085['D085_Mensagem'] .= $imagemCarta . ' ' . $imagemWhats ;
  				

  				if($rowD085['D085_Entidade'] == 'T003'){
  					$rowVenda = mysql_fetch_assoc(mysql_query("SELECT T003_Valor_Total valorTotal, T003_Status_Orcamento(T003_Flag_Status_Orcamento) status FROM T003 WHERE T003_Id = '{$rowD085['D085_Id_Entidade']}'"));
  					$sqlProdutos = mysql_query("SELECT T004_Codigo_Produto as Codigo_Produto, T004_Descricao_Produto as Descricao_Produto, T004_Quantidade as Quantidade, T004_Observacao_Entrega as Entrega, T004_Observacao as Observacao FROM T004 WHERE T004_T003_Id = '{$rowD085['D085_Id_Entidade']}'");
  				} else if($rowD085['D085_Entidade'] == 'T005'){
  					$rowVenda = mysql_fetch_assoc(mysql_query("SELECT T005_Valor_Total valorTotal, group_concat(distinct '<span>', T005_Status_Pedido(T005_Flag_Status,1), '</span>', '<br />', if(T005_Flag_Status=9, T005_Motivo_Nao_Liberado,'')) as status FROM T005 WHERE T005_Id = '{$rowD085['D085_Id_Entidade']}'"));
  					$sqlProdutos = mysql_query("SELECT T006_Codigo_Produto as Codigo_Produto, T006_Descricao_Produto as Descricao_Produto, T006_Quantidade as Quantidade, T006_Observacao_Entrega as Entrega, T006_Observacao as Observacao FROM T006 WHERE T006_T005_Id = '{$rowD085['D085_Id_Entidade']}'");
  				} else if($rowD085['D085_Entidade'] == 'T007'){
  					$rowVenda = mysql_fetch_assoc(mysql_query("SELECT T007_Valor_Total valorTotal FROM T007 WHERE T007_Id = '{$rowD085['D085_Id_Entidade']}'"));
  					$sqlProdutos = mysql_query("SELECT T008_Codigo_Produto as Codigo_Produto, T008_Descricao_Produto as Descricao_Produto, T008_Quantidade as Quantidade, T006_Observacao_Entrega as Entrega, T006_Observacao as Observacao FROM T008 LEFT JOIN T006 ON T006_Id=T008_T006_Id WHERE T008_T007_Id = '{$rowD085['D085_Id_Entidade']}'");
  				}
  				$valorTotal = gCorrigeNumero($rowVenda['valorTotal']);
  				$rowVenda['status'] = !empty($rowVenda['status']) ? " - ".$rowVenda['status'] : '';
  				$rowD085['D085_Mensagem'] .= "<div style=\"margin-left: 10px; float: left;\">R$ {$valorTotal} {$rowVenda['status']}</div>";

  				if(mysql_num_rows($sqlProdutos) > 0){
  					$gridProdutos = <<<EOT
  						<br><table style="{$cssHeaderGrid}" cellspacing="0" cellpadding="4">
  							<tr>
  								<td style="{$cssHeaderGrid} font-size: 10px; text-align: center;">Qtde</td>
  								<td style="{$cssHeaderGrid} font-size: 10px; border-left: 1px solid #silver !important;">Código</td>
  								<td style="{$cssHeaderGrid} font-size: 10px; border-left: 1px solid #silver !important;">Descrição</td>
  								<td style="{$cssHeaderGrid} font-size: 10px; border-left: 1px solid #silver !important; text-align: center;">Previsão</td>
  								<td style="{$cssHeaderGrid} font-size: 10px; border-left: 1px solid #silver !important;">Observação</td>
  							</tr>
EOT;
  					while($rowProdutos = mysql_fetch_assoc($sqlProdutos)){
  						$rowProdutos['Quantidade'] = gCorrigeNumero($rowProdutos['Quantidade']);
						$rowProdutos['Entrega'] = ($rowProdutos['Entrega'] > 0) ? " + {$rowProdutos['Entrega']} day" : '';
  						$rowProdutos['Entrega'] = date("Y-m-d", strtotime($rowD085['D085_Data'] . $rowProdutos['Entrega']));
  						$rowProdutos['Entrega'] = gCorrigeData($rowProdutos['Entrega']);
  						$gridProdutos .= <<<EOT
  							<tr>
  								<td style="border-bottom: 1px solid #DDD; border-right: 1px solid #DDD; background-color: #FFF; text-align: right; border-left: 1px solid #DDD;">{$rowProdutos['Quantidade']}</td>
  								<td style="border-bottom: 1px solid #DDD; border-right: 1px solid #DDD; background-color: #FFF;">{$rowProdutos['Codigo_Produto']}</td>
  								<td style="border-bottom: 1px solid #DDD; border-right: 1px solid #DDD; background-color: #FFF;">{$rowProdutos['Descricao_Produto']}</td>
  								<td style="border-bottom: 1px solid #DDD; border-right: 1px solid #DDD; background-color: #FFF;">{$rowProdutos['Entrega']}</td>
  								<td style="border-bottom: 1px solid #DDD; border-right: 1px solid #DDD; background-color: #FFF;">{$rowProdutos['Observacao']}</td>
  							</tr>
EOT;
  					}
  					$gridProdutos .= "</table>";
  				}
  				if($visao == 'horizontal'){
  					$rowD085['D085_Mensagem'] .= $gridProdutos;
  				}
  			} else if($rowD085['D085_Flag_Manual'] == 'S' && $rowD085['D085_T139_Id'] <= 0){
  				$rowD085['D085_Entidade'] = 'D085';
  				$rowD085['D085_Id_Entidade'] = $rowD085['D085_Id'];
  				if($rowD085['D085_Data_Previsao_Retorno'] != '0000-00-00'){
  					$dataHoraRetorno = gCorrigeData($rowD085['D085_Data_Previsao_Retorno']) . ' ' . $rowD085['D085_Hora_Previsao_Retorno'];
  					$retornoAgendado = "<div style=\" float: right;\"><span class=\"ui-icon\" style=\"float: left; opacity: 0.75; background-position: -80px -114px;\"></span>{$dataHoraRetorno}</div>";
  				}
  				$icones = "<span class=\"ui-icon\" style=\"background-position: -128px -99px; width: 13px; float: left; margin-left: -3px; margin-right: 2px;\" />";
  				$contato = array($rowD085['D013_Nome_Contato'], $rowD085['D013_E_Mail'], $rowD085['D013_DDD_Telefone_1'], $rowD085['D013_Telefone_1'], $rowD085['D013_DDD_Celular'], $rowD085['D013_Celular']);
  			} else if($rowD085['D085_Entidade'] == 'T218' || $rowD085['D085_Entidade'] == 'T224' || $rowD085['D085_Entidade'] == 'T255'){
  				if($rowD085['D085_Entidade'] == 'T218'){
  					$rowD085['D085_Mensagem'] = "";
  					$sqlProdutos = mysql_query("SELECT T218_Id, T219_Codigo_Produto as Codigo_Produto, T219_Descricao_Produto as Descricao_Produto, T219_Quantidade as Quantidade, T219_Observacao_Entrega as Entrega, T219_Observacao as Observacao FROM T219 LEFT JOIN T218 ON T218_Id=T219_T218_Id WHERE T218_Grupo = '{$rowD085['D085_Id_Entidade']}' ORDER BY T218_Id");
  				} else if($rowD085['D085_Entidade'] == 'T224'){
  					$sqlProdutos = mysql_query("SELECT T225_Codigo_Produto as Codigo_Produto, T225_Descricao_Produto as Descricao_Produto, T225_Quantidade as Quantidade, T225_Observacao_Entrega as Entrega, T225_Observacao as Observacao, T013_Data_Entrada_Estoque, T013_Numero_Nota_Fiscal FROM T225 LEFT JOIN T014 ON T014_T225_Id=T225_Id LEFT JOIN T013 ON T013_Id=T014_T013_Id WHERE T225_T224_Id = '{$rowD085['D085_Id_Entidade']}'");
  				} else if($rowD085['D085_Entidade'] == 'T255'){
  					$sqlProdutos = mysql_query("SELECT D001_Codigo_Produto as Codigo_Produto, D001_Descricao_Produto as Descricao_Produto, T256_Quantidade as Quantidade, T256_Data_Previsao_Entrega as Entrega, T256_Observacao_Producao as Observacao, T256_Quantidade_Entrada_Estoque FROM T256
  						LEFT JOIN D009 ON D009_Id=T256_D009_Id LEFT JOIN D049 ON D049_Id=D009_D049_Id LEFT JOIN D001 ON D001_Id=D049_D001_Id
  						WHERE T256_T255_Id = '{$rowD085['D085_Id_Entidade']}'");
  				}

				$rowVenda['status'] = $contato = '';
				$rowD085['D085_Mensagem'] = "<span style=\"float: left;\">{$rowD085['D085_Mensagem']}</span>";
  				if($rowD085['D085_Entidade'] == 'T224'){
  					$rowVenda = mysql_fetch_assoc(mysql_query("SELECT T224_Nome_Status status FROM T224 WHERE T224_Id = '{$rowD085['D085_Id_Entidade']}'"));
  				} else if($rowD085['D085_Entidade'] == 'T255'){
  					$rowVenda = mysql_fetch_assoc(mysql_query("SELECT IF(T255_Flag_Status='99','Finalizado','Aberto') status FROM T255 WHERE T255_Id = '{$rowD085['D085_Id_Entidade']}'"));
  				}
  				$rowVenda['status'] = !empty($rowVenda['status']) ? " - ".$rowVenda['status'] : '';
  				$rowD085['D085_Mensagem'] .= "<div style=\"margin-left: 5px; float: left;\">{$rowVenda['status']}</div>";

  				$gridProdutos = '';
  				if(mysql_num_rows($sqlProdutos) > 0){
  					unset($tr);
  					if($rowD085['D085_Entidade'] == 'T224') {
	  					$tr = <<<EOT
	  						<td style="{$cssHeaderGrid} font-size: 10px; border-right: 1px solid #silver !important; text-align: center;">Nota</td>
	  						<td style="{$cssHeaderGrid} font-size: 10px; text-align: center;">Data</td>
EOT;
					} else if($rowD085['D085_Entidade'] == 'T255') {
	  					$tr = <<<EOT
	  						<td style="{$cssHeaderGrid} font-size: 10px; text-align: center;">Concluído</td>
EOT;
					}
  					$gridProdutos_ = $gridProdutos = <<<EOT
  						<table style="{$cssHeaderGrid}" cellspacing="0" cellpadding="4">
  							<tr>
  								<td style="{$cssHeaderGrid} font-size: 10px; text-align: center; border-left: 1px solid #silver !important;">Qtde</td>
  								<td style="{$cssHeaderGrid} font-size: 10px; border-left: 1px solid #silver !important;">Código</td>
  								<td style="{$cssHeaderGrid} font-size: 10px; border-left: 1px solid #silver !important;">Descrição</td>
  								<td style="{$cssHeaderGrid} font-size: 10px; border-left: 1px solid #silver !important; text-align: center;">Previsão</td>
  								<td style="{$cssHeaderGrid} font-size: 10px; border-left: 1px solid #silver !important;">Observação</td>
  								{$tr}
  							</tr>
EOT;
					$gridProdutos = "<br>".$gridProdutos;
					$ultimoT218_Id = 0;
  					while($rowProdutos = mysql_fetch_assoc($sqlProdutos)){
  						unset($tr);
  						if($rowD085['D085_Entidade'] == 'T224') {
  							$rowProdutos['T013_Data_Entrada_Estoque'] = gCorrigeData($rowProdutos['T013_Data_Entrada_Estoque']);
		  					$tr = <<<EOT
		  						<td style="border-bottom: 1px solid #DDD; border-right: 1px solid #DDD; background-color: #FFF;">{$rowProdutos['T013_Numero_Nota_Fiscal']}</td>
  								<td style="border-bottom: 1px solid #DDD; border-right: 1px solid #DDD; background-color: #FFF;">{$rowProdutos['T013_Data_Entrada_Estoque']}</td>
EOT;
						} else if($rowD085['D085_Entidade'] == 'T255') {
  							$rowProdutos['T256_Quantidade_Entrada_Estoque'] = gCorrigeNumero($rowProdutos['T256_Quantidade_Entrada_Estoque']);
		  					$tr = <<<EOT
		  						<td style="border-bottom: 1px solid #DDD; border-right: 1px solid #DDD; background-color: #FFF; text-align: right;">{$rowProdutos['T256_Quantidade_Entrada_Estoque']}</td>
EOT;
						}
  						//Caso seja uma cotação, o grid precisa ser montado para cada cotação do grupo
  						if($rowD085['D085_Entidade'] == 'T218') {
  							if($ultimoT218_Id == 0){
  								$gridProdutos = "<div style=\"clear: both;\">Cotação {$rowProdutos['T218_Id']}</div>".$gridProdutos_;
  							} else if($rowProdutos['T218_Id'] != $ultimoT218_Id && $ultimoT218_Id > 0){
	  							$gridProdutos .= "</table>";
	  							$gridProdutos .= "<div style=\"clear: both; padding-top: 3px;\">Cotação {$rowProdutos['T218_Id']}</div>".$gridProdutos_;
	  						}
  						}
  						$ultimoT218_Id = $rowProdutos['T218_Id'];
  						if(!empty($rowProdutos['Entrega']) && !preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $rowProdutos['Entrega'])){
  							$rowProdutos['Entrega'] = date("Y-m-d", strtotime($rowD085['D085_Data'] . " +{$rowProdutos['Entrega']} day"));
  						}
  						$rowProdutos['Entrega'] = gCorrigeData($rowProdutos['Entrega']);
  						$rowProdutos['Quantidade'] = gCorrigeNumero($rowProdutos['Quantidade']);
  						$gridProdutos .= <<<EOT
  							<tr>
  								<td style="border-bottom: 1px solid #DDD; border-right: 1px solid #DDD; background-color: #FFF; text-align: right; border-left: 1px solid #DDD;">{$rowProdutos['Quantidade']}</td>
  								<td style="border-bottom: 1px solid #DDD; border-right: 1px solid #DDD; background-color: #FFF;">{$rowProdutos['Codigo_Produto']}</td>
  								<td style="border-bottom: 1px solid #DDD; border-right: 1px solid #DDD; background-color: #FFF;">{$rowProdutos['Descricao_Produto']}</td>
  								<td style="border-bottom: 1px solid #DDD; border-right: 1px solid #DDD; background-color: #FFF;">{$rowProdutos['Entrega']}</td>
  								<td style="border-bottom: 1px solid #DDD; border-right: 1px solid #DDD; background-color: #FFF;">{$rowProdutos['Observacao']}</td>
  								{$tr}
  							</tr>
EOT;
  					}
  					$gridProdutos .= "</table>";
  				}
  				if($visao == 'horizontal'){
  					$rowD085['D085_Mensagem'] .= $gridProdutos;
  				}
  			} else if($rowD085['D085_Entidade'] == 'RETORNO'){
  				$tmp = explode('</span>', $rowD085['D085_Mensagem']);
  				$rowD085['D085_Mensagem'] = $tmp[0] . '</span style="color: var(--letra-atividades);"> Lembrete de retorno p/ ' . $tmp[1];
  			}

  			$conteudo = array(
  				'mensagem' => $rowD085['D085_Mensagem'],
  				'mensagemOculta' => $mensagemOculta,
  				'usuario' => $rowD085['C007_Primeiro_Nome'],
  				'contato' => $contato,
  				'data' => date("d/m/Y H:i:s", strtotime($rowD085['D085_Data'].' '.$rowD085['D085_Hora'])),
  				'T139_Id' => $row['T139_Id'],
  				'D085_C007_Id' => $rowD085['D085_C007_Id'],
  				'D085_T259_Id' => $rowD085['D085_T259_Id'],
  				'entidade' => array($rowD085['D085_Entidade'], $rowD085['D085_Id_Entidade']),
  				'retornoAgendado' => $retornoAgendado,
  				'icones' => $icones,
  				'T003_Id'=>$T003_Id,
  				'T005_Id'=>$T005_Id,
  				'T182_Id'=>$T182_Id
  			);
  			echo montarInteracaoTarefa($conteudo, $rowD085['D085_Id'], ($row['T139_Pasta'] == 'E') ? 'R' : 'E', $visao);
  		}
  		$visaoAlterar = ($visao == 'horizontal' || empty($visao)) ? 'vertical' : 'horizontal';
  		$icone = ($visao == 'horizontal' || empty($visao)) ? 'ui-icon-view-horizontal' : 'ui-icon-view-vertical';
  		$botao = ($visao == 'horizontal' || empty($visao)) ? 'Ocultar' : 'Detalhes';

  		/*<a href="#" class="mudarVisao ui-corner-all" role="button">
			<span style="background-image: url(/hardness3/static/css/temas/padrao/images/ui-icons_ffffff_256x240.png);" class="ui-icon {$icone}" title="Alterar visão">Alterar visão</span>
		</a>*/
  		echo <<<EOT
  			</div>
  			<button type="button" class="mudarVisao ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" style="height: 23px; width: 90px" role="button" aria-disabled="false">
  				<span class="ui-button-text" style="text-align:center; margin-top: -3px; margin-left: -20px; width: 90px;">{$botao}</span>
  			</button>
			<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
  				setTimeout(function() {
					resizeGridTarefa();
				}, 50);

				function resizeGridTarefa(){
					setTimeout(function() {
						var outerHeight = parseFloat($('#{$g['divId']}').parent().css('height'));
						var outerWidth = parseFloat($('#{$g['divId']}').parent().css('width'));
						$('#divTarefas').css({'height': outerHeight - 63});
						$('#{$g['divId']}').css({'height': outerHeight - 63});
						$('#{$r_divForm}').css({'height': 60});

						$('#{$r_divForm} #D085_Mensagem').css({'width': outerWidth-110});
						$('#{$r_divForm} .formEditor-etc017FormInserirAgendaTarefa_btn').css({'left': outerWidth-77});

						$("#divTarefas").animate({ scrollTop: $('#divTarefas')[0].scrollHeight}, 1000);
					}, 50);
				}

				$('.ui-dialog:last .ui-icon-newwin').bind("click", function(data, ui) {
					setTimeout(function() {
						resizeGridTarefa();
					}, 50);
				});

				$('.mudarVisao').unbind('click').bind('click', function(data, ui) {
					$.getJSON('/etc/etc017/grid_func-ajax/mudarVisao/?ajax=true&tela=atividadesTarefa&visao={$visaoAlterar}&callback=?', function(request) {
						if(request.code){
							divRefresh('{$g['divId']}');
						}
					});
				}).css({'position': 'absolute', 'right': '3px', 'top': '3px'}).hover(function(){ $(this).addClass('ui-state-hover').css('opacity', 0.8); }, function() { $(this).removeClass('ui-state-hover').css('opacity', 1); });
			} </script></div>
EOT;

echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
