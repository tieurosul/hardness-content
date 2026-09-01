<?php
namespace hardness;
/**
* Este código PHP irá substituir o callback: campoMostraValorConta()
*/

function _campoMostraValorConta ($array){
	global $g;
	
	return $array['todosCampos']['D014_SubConta'];
}
