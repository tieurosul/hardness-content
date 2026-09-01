<?php
namespace hardness;
/**
* Alterar (sobre-escrever) Métodos de uma Classe
*/

class FIN007 extends FIN007_ {
	// defina os métodos para sobreescrever

	public function triggerAposLiberarAnaliseCredito($T005_Id) {
        $updt = "UPDATE T005A SET T005A_Data_Hora_Aprovacao_Credito = CURRENT_TIMESTAMP() WHERE T005A_T005_Id = '{$T005_Id}'";
        log("Atualiza Data Hora Analise Credito {$updt}");
        mysqli_query($updt);
        
        $sqlT006 = <<<SQL
            SELECT T006_Id, T006_D009_Id, T006_Quantidade FROM T006 WHERE T006_T005_Id = '{$T005_Id}'
        SQL;
        $resT006 = mysql_query($sqlT006);

        if ($erro = mysql_error()) {
            log("T005 updateAfter: erro ao consultar T006 do pedido {$T005_Id}: {$erro}");
        } else {
            require_once('bibliotecas/classes/CAD002.php');
            $CAD002 = new CAD002();

            while ($T006 = mysql_fetch_assoc($resT006)) {
                $sqlT238 = <<<SQL
                    SELECT T238_Id FROM T238 WHERE T238_T006_Id = '{$T006['T006_Id']}' LIMIT 1
                SQL;
                $T238 = mysql_fetch_assoc(mysql_query($sqlT238));

                // Não refaz separação
                if (!empty($T238['T238_Id'])) {
                    continue;
                }
                
                log("Vai fazer a separação para {$T006['T006_Id']}");
                $retorno = $CAD002->preencherLocacoes($T006['T006_D009_Id'], $T005_Id, $T006['T006_Id'], $T006['T006_Quantidade']);
                log("retorno preencherLocacoes(): " . $retorno);
            }
        }

        return true;
    }
}


