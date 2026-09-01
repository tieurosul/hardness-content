<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /cad-cad001-tab-cad001tabCadastro/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/
        // Personalizacao 16/02/2024 - Felipe Carrano
        // A aba financeiro não estava habilitando mesmo com a opção no perfil 103 liberada
        // Alterando o nome da aba, apenas com um "espaço", funcionou
        
		/**
		* Cabeçalho (Versão Acima da TAB)
		*/
		if (isset($r_acaoId) && !empty($r_acaoId)) {
			$cabecalhoId = uniqid();
			echo '<div id="'.$cabecalhoId.'" class="contentCabecalho" style="height: 38px;"></div>';
			$actions = array(
				// identificador, url, paramentros get (opcional)
				array($cabecalhoId, '/cad/cad001/outro/cad001CabecalhoCliente/', '&acaoId=' . urlencode($r_acaoId), true),
			);
			echo gProcessaAcoes($actions);
		}
		
		// Form (caso seja o mesmo form em multiplas tabs - raramente utilizado) // '&formId=' . urlencode($formId)
		$formId = uniqid();
		$g['smarty']->assign('formId', $formId);
		$idTabContatos = uniqid();
		$idTabEnderecos = uniqid();
		$tabs = array();
		$tabsList = array();
	    $tabsList[] = array(uniqid(), '/cad/cad001/form/cad001FormCadastro/', 'Cadastro', "&tab=cadastro&acaoId=" . urlencode($_REQUEST['acaoId']) . '&formId=' . urlencode($formId));
        if (isset($g['c029Ids'][103])) {
			$tabsList[] = array(uniqid(), '/cad/cad001/form/cad001FormCadastro/', 'Comercial', "&tab=comercial&acaoId=" . urlencode($_REQUEST['acaoId']) . '&formId=' . urlencode($formId));
			$tabsList[] = array(uniqid(), '/cad/cad001/form/cad001FormCadastro/', 'Financeiro ',  "&tab=financeiro&acaoId=" . urlencode($_REQUEST['acaoId']) . '&formId=' . urlencode($formId));
			$tabsList[] = array(uniqid(), '/cad/cad001/form/cad001FormCadastro/', 'Fiscal', "&tab=fiscal&acaoId=" . urlencode($_REQUEST['acaoId']) . '&formId=' . urlencode($formId));
		}
		if(!empty($r_acaoId)){
			$tabsList[] = array($idTabContatos , '/cad/cad001/content/cad001contentContatos/', 'Contatos', "&tab=geral&D024_Id=" . urlencode($_REQUEST['acaoId']), true);
			$tabsList[] = array($idTabEnderecos, '/cad/cad001/grid/cad001GridEnderecos/', 'Endereços', "&acaoId=" . urlencode($_REQUEST['acaoId']));
			if (isset($g['c029Ids'][103])) {
				//$tabsList[] = array(uniqid(), '/cad/cad001/content/cad001ContentReferencias/', 'Referências', "&tab=geral&D024_Id=" . urlencode($_REQUEST['acaoId']), true);
				//$tabsList[] = array(uniqid(), '/cad/cad001/form/cad001FormCadastro/', 'Cobrança', "&tab=cobranca&acaoId=" . urlencode($_REQUEST['acaoId']) . '&formId=' . urlencode($formId));
				//$tabsList[] = array(uniqid(), '/cad/cad001/form/cad001FormCadastro/', 'Entrega', "&tab=entrega&acaoId=" . urlencode($_REQUEST['acaoId']) . '&formId=' . urlencode($formId));
				//$tabsList[] = array(uniqid(), '/cad/cad001/form/cad001FormCadastro/', 'Fiscal', "&tab=fiscal&acaoId=" . urlencode($_REQUEST['acaoId']) . '&formId=' . urlencode($formId));
				//$tabsList[] = array(uniqid(), '/cad/cad001/form/cad001FormCadastro/', 'Comercial', "&tab=comercial&acaoId=" . urlencode($_REQUEST['acaoId']) . '&formId=' . urlencode($formId));
				//$tabsList[] = array(uniqid(), '/cad/cad001/form/cad001FormCadastro/', 'Financeiro',  "&tab=financeiro&acaoId=" . urlencode($_REQUEST['acaoId']) . '&formId=' . urlencode($formId));
				//$tabsList[] = array(uniqid(), '/cad/cad001/content/cad001contentVeiculos/', $g['conf']->altText('Veículos'), "&tab=geral&D024_Id=" . urlencode($_REQUEST['acaoId']), true);
				//$tabsList[] = array(uniqid(), '/cad/cad001/content/cad001contentEmail/', 'E-Mail', "&tab=geral&acaoId=" . urlencode($_REQUEST['acaoId']), true);
				$tabsList[] = array(uniqid(), '/cad/cad001/content/cad001ContentDocumentos/', 'Documentos', "&tab=geral&acaoId=" . urlencode($_REQUEST['acaoId']). "&D024_Id=" . urlencode($_REQUEST['acaoId']), true)	;
			}
			$tabsList[] = array(uniqid(), '/cad/cad001/grid/cad001GridAgenda/', 'Agenda', "&acaoId=" . urlencode($_REQUEST['acaoId']). "&D024_Id=" . urlencode($_REQUEST['acaoId']), true);
			$tabsList[] = array(uniqid(), '/fin/fin001/content/fin001ContentCreditosClientes/', 'Créditos', "&D024_Id=" . urlencode($_REQUEST['acaoId']), true);
			$tabsList[] = array(uniqid(), '/cad/cad001/outro/cad001MostraSintegra/', 'Sintegra',  "&acaoId=" . urlencode($_REQUEST['acaoId']));
		}
		// Processa Tabs
		echo gProcessaTabs($tabsList, $tabs, true);



		// Carrega template
		$g['smarty']->assign('tabs', $tabs);
		$g['smarty']->display('tab.tpl');
		
		// Quando vier do botão (Incluir Veículos, diretamente no combo)
		if (!empty($r_acaoId) && isset($r_tab) && $r_tab == 'veiculos') {
			echo <<<EOT
				<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
					setTimeout(function() {
						$('#{$g['divId']} .tabs').tabs('select', 9);
					}, 100);
				} </script></div>
EOT;
		}
		
		// Quando vier do botão (Incluir Contatos, diretamente no combo)
		if (!empty($r_acaoId) && isset($r_tab) && $r_tab == 'contatos') {
			echo <<<EOT
				<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
					setTimeout(function() {
						$('#{$g['divId']} .tabs').tabs('select', '#{$idTabContatos}');
					}, 100);
				} </script></div>
EOT;
		}		

		// Quando vier do botão (Incluir Enderecos, diretamente no combo)
		if (!empty($r_acaoId) && isset($r_tab) && $r_tab == 'enderecos') {
			echo <<<EOT
				<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
					setTimeout(function() {
						$('#{$g['divId']} .tabs').tabs('select', '#{$idTabEnderecos}');
					}, 100);
				} </script></div>
	EOT;
			}	
