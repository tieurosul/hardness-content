<?php
namespace hardness;
/**
* Este código PHP irá substituir o callback: T055_T066_Id_Local()
*/


function _T055_T066_Id_Local ($array) {
	global $g;
	
	if (empty($array['todosCampos']['T055_D009_Id'])) {
		return '-';
	}
	
	$validade = ($array['todosCampos']['T066A_Data_Validade'] == '0000-00-00' || empty($array['todosCampos']['T066A_Data_Validade'])) ? '' : ' - '.gCorrigeData($array['todosCampos']['T066A_Data_Validade']);
	$mostrar = '- LOTE: '.$array['todosCampos']['T066A_Numero_Lote'] . ' | LOC: ' . $array['todosCampos']['D004_Local'].' - QTD '.gCorrigeNumero($array['todosCampos']['T066_Quantidade_Estoque']).$validade;
	$mostrar = empty($array['campoAtual']) ? '-' : $mostrar;
	$options = gGeraSelect('T066', 'T066_Id', 'D004_Local,format(T066_Quantidade_Estoque,0) as T066_Quantidade_Estoque, if(T066A_Data_Validade = "0000-00-00","",concat("- VAL ",DATE_FORMAT(T066A_Data_Validade,"%d/%m/%Y"))) AS T066A_Data_Validade, IF(IFNULL(T066A_Numero_Lote,"") = "", "ND", T066A_Numero_Lote)', "LEFT JOIN D004 ON T066_D004_Id = D004_Id LEFT JOIN T066A ON T066_Id = T066A_T066_Id WHERE T066_D009_Id = '{$array['todosCampos']['T055_D009_Id']}' ORDER BY D004_Local", true, $array['todosCampos']['T055_T066_Id'],"Lote: %4 - LOC: %1 - QTD %2 %3");
	$options[] = array(
		'title' => 'optgroup',
		'value' => '- - - - - - - - - - - - - - - - -'
	);
	$options[] = array(
		'title' => 'ALTERAR LOCAÇÃO',
		'value' => 'AL'
	);
	$options = gProcessaSelect($options);
	$idselect = uniqid();
	$iddiv = uniqid();
	$result = "<select id=\"{$idselect}\" onchange=\"atualizaT055Local(event, this, '{$array['todosCampos']['T055_Id']}', '{$array['todosCampos']['T055_D009_Id']}');\" onblur=\"$(this).hide(); $('#{$iddiv}').show();\" style=\"display: none;\">{$options}";
	$result .= <<<EOT
</select>
	<div id="{$iddiv}" onclick="$('#' + '{$idselect}').show(); $(this).hide();"><span class="ui-icon ui-icon-pencil" style="margin-top: -1px; position: relative; float: left;" ></span><span style="padding-right: 16px;">{$mostrar}</span></div>
	<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
		function atualizaT055Local(event, select, T055_Id, D009_Id){
			var campo = $(select).val();
			if (campo == 'AL') {
				abrirJanela(event, '{$g['divIdRoot']}', '{$g['divId']}', unique(), '', 'Locação', '/est/est003/content/est003ContentLocacoes/','&acaoId=' + encodeURIComponent(D009_Id) + '&D009_Id=' + encodeURIComponent(D009_Id) + '&T0155_Id=' + encodeURIComponent(T055_Id), [1500,500]);
				$('#{$idselect}').val(0);
				$('#{$idselect}').hide();
				$('#{$iddiv}').show();
			} else {
				$.getJSON('/imp/imp003/grid_func-ajax/atualizaT055Local/?ajax=true&T055_Id='+ encodeURIComponent(T055_Id) + '&T066_Id='+ encodeURIComponent(campo) + '&callback=?', function(request) {
					if(request.code){
						divRefresh('{$g['divId']}', true, false, false, T055_Id, 'T055_Id');
					}
				});	
			}
		}
	}</script></div>
EOT;
	return $result;
}
