<?php
namespace hardness;
/**
* Este código PHP irá substituir o callback: verificaEstoqueVermelho()
*/

function _verificaEstoqueVermelho ($array) {
	if ($array['todosCampos']['T006_Quantidade'] > $array['todosCampos']['T006_Quantidade_Estoque']) {
		return '<span style="color:red; font-weight: bold;">' . gCorrigeNumero($array['todosCampos']['T006_Quantidade_Estoque'], 3) . '</span>';
	} else {
		return gCorrigeNumero($array['todosCampos']['T006_Quantidade_Estoque'], 3);
	}
}
