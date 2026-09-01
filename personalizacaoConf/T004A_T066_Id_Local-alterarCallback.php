<?php
namespace hardness;
/**
* Este código PHP irá substituir o callback: T004A_T066_Id_Local()
*/


function _T004A_T066_Id_Local ($array) {
	global $g;
    
	if (empty($array['todosCampos']['T004_D009_Id'])) {
		return '-';
	}

	if($array['todosCampos']['D001A_Flag_Validade'] == 'S'){

		$extraD004 = "LEFT JOIN D004  ON T066_D004_Id  = D004_Id 
				 	  LEFT JOIN T066A ON T066A_T066_Id = T066_Id 
					      WHERE T066_D009_Id = '{$array['todosCampos']['T004_D009_Id']}'
						  AND IFNULL(T066A_Flag_Ativo,'S') = 'S'
				       ORDER BY IF(T066A_Data_Validade = '0000-00-00' OR T066A_Data_Validade IS NULL,'9999-99-99', T066A_Data_Validade) ASC, T066_Quantidade_Estoque_Liquido DESC, T066_Id ASC";

		$options = gGeraSelect('T066', 'T066_Id', 'D004_Local,format(T066_Quantidade_Estoque_Liquido,0),if(T066A_Data_Validade = "0000-00-00" OR T066A_Data_Validade IS NULL,"- VAL. NÃO DEFINIDA", concat("- VAL: ",DATE_FORMAT(T066A_Data_Validade,"%d/%m/%Y"))), IF(IFNULL(T066A_Numero_Lote,"") = "", "ND", T066A_Numero_Lote)', $extraD004, true, $array['todosCampos']['T004A_T066_Id'],"LOTE: %4 - LOC: %1 (QTD:%2) %3");
		$validade = ($array['todosCampos']['T066A_Data_Validade'] > '0000-00-00') ? "- VAL: ".gCorrigeData($array['todosCampos']['T066A_Data_Validade']) : ' VAL. NÃO DEFINIDA';
		$mostrar  = "LOTE: ".$array['todosCampos']['T066A_Numero_Lote']. " | LOC: " . $array['todosCampos']['D004_Local']." (QTD: ".gCorrigeNumero($array['todosCampos']['T066_Quantidade_Estoque_Liquido'],0).")". $validade;

	} else {
		$extraD004 = "LEFT JOIN D004  ON T066_D004_Id  = D004_Id 
					  LEFT JOIN T066A ON T066A_T066_Id = T066_Id 
						  WHERE T066_D009_Id = '{$array['todosCampos']['T004_D009_Id']}'
						  AND IFNULL(T066A_Flag_Ativo,'S') = 'S'
		 			   ORDER BY T066_Quantidade_Estoque DESC";

		$options = gGeraSelect('T066','T066_Id','D004_Local,format(T066_Quantidade_Estoque_Liquido,0),if(T066A_Data_Validade = "0000-00-00" OR T066A_Data_Validade IS NULL,"", concat("- VAL: ",DATE_FORMAT(T066A_Data_Validade,"%d/%m/%Y"))), IF(IFNULL(T066A_Numero_Lote,"") = "", "ND", T066A_Numero_Lote)', $extraD004, true, false, "LOTE: %3 - LOC: %1 (QTD:%2)");
		$mostrar = "LOTE: ".$array['todosCampos']['T066A_Numero_Lote']. " | LOC: " .$array['todosCampos']['D004_Local'] . " (QTD: ".gCorrigeNumero($array['todosCampos']['T066_Quantidade_Estoque_Liquido'],0) . ")";

	}

	$mostrar = empty($array['campoAtual']) ? '-' : $mostrar;

	$options[] = array(
		'title' => 'optgroup',
		'value' => '- - - - - - - - - - - - - - - - -'
	);
/* 	$options[] = array(
		'title' => 'ALTERAR LOCAÇÃO',
		'value' => 'AL'
	); */
	$options = gProcessaSelect($options);
	$idselect = uniqid();
	$iddiv = uniqid();
	$result = "<select id=\"{$idselect}\" onchange=\"atualizaT004Local(event, this, '{$array['todosCampos']['T004_Id']}', '{$array['todosCampos']['T004_D009_Id']}');\" onblur=\"$(this).hide(); $('#{$iddiv}').show();\" style=\"display: none;\">{$options}";
	$result .= <<<EOT
</select>
	<div id="{$iddiv}" onclick="$('#{$idselect}').show().focus(); $(this).hide();"><span class="ui-icon ui-icon-pencil" style="margin-top: -1px; position: relative; float: left;" ></span><span style="padding-right: 16px;">{$mostrar}</span></div>
	<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
		function atualizaT004Local(event, select, T004_Id, D009_Id){
			var campo = $(select).val();
			if (campo == 'AL') {
				abrirJanela(event, '{$g['divIdRoot']}', '{$g['divId']}', unique(), '', 'Locação', '/est/est003/content/est003ContentLocacoes/','&acaoId=' + encodeURIComponent(D009_Id) + '&D009_Id=' + encodeURIComponent(D009_Id) + '&T004_Id=' + encodeURIComponent(T004_Id), [700,350]);
				$('#{$idselect}').val(0);
				$('#{$idselect}').hide();
				$('#{$iddiv}').show();
			} else {
				$.getJSON('/ven/ven001/grid_func-ajax/atualizaT004Local/?ajax=true&T004_Id='+ encodeURIComponent(T004_Id) + '&T066_Id='+ encodeURIComponent(campo) + '&callback=?', function(request) {
					if(request.code){
						divRefresh('{$g['divId']}', true, false, false, T004_Id, 'T004_Id');
					}
				});	
			}
		}
	}</script></div>
EOT;
	return $result;
}
