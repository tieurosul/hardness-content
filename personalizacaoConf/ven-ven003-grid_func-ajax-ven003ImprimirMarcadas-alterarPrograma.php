<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /ven-ven003-grid_func-ajax-ven003ImprimirMarcadas/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

		global $g;
		require_once("bibliotecas/classes/VEN003.php");
		$VEN003 = new VEN003();
		$extra = base64_decode($r_extra);

		$flagType = (empty($r_flagType) ? "T007_Flag_Impresso='M'" : base64_decode($r_flagType));

		//die($extra);
		$extra = gInsertExtraWhere($extra, $flagType);
		$extra = gInsertGroupBy($extra, "T007_Id");
		$extra = gRetirarOrderBy($extra);
		// Valir o LEFT JOIN T007 com espaço no final para nao pegar a tabela estendida T007A
		if (strpos(strtoupper($extra),'LEFT JOIN T007 ')){
			$sql = "SELECT T007_Id,T007_Numero_Protocolo_Nfe,T007_Data_Emissao,if(T007_Flag_ACP!='3',LPAD(T007_Numero_Nota_Fiscal,7,'0'),T007_Numero_Nota_Fiscal) as NFe FROM T008 {$extra}";
		} else {
			$sql = "SELECT T007_Id,T007_Numero_Protocolo_Nfe,T007_Data_Emissao,if(T007_Flag_ACP!='3',LPAD(T007_Numero_Nota_Fiscal,7,'0'),T007_Numero_Nota_Fiscal) as NFe FROM T007 {$extra}";
		}
		$retorno = $VEN003->imprimirNFeMarcada(false, $sql);
		if($retorno === true){
			require_once("bibliotecas/PDFMerger/PDFMerger.php");
			$pdf = new \PDFMerger;
			$sql = mysql_query($sql);
			while($res = mysql_fetch_array($sql)){
				//error_log("qual NF deu erro : ".$res['T007_Id']);
				if(!empty($res['T007_Numero_Protocolo_Nfe'])) {
					$AnoMesNFe=substr($res['T007_Data_Emissao'],0,7);
					$arquivoXML = '../hardness/'.$g['pathDados'].'nfe/xml/autorizado/'.$AnoMesNFe.'/'.$res['T007_Numero_Protocolo_Nfe'].'.xml';
					$pdf->addPDF('../hardness/'.$g['pathDados'].'nfe/danfe/danfe_'.$res['T007_Numero_Protocolo_Nfe'].'.pdf', 'all');
				}
			}
			$pdf->merge('../hardness/'.$g['pathDados'].'nfe/danfe', 'danfe.pdf');
			die();
		} else {
			$resposta['code'] = false;
			$resposta['data'] = 'Nao foi possível gerar os DANFES para impressao';
		}


