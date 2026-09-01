<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /etc-etc001-grid-etc001_grid_urls/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

        global $g;
        
        $disable = false;
		if(isset($g['c029Ids'][187]) || isset($g['c029Ids'][186])){
			$disable = true;
		}

        if (empty($r_D001_Id)){
            $r_D001_Id = isset($r_form_cad002_form1);
        }
		/**
		* Definição do Grid (Tabela)
		*/
		$grid = array(
			array('colunaValor' => "campoExclusao", 'colunaTipo' => "livre", 'callback' => 'gExcluirLinha', 'callbackParameter' => array('T144_Id', $g['divId']), 'colunaExibir' => $disable),
			array('colunaValor' => "T144_Id", 'colunaTitulo' => 'ID', 'chavePrimaria' => true, 'colunaExibir' => false),
			array('colunaValor' => "T144_Data_Cadastro", 'colunaTitulo' => 'Cadastrado Em','callback' => 'gCorrigeData'),
			array('colunaValor' => "D001_Descricao_Produto", 'colunaTitulo' => 'Produto', 'colunaExibir' => false),
			array('colunaValor' => "T144_Url", 'colunaTitulo' => 'Link'),
			array('colunaValor' => "T144_Descricao", 'colunaTitulo' => 'Descrição','colunaEditar' => true),
			array('colunaValor' => "T144_Ultimo_Acesso", 'colunaTitulo' => 'Último Acesso','callback' => 'gCorrigeData'),
			array('colunaValor' => "T144_Versao_Cache", 'colunaTitulo' => 'Possui Cache?', 'callback' => 'possuiCache'),
			array('colunaValor' => "T144_Flag_Tipo", 'colunaTitulo' => 'Tipo', 'callback' => 'tipoDocumento', 'colunaExibir' => false, 'colunaEditar' => true,'colunaEditarGeraSelect' => array(
                array('title' => '...', 'value' => ''),
				array('title' => 'Link Orçamento/Pedido', 'value' => 'O'),
                array('title' => 'Foto Sistema', 'value' => 'F'),
              ))
		);
		
		/**
		* Definição da SQL
		*/
		$from = 'T144';
		$extra = 'LEFT JOIN D001 ON D001_Id=T144_D001_Id';
		if (isset($r_D001_Id) && !empty($r_D001_Id)) {
			$extra .= " WHERE (T144_D001_Id = '{$r_D001_Id}')\n";
		}
		$extra .= <<<EOT
			ORDER BY T144_Data_Cadastro DESC
EOT;
		
		/**
		* Botões (Versão Acima do GRID)
		*/
        if ($disable) {
            echo <<<EOT
                <div class="buttonsBottomTop">
                    <button type="button" id="etc001_grid_urls_incluir">Incluir</button>
                </div>
                <div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {		
                    $('#etc001_grid_urls_incluir').unbind('click').bind('click', function(data) {
                        abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}', unique(), '', 'Cadastrar Novo Link', '/etc/etc001/form/etc001_form_urls/', '&tab=geral&D001_Id=' + encodeURIComponent("{$r_D001_Id}"), [550,180]);
                    });
                } </script></div>
EOT;
        }

		/**
		* Filtro
		*/
	  
		/**
		* Define ação do click de abertura (ao clicar em uma linha do grid)
		*/
		$novoIdTelaForm = uniqid();
		echo <<<EOT
		<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
			$('#{$g['divId']} .grid tbody tr').unbind('click').unbind('click').bind('click', function(data) {
				if (data.target.nodeName != 'TD' && data.target.nodeName != 'TR') { return true; }
				var id = $(this).closest('tr').attr('id').replace(/T144_Id-/, '');
				abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}' ,'{$novoIdTelaForm}', '', 'Abrir Link - ID: ' + id, '/etc/etc001/outros/etc001_outros_iframe/', '&acaoId=' + encodeURIComponent(id) + '&tabela={$from}', 'full');
			});
			$('#{$g['divId']} .grid .campoEditavel').unbind('click').bind('click', function(event) { return false; });
		} </script></div>
EOT;
	
		/**
		 * Geração: Monta o SQL e retorna o dados
		 */
		list($geradoSql, $geradoDados) = $g['sqlAuto']->gerarSQLGrid($grid, $from, $extra);
		
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
