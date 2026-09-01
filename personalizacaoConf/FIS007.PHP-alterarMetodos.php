<?php
namespace hardness;
/**
* Alterar (sobre-escrever) Métodos de uma Classe
*/

class FIS007 extends FIS007_ {
	// defina os métodos para sobreescrever

    /**
     * exportarXMLNfe
     *
     * @param Form $form
     * @return String/bool
     */ 	
     // 28/02/2025 - Personaização para exportar apenas as notas de venda, testes sistemas novo
    public function xxxexportarXMLNfe($form)
    {
        global $g;
        
        $Diretorio_Usuario = $g['pathDadosUsuario'];
        
        $Data_Inicio       = $form->campoValorEnviado('Data_Inicio');
        $Data_Fim          = $form->campoValorEnviado('Data_Fim');
        $C004_Id           = $form->campoValorEnviado('C004_Id');
        $IncluirCanceladas = $form->campoValorEnviado('IncluirCanceladas');
        $SepararCFOP       = $form->campoValorEnviado('SepararCFOP');
        $D024_Id           = $form->campoValorEnviado('D024_Id');
        $D018_Id           = $form->campoValorEnviado('D018_Id');
        $mErro             = array();
        
        if (!extension_loaded('zip')) {
            return 'Nao esta habilitado php_zip, edite seu php.ini e descomente essa linha, se nao existir basta cria-la: extension=php_zip';
        }
        
        $cData_Inicio = str_replace("/", "_", $Data_Inicio);
        $cData_Fim    = str_replace("/", "_", $Data_Fim);
        
        $dData_Inicio = troca_data_amd($Data_Inicio);
        $dData_Fim    = troca_data_amd($Data_Fim);
        
        $xData_Inicio = gCorrigeDataInverte($Data_Inicio);
        $xData_Fim    = gCorrigeDataInverte($Data_Fim);
        
        
        if (empty($xData_Inicio)) {
            return "Digite a Data Inicial";
        }
        
        if (empty($xData_Fim)) {
            return "Digite a Data Final";
        }
        
        if ($xData_Fim < $xData_Inicio) {
            return "Data final é menor que a data inicial";
        }
        
        if (empty($C004_Id)) {
            return "Selecione uma Empresa";
        }

        $cClausula = " SELECT ";
		$cClausula .= " T007_Codigo_CFOP, ";
		$cClausula .= " T007_Flag_ACP, ";
		$cClausula .= " T007_Data_Emissao, ";
		$cClausula .= " T007_Numero_Protocolo_Nfe, ";
		$cClausula .= " T007_Flag_SCAN, ";
		$cClausula .= " T007_Numero_Nota_Fiscal, ";
		$cClausula .= " T007_Numero_Protocolo_Cancelamento_Nfe, ";
		$cClausula .= " T007_Chave_Acesso_Nfe ";
		$cClausula .= " FROM T007 ";
		$cClausula .= " LEFT JOIN D024 ON D024_Id = T007_D024_Id ";
		$cClausula .= " LEFT JOIN D006 ON D006_Id = T007_D006_Id ";
		$cClausula .= " WHERE T007_C004_Id='$C004_Id' ";
		$cClausula .= " AND T007_Data_Emissao >= '$dData_Inicio' ";
		$cClausula .= " AND T007_Data_Emissao <= '$dData_Fim' ";
		$cClausula .= " AND (T007_Numero_Protocolo_Nfe IS NOT NULL AND T007_Numero_Protocolo_Nfe != '')";
		$cClausula .= " AND (T007_Flag_ACP='6' OR T007_Flag_ACP='8') ";
		$cClausula .= " AND D006_Flag_Venda_Compra_Outros='V' ";		
        if($D024_Id > 0){
            $cClausula .= " AND T007_D024_Id='$D024_Id' ";
        }
        if($D018_Id > 0){
            $cClausula .= " AND D024_D018_Id='$D018_Id' ";
        }
        if ($IncluirCanceladas != "on") {
            $cClausula .= " AND T007_Flag_Cancelada!='S' ";
        }
        $cClausula .= " ORDER BY T007_Codigo_CFOP,T007_Data_Emissao,T007_Numero_Nota_Fiscal ";
        
        $cT007 = mysql_query($cClausula);
        $erro  = mysql_error();

        if(!empty($erro)){
            return $erro;
        }
        
        if (mysql_num_rows($cT007) == 0) {
            return "Não encontrada notas entre as datas selecionadas<br>$cClausula";
        }
        
        $Diretorio_Usuario = $g['pathDadosUsuario'];
        
        $Nome_Pasta = $Diretorio_Usuario . "arq_temp/";
        
        if (file_exists($Nome_Pasta)) {
            $this->recursiveDelete($Nome_Pasta);
        }
        
        
        if (!is_dir("$Nome_Pasta")) {
            //-- CRIA A PASTA CASO ELA AINDA NAO EXISTA
            if (!mkdir("$Nome_Pasta", 0777)) {
                return "Erro ao criar a pasta: <b>$Nome_Pasta</b>";
            }
            chmod("$Nome_Pasta", 0777);
        }
        $Nome_Pasta = $Diretorio_Usuario . "/arq_temp/xml_nfe";
        if (@!is_dir("$Nome_Pasta")) {
            //-- CRIA A PASTA CASO ELA AINDA NAO EXISTA
            if (!@mkdir("$Nome_Pasta", 0777)) {
                return "Erro ao criar a pasta: <b>$Nome_Pasta</b>";
            }
            chmod("$Nome_Pasta", 0777);
        }
        
        if(!empty($D018_Id)){
            $D018=mysql_query("SELECT D018_Uf from D018 WHERE D018_Id='{$D018_Id}'");
            $mD018=mysql_fetch_array($D018);
            $D018_UF = $mD018['D018_Uf'];
        }
        
        $xCFOP_Ant     = "xxx";
        $DestinoCopias = "";
        $cArquivo_ZIP  = "$cData_Inicio" . "_a_$cData_Fim" . "_Empresa_$C004_Id" . "_UF_$D018_UF" . ".zip";
        $zip           = new \ZipArchive();
        if ($zip->open("$Nome_Pasta/$cArquivo_ZIP", \ZIPARCHIVE::CREATE) !== TRUE) {
            return "Impossivel abrir <$cArquivo_ZIP>";
        }
        
        while ($mT007 = mysql_fetch_array($cT007)) {
            if ($SepararCFOP == "on") {
                $mT007['T007_Codigo_CFOP'] = str_replace(".", "", $mT007['T007_Codigo_CFOP']);
                if ($xCFOP_Ant != $mT007['T007_Codigo_CFOP']) {
                    $xCFOP_Ant     = $mT007['T007_Codigo_CFOP'];
                    $DestinoCopias = $Nome_Pasta . "/$xCFOP_Ant";
                    if (!is_dir("$DestinoCopias")) {
                        mkdir($DestinoCopias, 0777);
                        chmod($DestinoCopias, 0777);
                    }
                }
            } else {
                $DestinoCopias = $Nome_Pasta;
            }

            $tipoDoc = ($mT007['T007_Flag_ACP'] == '6') ? 'NFe_' : 'NFCe_';
            
            //-- $Diretorio_NFe � definido no arquivo include/pasta_usuario.inc
            
            $Diretorio_NFe = $g['pathDadosAntigo'] . 'nfe';
            
            $cAno_Mes_NFe        = substr($mT007['T007_Data_Emissao'], 0, 7);
            $cArquivoProtocolado = "$Diretorio_NFe/xml/autorizado/{$cAno_Mes_NFe}/{$mT007['T007_Numero_Protocolo_Nfe']}.xml";
            $Novo_Arquivo        = "";
            $Novo_Arq            = "";
            if (file_exists($cArquivoProtocolado)) {
                
                if ($mT007['T007_Flag_SCAN'] == 'S') {
                    $Novo_Arq = $tipoDoc."SCAN_{$mT007['T007_Numero_Nota_Fiscal']}.xml";
                } else {
                    $Novo_Arq = $tipoDoc."{$mT007['T007_Numero_Nota_Fiscal']}.xml";
                }
                $Novo_Arquivo = "$DestinoCopias/$Novo_Arq";
                if (!@copy($cArquivoProtocolado, $Novo_Arquivo)) {
                    $mErro[] = "FALHA AO COPIAR O ARQUIVO! <br />ORIGEM: $cArquivoProtocolado/pDESTINO: $Novo_Arquivo";
                } else {
                    $nCopiados++;
                }
            } else {
                $mErro[] = "XML NAO EXISTE!<br />NF:{$mT007['T007_Numero_Nota_Fiscal']}<br />PROTOCOLO:{$mT007['T007_Numero_Protocolo_Nfe']}";
            }
            if ($SepararCFOP == "on") {
                @$zip->addFile($Novo_Arquivo, "XML/$xCFOP_Ant/$Novo_Arq");
            } else {
                @$zip->addFile($Novo_Arquivo, "XML/$Novo_Arq");
            }
            
            //-- PARA NFE CANCELADO
            if (!empty($mT007['T007_Numero_Protocolo_Cancelamento_Nfe'])) {
                
                $cArquivoProtocolado = "$Diretorio_NFe/xml/autorizado/ID{$mT007['T007_Numero_Protocolo_Cancelamento_Nfe']}.xml";
                $Novo_Arq            = $tipoDoc."ID{$mT007['T007_Numero_Protocolo_Cancelamento_Nfe']}.xml";
                // modelo novo, depois das alterações do evantoni
                if (!file_exists($cArquivoProtocolado)) {
                    $zenSeqEvento        = str_pad(1, 2, "0", STR_PAD_LEFT);
                    $tpEvento            = '110111';
                    $cArquivoProtocolado = "$Diretorio_NFe/xml/cancelado/ID{$tpEvento}{$mT007['T007_Chave_Acesso_Nfe']}{$zenSeqEvento}.xml";
                }
                if (file_exists($cArquivoProtocolado)) {
                    if ($mT007['T007_Flag_SCAN'] == 'S') {
                        $Novo_Arquivo = "$DestinoCopias/{$tipoDoc}SCAN_{$mT007['T007_Numero_Nota_Fiscal']}" . "_Cancelada.xml";
                        $Novo_Arq     = $tipoDoc."SCAN_{$mT007['T007_Numero_Nota_Fiscal']}" . "_Cancelada.xml";
                    } else {
                        $Novo_Arquivo = "$DestinoCopias/{$tipoDoc}{$mT007['T007_Numero_Nota_Fiscal']}" . "_Cancelada.xml";
                        $Novo_Arq     = $tipoDoc."{$mT007['T007_Numero_Nota_Fiscal']}" . "_Cancelada.xml";
                    }
                    if (!@copy($cArquivoProtocolado, $Novo_Arquivo)) {
                        $mErro[] = "FALHA AO COPIAR O ARQUIVO!<br />ORIGEM: $cArquivoProtocolado<br />DESTINO: $Novo_Arquivo";
                    } else {
                        if ($SepararCFOP == "on") {
                            @$zip->addFile($Novo_Arquivo, "XML/$xCFOP_Ant/$Novo_Arq");
                        } else {
                            @$zip->addFile($Novo_Arquivo, "XML/$Novo_Arq");
                        }
                        $nCopiados++;
                    }
                } else {
                    $mErro[] = "XML DE CANCELAMENTO NAO EXISTE!<br />NF:{$mT007['T007_Numero_Nota_Fiscal']}  PROTOCOLO:{$mT007['T007_Numero_Protocolo_Nfe']} == " . $cArquivoProtocolado;
                }    
            }           
        }
        @$zip->close();
        
        $erros = "";
        if (count($mErro) > 0) {
            $erros = "O arquivo foi gerado mas ocorreram os sequintes erros: <br />" . implode("<br />", $mErro);
        }
        
        if ($nCopiados > 0) {
            //CompactarPastas("$Nome_Pasta");
            return array("/hardness3/$Nome_Pasta/$cArquivo_ZIP",$erros);
        }
        
        return "Erro! Por favor, contato o suporte técnico";
        //MÉTODO COMENTADO, POIS SUA CHAMADA TAMBÉM ESTÁ COMENTADA
        // public function CompactarPastas($cPasta_Original){
        // global $zip;
        // foreach (glob("$cPasta_Original/*") as $oArquivo)
        // {
        // 						
        // if(is_file($oArquivo)){
        // echo "Add $oArquivo ...<br>";
        // $zip->addFile($oArquivo);
        // 					   	  	      
        // }
        // elseif(is_dir($oArquivo))
        // {   		 
        // CompactarPastas($oArquivo);
        // }
        // }
        // return;
        // }  
    }

}

