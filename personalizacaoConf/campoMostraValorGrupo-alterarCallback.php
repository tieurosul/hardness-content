<?php
namespace hardness;
/**
* Este código PHP irá substituir o callback: campoMostraValorGrupo()
*/

function _campoMostraValorGrupo ($array){
	global $g;
	
	return $array['todosCampos']['D032_Descricao'];
}
