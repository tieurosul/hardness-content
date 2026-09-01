<?php
namespace hardness;
/**
* Alterar (sobre-escrever) Métodos de uma Classe
*/

class FIS012 extends FIS012_ {
	// defina os métodos para sobreescrever

	        /* Raquel 08/01/2019*/
        /**
         * finalizar MDFe
         *
         * @param String $T276_Id
         * @return true
         */        
        public function finalizarMDFe($T276_Id){
            global $g;

            if(empty($T276_Id)){
                return "Não encontrado o ID do MDF-e (T276_Id)";
            }

            // Validação dados gerais MDFe
            $T276 = mysql_query("SELECT T276_Versao_MDFe,
                            T276_Numero_Protocolo_MDFe,
                            T276_Flag_Cancelada,
                            T276_Flag_Encerrada,
                            T276_D018_Id_Carregamento,
                            T276_D018_Id_Descarregamento,
                            T276_Informacoes_Adicionais_Fisco,
                            T276_Informacoes_Adicionais_Contribuinte,
                            T276_Data_Inicio_Viagem,
                            T276_Data_Emissao,
                            T276_Numero_MDFe,
                            T276_UF_Percurso,
                            carregamentoUF.D018_UF as carregamentoUF,
                            descarregamentoUF.D018_UF as descarregamentoUF
                       FROM T276
                  LEFT JOIN D018 as carregamentoUF ON carregamentoUF.D018_Id=T276_D018_Id_Carregamento
                  LEFT JOIN D018 as descarregamentoUF ON descarregamentoUF.D018_Id=T276_D018_Id_Descarregamento
                      WHERE T276_Id='{$T276_Id}'
                    ");
            $mT276 = mysql_fetch_assoc($T276);
            if($mT276['T276_Versao_MDFe'] != '3.00'){
                return "Versão ".$mT276['T276_Versao_MDFe']." do MDF-e, no cadastro da empresa está incorreta.";
            }
            if(!empty($mT276['T276_Numero_Protocolo_MDFe']) || $mT276['T276_Flag_Cancelada'] == 'S' || $mT276['T276_Flag_Encerrada'] == 'S'){
                return "MDF-e já foi enviado, não é possível ser finalizado.";
            }
            if(empty($mT276['T276_D018_Id_Carregamento'])){
                return "Necessário preencher UF de carregamento, no cabeçalho do MDF-e.";       
            }
            if(empty($mT276['T276_D018_Id_Descarregamento'])){
                return "Necessário preencher UF de descarregamento, no cabeçalho do MDF-e.";       
            }
            if(strlen($mT276['T276_Informacoes_Adicionais_Fisco']) > 2000){
                return "Informações Interesse fisco, em adicionais, ultrapassou 2000 caracteres, necessário diminuir seu tamanho. Este campo esta com " . strlen($mT276['T276_Informacoes_Adicionais_Fisco']) . " caracteres.";
            }
            if(strlen($mT276['T276_Informacoes_Adicionais_Contribuinte']) > 5000){
                return "Informações Interesse contribuinte, em adicionais, ultrapassou 5000 caracteres, necessário diminuir seu tamanho. Este campo esta com " . strlen($mT276['T276_Informacoes_Adicionais_Contribuinte']) . " caracteres.";
            }
            if(strtotime($mT276['T276_Data_Inicio_Viagem']) < strtotime($mT276['T276_Data_Emissao'])){
                return "Data início viagem não pode ser anterior a data de emissão.";          
            }
            // Validação dos dados de percurso
            // A UF de descarregamento não pode estar no percurso e nem a UF de carregamento
            $ufPercurso = explode(",", $mT276['T276_UF_Percurso']);
            for($i=0 ; $i<count($ufPercurso); $i++){
                $UF = $ufPercurso[$i];
                log("UF PERCURSO: ".$ufPercurso[$i]);
                if(($UF == $mT276['carregamentoUF']) || ($UF == $mT276['descarregamentoUF'])){
                    return "UF de carregamento e descarregamento não devem estar inseridas na UF de percurso.";
                }        
            }
        

            // Verificar se foi inserido mais de um veículo de tração ou mais de 3 de Reboque
            $T283 = mysql_query("SELECT T283_Categoria_Veiculo, 
                                        COUNT(T283_Id) as total
                                   FROM T283
                                  WHERE T283_T276_Id='{$T276_Id}'
                               GROUP BY T283_Categoria_Veiculo");
            while($mT283 = mysql_fetch_array($T283)){
                if($mT283['T283_Categoria_Veiculo'] == 1 && $mT283['total'] > 3){
                    return "Podem ser cadastrados até 3 veículos de reboque no MDF-e.";
                }    
                if($mT283['T283_Categoria_Veiculo'] == 2 && $mT283['total'] != 1){
                    return "Deve ser cadastrado somente 1 veículo de tração no MDF-e.";
                }
            }
            
            // Validação dados da nota do MDFe
            $T284 = mysql_query("SELECT T284_Id,
                                        T284_Valor_Carga,
                                        T284_Peso_Bruto_Total,
                                        T284_Chave_De_Acesso_NFe,
                                        T284_Chave_De_Acesso_CTe,
                                        T284_Flag_Tipo_Documento,
                                        T284_Numero_NFe,
                                        T284_Peso_Bruto_Total,
                                        D018_Id,
                                        D018_UF
                                   FROM T284
                              LEFT JOIN T007 ON T007_Id=T284_T007_Id
                              LEFT JOIN D018 ON D018_Id=T284_D018_Id
                                  WHERE T284_T276_Id='{$T276_Id}'
                    ");
            $cErro = mysql_error();
            if (!empty($cErro)) {
                return 'Erro: ' . $cErro;
            }
            $resultNotasMDfe = '';
            $resultUFMDfe = '';
            if(mysql_num_rows($T284) <= 0){
                return "MDF-e não possui documentos(NF-e ou CT-e), não é possível finalizar.";
            }else{
                while($mT284 = mysql_fetch_array($T284)){

                    if($mT284['T284_Valor_Carga'] <= 0){
                        if($mT284['T284_Flag_Tipo_Documento'] == 1) {
                            $resultNotasMDfe .=" Valor do Conhecimento deve ser maior que zero.<br/>";
                        } elseif($mT284['T284_Flag_Tipo_Documento'] == 2) {
                            $resultNotasMDfe .=" Valor da Nota Fiscal ".$mT284['T284_Numero_NFe']." deve ser maior que zero.<br/>";
                        }
                    }
                    if($mT284['T284_Peso_Bruto_Total'] <= 0){
                        if($mT284['T284_Flag_Tipo_Documento'] == 1) {
                            $resultNotasMDfe .=" Peso do Conhecimento deve ser maior que zero.<br/>";
                        }elseif($mT284['T284_Flag_Tipo_Documento'] == 2) {
                            $resultNotasMDfe .=" Peso da Nota Fiscal ".$mT284['T284_Numero_NFe']." deve ser maior que zero.<br/>";
                        }
                    }

                    if($mT284['T284_Flag_Tipo_Documento'] == 2 && $mT284['T284_Chave_De_Acesso_NFe'] <= 0){
                        $resultNotasMDfe .=" Necessário preencher a chave de acesso da Nota Fiscal ".$mT284['T284_Numero_NFe'].".<br/>";
                    }elseif($mT284['T284_Flag_Tipo_Documento'] == 1 && $mT284['T284_Chave_De_Acesso_CTe'] <= 0) {
                        $resultNotasMDfe .=" Necessário preencher a chave de acesso do Conhecimento.<br/>";
                    }

                    if($mT284['D018_UF'] != $mT276['descarregamentoUF']){
                        if($mT284['T284_Flag_Tipo_Documento'] == 1) {
                            $resultNotasMDfe .= "UF ".$mT284['D018_UF']." do conhecimento, chave de acesso {$mT284['T284_Chave_De_Acesso_CTe']} é diferente da UF de descarregamento ".$mT276['descarregamentoUF']." do MDF-e <br/>";
                        } elseif($mT284['T284_Flag_Tipo_Documento'] == 2) {
                            $resultNotasMDfe .= "UF ".$mT284['D018_UF']." da nota  ".$mT284['T284_Numero_NFe']." é diferente da UF de descarregamento ".$mT276['descarregamentoUF']." do MDF-e <br/>";
                        }
                    }

                    // Documento NF-e
                    if ($mT284['T284_Flag_Tipo_Documento'] == 2) {
                        $notasMDFe = mysql_fetch_assoc(mysql_query("SELECT COUNT(T284_T276_Id) as total 
                                                                    FROM T276 
                                                                LEFT JOIN T284 ON T284_T276_Id=T276_Id  
                                                                    WHERE T284_Chave_De_Acesso_NFe='{$mT284['T284_Chave_De_Acesso_NFe']}' 
                                                                    AND T276_Flag_Cancelada='N'
                                                                GROUP BY T284_Chave_De_Acesso_NFe"));
                        if($notasMDFe['total'] > 1 ){
                            //return "A nota ".$mT284['T284_Numero_NFe']."  já está vinculada a outro MDF-e";
                        }
                        //Documento CT-e
                    } elseif($mT284['T284_Flag_Tipo_Documento'] == 1) {
                        $notasMDFe = mysql_fetch_assoc(mysql_query("SELECT COUNT(T284_T276_Id) as total 
                                                                    FROM T276 
                                                                LEFT JOIN T284 ON T284_T276_Id=T276_Id  
                                                                    WHERE T284_Chave_De_Acesso_CTe='{$mT284['T284_Chave_De_Acesso_CTe']}' 
                                                                    AND T276_Flag_Cancelada='N'
                                                                GROUP BY T284_Chave_De_Acesso_CTe"));
                        if($notasMDFe['total'] > 1 ){
                           //return "O conhecimento {$mT284['T284_Chave_De_Acesso_CTe']} já está vinculada a outro MDF-e";
                        }
                    }
 
                    if(!empty($resultNotasMDfe)){
                        $resultNotasMDfe .= "<br/>";
                    }
                }
            }
            if(!empty($resultNotasMDfe)){
                return $resultNotasMDfe;
            }

            // Insere o Numero do mdfe
            if(empty($mT276['T276_Numero_MDFe'])){
                $numeroMDFe = mysql_fetch_array(mysql_query("SELECT T276_Numero_MDFe FROM T276 ORDER BY ROUND(T276_Numero_MDFe) DESC LIMIT 0,1"));
                $proximoNumero = $numeroMDFe['T276_Numero_MDFe'];
                $proximoNumero++; 

                mysql_query("UPDATE T276 SET T276_Numero_MDFe='{$proximoNumero}' WHERE T276_id='{$T276_Id}'");
                $erro = mysql_error();
                if ($erro) {
                    mysql_query("ROLLBACK");
                    return $erro;
                }
            }
                mysql_query("UPDATE T276 SET T276_Flag_Travar_MDFe='S' WHERE T276_id='{$T276_Id}'"); 
                $erro = mysql_error();
                if ($erro) {
                    mysql_query("ROLLBACK");
                    return $erro;
                }
            return true;
        }
}
