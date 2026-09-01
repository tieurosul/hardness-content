<?php
namespace hardness;
/**
* Alterar (sobre-escrever) Métodos de uma Classe
*/

class EST002 extends EST002_ {
    // defina os métodos para sobreescrever

    public function triggerAposImpressaoPedido($T005_Id) {
		global $g;

		$updt = "UPDATE T005A SET T005A_Data_Hora_Etapa_Expedicao = CURRENT_TIMESTAMP() WHERE T005A_T005_Id = '{$T005_Id}'";
		log("Atualiza Data Hora Etapas Expedição {$updt}");
		mysqli_query($updt);
        
        return true;
    }

    public function excluirRomaneio($T005_Id, $romaneioRmv) {
		global $g;

		$t005 = mysql_fetch_assoc(mysqli_query("SELECT T005_T275_Id FROM T005 WHERE T005_Id = '{$T005_Id}'"));
		if ($t005['T005_T275_Id'] != $romaneioRmv) {
			return "Romaneio <b>{$romaneioRmv}</b> não é o correto do pedido <b>{$T005_Id}</b>!";
		}

		mysql_query("START TRANSACTION");

		$updt = "UPDATE T005 SET T005_T275_Id = '' WHERE T005_Id = '{$T005_Id}'";
		if (!mysql_query($updt)) {
			mysql_query("ROLLBACK");
			return "Erro ao desvincular romaneio <b>{$romaneioRmv}</b> do pedido <b>{$T005_Id}</b>:<br><br>" . mysql_error();
		}

		$del = "DELETE FROM T275 WHERE T275_Id = '{$romaneioRmv}'";
		if (!mysql_query($del)) {
			mysql_query("ROLLBACK");
			return "Erro ao deletar romaneio <b>{$romaneioRmv}</b> do pedido <b>{$T005_Id}</b>:<br><br>" . mysql_error();
		}
        
		mysql_query("COMMIT");
        return true;
    }

    /**
	* SeparacaoPedido
     *
     * @param Form $form
     * @return true/string
     */
    public function SeparacaoPedido($form)
    {
        global $g;
        $T005_Id          = $form->campoValorEnviado('T005_Id');
        $T005_Estoque_Box = $form->campoValorEnviado('T005_Estoque_Box');

        $T006A = mysql_query("SELECT T006A_Id 
                                FROM T006A
                           LEFT JOIN T006 ON T006_Id = T006A_T006_Id
                           LEFT JOIN D009 ON D009_Id = T006_D009_Id
                           LEFT JOIN D049 ON D049_Id = D009_D049_Id
                           LEFT JOIN D001 ON D001_Id = D049_D001_Id
                           LEFT JOIN T303 ON D001_Id = T303_D001_Id
                           LEFT JOIN T140 ON T140_Id = T303_T140_Id OR T140_D001_Id = D001_Id
                               WHERE T006_T005_Id = '{$T005_Id}'
                                 AND T006A_C007_Id_Impressao_Certificado = '0'
                                 AND T140_Id IS NOT NULL");
                                 
        if(mysql_num_rows($T006A) > 0){
            //return "Possui itens que o certificado não foi impresso, favor imprimir para dar sequencia";
        }
        
        $T006_Restricao = mysql_query("SELECT T006_Codigo_Produto,
                                              T006_Quantidade,
                                              T006_Quantidade_Separacao 
                                         FROM T006 
                                    LEFT JOIN T005 ON T005_Id=T006_T005_Id 
                                        WHERE T006_T005_Id='{$T005_Id}' AND (T006_Quantidade<T006_Quantidade_Separacao) 
                                          AND IF(IFNULL(T006_C004_Id_Estoque,0) > 0, T006_C004_Id_Estoque, T005_C004_Id) = T005_C004_Id");
        if (mysql_num_rows($T006_Restricao) > 0) {
            $mensagem = '';
            while($mT006 = mysql_fetch_array($T006_Restricao)){
                $mT006['T006_Quantidade']           = gCorrigeNumero($mT006['T006_Quantidade']);
                $mT006['T006_Quantidade_Separacao'] = gCorrigeNumero($mT006['T006_Quantidade_Separacao']);
                $mensagem .= "<b>Código {$mT006['T006_Codigo_Produto']}</b> - Quantidade: {$mT006['T006_Quantidade']} / Separado: {$mT006['T006_Quantidade_Separacao']}<br>";
            }
            return "Itens abaixo com quantidade separada maior que a quantidade do pedido, necessário excluir e refazer a separação<br><br>".$mensagem;
        }

        $T006  = mysql_query("SELECT (sum(T006_Quantidade_Separacao)/sum(T006_Quantidade)*100) FROM T006 LEFT JOIN T005 ON T005_Id=T006_T005_Id WHERE T006_T005_Id='$T005_Id' AND IF(IFNULL(T006_C004_Id_Estoque,0) > 0, T006_C004_Id_Estoque, T005_C004_Id) = T005_C004_Id");
        $mT006 = mysql_fetch_array($T006);
        
        if ($mT006[0] < 100 && empty($T005_Estoque_Box)) {
            return "Este pedido está apenas " . number_format($mT006[0], 2, ',', '.') . "% separado, você deverá informar um BOX";
        }
        if ($mT006[0] < 100 && !empty($T005_Estoque_Box)) {
            $sql = <<<EOT
				UPDATE
					T006
				SET 
					T006_Flag_Divergencia='S'
				WHERE
					T006_T005_Id='{$T005_Id}'
					AND (
						T006_Quantidade>T006_Quantidade_Separacao 
						OR T006_Quantidade_Separacao is null
					)
EOT;
            mysql_query($sql);

            mysql_query("update T005 set T005_Estoque_Box='{$T005_Estoque_Box}' where T005_Id='{$T005_Id}'");
            
            return "Este pedido está apenas " . number_format($mT006[0], 2, ',', '.') . "% separado, resolva as divergências";
        }
        
        
        /*
        // Comentado porque está errado - deve ser verificado apenas as locação na T238 
        // Não é mais necessário, porque não tem mais como adicionar uma locação em branco
        $sqlD004 = mysql_query("SELECT T066_Id FROM T006 LEFT JOIN T066 ON T066_D009_Id=T006_D009_Id WHERE T006_T005_Id='{$T005_Id}' and ifnull(T066_D004_Id,0)=0");
        if(mysql_num_rows($sqlD004) > 0) {
            return "Não foi informado a LOCAÇÃO de todos os produtos.<br />VERIFIQUE!";
        }
        */
        // VERIFICANDO
        if($g['C031']['desabilitarConferencia']=='S'){
            $T005_Flag_Status='3';
        } else {
            $T005_Flag_Status='10';
        }

        mysql_query("update T005 set T005_Flag_Status='{$T005_Flag_Status}', T005_Nome_Status = T005_Status_Pedido (T005_Flag_Status, 1), T005_Estoque_Box='{$T005_Estoque_Box}' where T005_Id='{$T005_Id}'");
        mysql_query("update T006 set T006_Flag_Divergencia='N' where T006_T005_Id='{$T005_Id}'");
        // Insere registros no histórico do pedido (T178)
        mysql_query("insert into T178(T178_T005_Id,T178_C007_Id,T178_Data,T178_Descricao,T178_Observacao) values ({$T005_Id},{$g['usuarioAtual']},CURRENT_TIMESTAMP(),'Enviou para conferência','')");
        
        return true;
        
    }
}












