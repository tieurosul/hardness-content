<?php
namespace hardness;
/**
* Alterar (sobre-escrever) Métodos de uma Classe
*/

class VEN003 extends VEN003_ {
	// defina os métodos para sobreescrever

        /**
         * imprimirNFeMarcada
         *
         * @return true
         */        
        public function imprimirNFeMarcada($extra = false, $sql = false)
        {
            global $g;
            if ($sql == false) {
                $sql = "SELECT T007_Id,T007_Numero_Protocolo_Nfe,T007_Data_Emissao, if(T007_Flag_ACP!='3',LPAD(T007_Numero_Nota_Fiscal,7,'0'),T007_Numero_Nota_Fiscal) as NFe FROM T007 " . $extra;
            } 
            $sql = mysql_query($sql);

            while ($res = mysql_fetch_array($sql)) {
                if (!empty($res['T007_Numero_Protocolo_Nfe'])) {
                    $AnoMesNFe  = substr($res['T007_Data_Emissao'], 0, 7);
                    $arquivoXML = $g['pathDadosAntigo'] . 'nfe/xml/autorizado/' . $AnoMesNFe . '/' . $res['T007_Numero_Protocolo_Nfe'] . '.xml';
                    if (!file_exists($arquivoXML)) {
                        $arquivoXML = $g['pathDadosAntigo'] . 'nfe/xml/autorizado/' . $res['T007_Numero_Protocolo_Nfe'] . '.xml';
                    }
                    $this->gerarDanfe($res['T007_Id'], $arquivoXML, true);
                }
            }
            return true;
        }

        /**
         * verificaCancelarNF
         *
         * @param String $T007_Id
         * @return String/bool
         */
        public function verificaCancelarNF($T007_Id)
        {
            global $g;
            
            $sqlT007 = mysql_query("SELECT * FROM T007 WHERE T007_Id = '{$T007_Id}'");
            $mT007   = mysql_fetch_array($sqlT007);
            
            $cSql_T007 = mysql_query("SELECT T007_Id FROM T007 WHERE T007_Id='{$T007_Id}' AND T007_Flag_Cancelada='S'");
            if (mysql_num_rows($cSql_T007) > 0) {
                return "Essa nota fiscal já foi cancelada";
            }
            
            $cSql_T007 = mysql_query("SELECT T007_Id FROM T007 WHERE T007_Id='{$T007_Id}' AND T007_Flag_Impresso!='S'");
            if (mysql_num_rows($cSql_T007) > 0) {
                //return "Essa nota fiscal nao foi impressa ainda";
            }
            
            $cSql_T002 = mysql_query("SELECT T002_Id FROM T002 WHERE T002_T007_Id='{$T007_Id}' AND T002_Data_Recebimento!='0000-00-00' AND (T002_Valor_Duplicata>0 || T002_Valor_Total>0)");
            if (mysql_num_rows($cSql_T002) > 0) {
                return "Existem títulos a receber baixados!";
            }
            
            $cSql_T002 = mysql_query("SELECT T002_Id FROM T002 WHERE T002_T007_Id='{$T007_Id}' AND T002_T035_Id>0");
            if (mysql_num_rows($cSql_T002) > 0) {
                return "Existem títulos que já foram enviados para o banco!";
            }

            // Validação comentada em 21/11/2019 devido ao erro apresentado na empresa fane, eles haviam reparcelado os titulos e depois excluido eles e não estavam conseguindo cancelar a nota fiscal
            /*$cSql_T002 = mysql_query("SELECT T002_Id FROM T002 WHERE T002_T007_Id='{$T007_Id}' AND T002_Flag_Cancelada = 'S'");
            if (mysql_num_rows($cSql_T002) > 0) {
                return "Existem títulos a receber cancelados! Verifique se foram agrupados!";
            }*/
            
            $cSql_T015 = mysql_query("SELECT T015_Id FROM T015 WHERE T015_T007_Id='{$T007_Id}' AND T015_Data_Pagamento!='0000-00-00'");
            if (mysql_num_rows($cSql_T015) > 0) {
                return "Existem títulos a pagar baixados!";
            }
            
            $cSql_T008 = mysql_query("SELECT T008_Id FROM T008 LEFT JOIN T007 ON T007_Id=T008_T007_Id WHERE T008_T007_Id='{$T007_Id}'");
            while ($mT008 = mysql_fetch_array($cSql_T008)) {
                $cSql = mysql_query("SELECT T008_Id,T007_Numero_Nota_Fiscal FROM T008 LEFT JOIN T007 ON T007_Id=T008_T007_Id WHERE T008_T008_Id='{$mT008['T008_Id']}' AND T007_Flag_Cancelada!='S' AND T007_Flag_Finalidade_NFe != '1'");
                if (mysql_num_rows($cSql) > 0) {
                    $notas = array();
                    while($mNotas = mysql_fetch_array($cSql)){
                        $notas[] = $mNotas['T007_Numero_Nota_Fiscal'];
                    }
                    $numeroNotas = implode($notas,", ");
                    return "Existem notas emitidas relacionadas a esta nota:<br><br>{$numeroNotas}";
                }
            }
            
            $cSql_T008 = mysql_query("SELECT T008_Id FROM T008 LEFT JOIN T007 ON T007_Id=T008_T007_Id WHERE T008_T007_Id='{$T007_Id}'");
            while ($mT008 = mysql_fetch_array($cSql_T008)) {
                $cSql = mysql_query("SELECT T006_Id FROM T006 LEFT JOIN T005 ON T005_Id=T006_T005_Id WHERE T006_T008_Id='{$mT008['T008_Id']}' AND T005_Flag_Status!='8' AND T005_Flag_Status!='5' AND T005_Flag_Status!='4'");
                if (mysql_num_rows($cSql) > 0) {
                    return "Existem pedidos relacionados a esta nota!";
                }
            }
            
            $cSql_T008 = mysql_query("SELECT T008_Id FROM T008 LEFT JOIN T007 ON T007_Id=T008_T007_Id WHERE T008_T007_Id='{$T007_Id}'");
            while ($mT008 = mysql_fetch_array($cSql_T008)) {
                $cSql = mysql_query("SELECT T014_Id, T013_Numero_Nota_Fiscal FROM T014 LEFT JOIN T013 ON T013_Id=T014_T013_Id WHERE T014_T008_Id='{$mT008['T008_Id']}' AND T013_Flag_Cancelada!='S'");
                if (mysql_num_rows($cSql) > 0) {
                    $notas = array();
                    while($mNotas = mysql_fetch_array($cSql)){
                        $notas[] = $mNotas['T013_Numero_Nota_Fiscal'];
                    }
                    $numeroNotas = implode(", ", array_unique($notas));
                    return "Não é possível cancelar esta NF, pois já existe NF recebida vinculada a esta devolução que não está cancelada. Verifique se a NF recebida deve ser cancelada antes de prosseguir.<br><br>Notas recebidas: {$numeroNotas}";
                }
            }
            
            //-- VERIFICA SE O USUARIO TEM ACESSO A OPCAO DE CANCELAR PEDIDO PENDENTE
            $sqlC030 = mysql_query("SELECT * FROM C030 WHERE C030_C021_Id = '{$g['c021']['C021_Id']}' AND c030_C029_Id = 169 AND C030_Acesso_Liberado = 'S'");
            if (mysql_num_rows($sqlC030) == 0) {
                return "Acesso não disponível para o perfil {$g['c021']['C021_Nome_Perfil']}!";
            }
            
            //Regra comentada, o prazo varia de UF para UF
            //if ($mT007['T007_Flag_ACP'] == 6) {
            //    $cSql_T007_EXP = mysql_query("SELECT timediff(now(),T007_Data_Hora_Protocolo_Nfe) FROM T007 WHERE T007_Id='$T007_Id'");
            //    $mT007_EXP     = mysql_fetch_array($cSql_T007_EXP);
            //    $Horas_Emissao = str_replace(":", "", $mT007_EXP[0]);
            //    if ($Horas_Emissao >= 1680000) {
            //        return "Nota foi emitida a mais de 168 horas. A Sefaz não aceita cancelamentos após esse prazo";
            //    }
            //}
            return true;
        }


}







