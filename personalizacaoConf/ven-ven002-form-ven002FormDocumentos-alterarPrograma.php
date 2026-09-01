<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /ven-ven002-form-ven002FormDocumentos/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/


				/**
		* PARTE 1: Variavéis envidas para o formulário
		*/
		// Variável indica qual tab do form será exibida
		$tab = (isset($_REQUEST['tab'])) ? $_REQUEST['tab'] : false;
		// Utilizada em UPDATE, é o ID do registro que está sendo editado
		$acaoId = (isset($_REQUEST['acaoId'])) ? $_REQUEST['acaoId'] : false;
		
		/**
		* PARTE 2: Definição do formulário
		*/
		// Este mesmo form será exibido em multiplas tabs ou não?
		$formMultiTab = false;
		if ($formMultiTab) {
			$formId = (isset($_REQUEST['formId'])) ? $_REQUEST['formId'] : false;
		} else {
			$formId = uniqid();
		}
		// A qual tabela este form está relacionada, e a chave primária da tabela
		$form_tabela = 'T167';
		$form_campo_pk = 'T167_Id';
		// ID do botão que irá executar o envio desse formulário (o botão provavelmente foi criado dentro do content.php, use o mesmo ID de lá)
		$enviar_btnId = array('ven002FormDocumentosBtnAdd', 'ven002FormDocumentosBtnCln', 'ven002FormDocumentosBtnNew');
        // Form que será executado ao clicar no botão
		$enviar_acaoHref = '/ven/ven002/form/ven002FormDocumentos/';

		//form editor
		$form_editor = array(
			'geral' => '{"T167_Descricao":{"top":30,"left":73,"width":342,"hidden":0},"uploadArquivo":{"top":7,"left":28,"width":208,"hidden":0},"ven002FormDocumentosBtnAdd":{"top":74,"left":480,"width":null,"hidden":0},"ven002FormImportarDoc":{"top":74,"left":576.0000610351562,"width":null,"hidden":0}}',
		);

		// Array de definição do formulário
		$r_T005_Id = isset($r_ven002FormDocumentos) ? $r_ven002FormDocumentos['T167_T005_Id'] : $r_T005_Id;
		                                                           
		$tmp = gGeraSelect('T005', 'T005_Id', 'T005_Numero_DI', "WHERE T005_Id = $r_T005_Id");
		foreach($tmp as &$value) { $value['selected'] = true; }
		// Veja documentação ou exemplos para entender todas as variavéis de definição disponivéis (automacaoFormulario.php)
		$form = new Formulario('ven002FormDocumentos');
		$formArray = array(
			'campos' => array(
				'geral' => array(
				)
			)
		);
		//$formArray['campos']['geral']['__T167_T005_Id'] 		= array('name' => '__T167_T005_Id', 'label'=>'Número Pedido', 'type' => 'select', 'extra'=>'style="width:382px;"', 'disabled' => true, 'reject'=> true, 'select' => 
		//	$tmp
		//);
		$formArray['campos']['geral']['T167_Descricao'] 	= array('name' => 'T167_Descricao', 'label'=>'Descrição', 'type' => 'textarea','length' => array(3,60));
		$formArray['campos']['geral']['uploadArquivo'] 			= array('name' => 'uploadArquivo', 'label' => 'Arquivo documento', 'type' => 'file', 'length' => array(24, 24), 'reject' => true, 'uppercase' => false);
		$formArray['campos']['geral']['T167_Nome_Arquivo'] 		= array('name' => 'T167_Nome_Arquivo', 'type' => 'hidden', 'uppercase' => false);
		if (!empty($r_acaoId)) {
			 $formArray['campos']['geral']['ven002FormDocumentosBtnNew'] = array('name' => 'ven002FormDocumentosBtnNew', 'type' => 'button', 'reload' => false, 'value' => 'Novo Registro', 'align' => 'right', 'style' => 'float:right;', 'reject' => true);
		}
		$formArray['campos']['geral']['T167_T005_Id'] 			= array('name' => 'T167_T005_Id', 'type' => 'hidden', 'value' => $r_T005_Id);
		$formArray['campos']['geral']['T167_C007_Id'] 			= array('name' => 'T167_C007_Id', 'type' => 'hidden', 'value' => $g['usuarioAtual']);
		$formArray['campos']['geral']['T167_Data_Hora_UpLoad'] 			= array('name' => 'T167_Data_Hora_UpLoad', 'type' => 'hidden', 'value' => date('Y-m-d H:i:s'));
		//$formArray['campos']['geral']['ven002FormDocumentosBtnCln'] = array('name' => 'ven002FormDocumentosBtnCln', 'type' => 'button', 'reload' => false, 'value' => 'Limpar', 'align' => 'right', 'style' => 'float:right;', 'reject' => true);
		$formArray['campos']['geral']['ven002FormDocumentosBtnAdd'] = array('name' => 'ven002FormDocumentosBtnAdd', 'type' => 'submit', 'reload' => false, 'value' => empty($r_acaoId) ? 'Incluir' : 'Alterar', 'align' => 'right', 'style' => 'float:right;', 'reject' => true);
        $formArray['campos']['geral']['ven002FormImportarDoc'] = array('name' => 'ven002FormImportarDoc', 'type' => 'button', 'reload' => false, 'value' => 'Importar', 'align' => 'right', 'style' => 'float:right;', 'reject' => true);
		$formArray['campos']['geral']['divReload'] 	= array('name' => 'divReload', 'label'=>'', 'type' => 'hidden','value' => @$r_divReload, 'reject' => true);
		
		$form->definirFormularioArray($formArray);
		
		/**
		* PARTE 3: Processamento do formulário (se foi enviado)
		* 		   E carregamento dos valores (se não foi enviado e possui acaoId)
		*/
		if ($formCodigo = $form->processarFormulario($form_tabela)) {
			// Form com erros
			if ($formCodigo == 1) {
                log("deu erro 1");
				echo gGeraAlertMsgErroForm($form);
			// Form sem erros
			} else if ($formCodigo == 2) {
                log("deu certo primeira etapa");
				// Insert ou Update?
				if (!empty($acaoId)) {
					// Update
					if ($sql = $g['sqlAuto']->gerarSQLUpdate($form_tabela, $form_campo_pk, $acaoId, $form)) {
						// Imprime uma mensagem e fecha a janela
						echo <<<EOT
								<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
									if (typeof jQuery != 'undefined') {
										divRefresh("{$g['divId']}", true);
										divRefresh('{$form->campoValorEnviado('divReload')}', true);
										divRefresh('{$g['divId']}', true);
									}
								} </script></div>
EOT;
					}
				} else {
					// Insert
					// Antes de criar o registro no banco, vamos verificar se o arquivo foi realmente enviado
					$arquivo = $form->campoValorEnviado('uploadArquivo');
					if ($arquivo['error'] == UPLOAD_ERR_OK) {
                        log("entrou aqui no certo");
						// Neste caso, guardamos o nome do arquivo no banco
						$form->campoSetarValor('T167_Nome_Arquivo', $arquivo['name'], true);
                        mysql_query("SET AUTOCOMMIT=0");
                        mysql_query("START TRANSACTION");
						if ($sql = $g['sqlAuto']->gerarSQLInsert($form_tabela, $form)) {
							// Gravar o arquivo no disco
							$pathinfo = pathinfo($arquivo['name']);
							$pathinfo['extension'] = empty($pathinfo['extension']) ? '.tmp' : $pathinfo['extension'];
							//$dir = "{$g['pathDados']}documentos/T005_".str_repeat('0',5-strlen($form->campoValorEnviado('T167_T005_Id'))).$form->campoValorEnviado('T167_T005_Id');
							$dir = "{$g['pathDados']}pedido";
							is_dir($dir) or mkdir($dir, 0777, true);
							$file = $dir . '/' . $g['mysqlLastId'] . '.' . $pathinfo['extension'];
							$retornoMoveFile = move_uploaded_file($arquivo['tmp_name'], $file);
							log("move upload file ".$arquivo['tmp_name'].' -> '.$file);
							if (!$retornoMoveFile){
			                    mysql_query("ROLLBACK");
								echo <<<EOT
									<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
										dialogConfirm('ERRO', 'Não foi possível realizar o upload do arquivo, tente novamente. Caso o problema persista informar o suporte técnico.');
										if (typeof jQuery != 'undefined') {
											divRefresh("{$g['divId']}", true);
											divRefresh('{$form->campoValorEnviado('divReload')}', true);
											divRefresh('{$g['divId']}', true);
										}
									} </script></div>
EOT;
							}
							echo <<<EOT
								<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
									if (typeof jQuery != 'undefined') {
										divRefresh("{$g['divId']}", true);
										divRefresh('{$form->campoValorEnviado('divReload')}', true);
										divRefresh('{$g['divId']}', true);
									}
								} </script></div>
EOT;
						}
					} else {
                        log("deu erro aqui");
                    }
		            mysql_query("COMMIT");
				}
			} else {
            				echo 'Deu erro ao importar arquivo';

            }

		} else {
			// Carrega os valores caso seja um update
			if (!empty($acaoId)) {
				$processado = $form->carregarValoresCampos($form_tabela, $form_campo_pk, $acaoId);
				// mostra erros de formulário uteis ao desenvolvedor
				if ($processado !== true) {
					reset($form->formArray['campos']);
					if ($tab == key($form->formArray['campos'])) {
						echo  $processado;
					}
				}
			}
		}
		
		
		/**
		* PARTE 4: Gerar o formulário
		*/
		$gerado = gGeraCamposForm($form, $tab, $acaoId, $enviar_btnId, $enviar_acaoHref, $formId, $form_campo_pk);
		
		
		/**
		* PARTE 5: Exibir o formulário
		*/
		$g['smarty']->assign('formularioID', $form->formularioID());
		$g['smarty']->assign('table', $gerado['table']); $g['smarty']->assign('form_tabela', isset($form_tabela) ? $form_tabela : '');
		echo $gerado['pagina'];
		if (!$formMultiTab) { $g["smarty"]->assign("formId", $formId); }
		$g['smarty']->display('formEditor.tpl');
		
		
		
		/**
		* Botões
		*/
		// Definição dos botões
		$botoesEsq = '';
		$botoesDir = '';
		// $botoesDir .= '<button type="submit" id="content_form1_btn">Enviar</button>';
		// $botoesDir .= '<button onclick="fecharJanela(\''.$g['divId'].'\');" class="btnFechar">Cancelar</button>';
		$g['smarty']->assign('botoesId', $g['divId']);
		$g['smarty']->assign('botoesEsq', $botoesEsq);
		$g['smarty']->assign('botoesDir', $botoesDir);
		echo $g['smarty']->fetch('buttons-floatBottom.tpl');
		// Ações dos botões
		// A ação do botão Enviar é definido dentro do formulário
        echo <<<EOT
            <div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
                $('#ven002FormImportarDoc').unbind('click').bind('click', function(data) {
                    abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}', unique(), '', 'Importar Documento', '/ven/ven002/content/ven002ContentImportarDoc/', '&T005_Id=' + encodeURIComponent('{$r_T005_Id}') + '&divReload=' + encodeURIComponent('{$r_divReload}'), [300,100]);
                });
            } </script></div>
EOT;
