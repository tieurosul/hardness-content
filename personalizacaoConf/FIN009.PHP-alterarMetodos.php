<?php
namespace hardness;
/**
* Alterar (sobre-escrever) Métodos de uma Classe
*/

class FIN009 extends FIN009_ {
	// defina os métodos para sobreescrever

    /**
     * FIN009RecalculaProjecao
     *
     * @param String $D014_Id
     * @return String/bool
     */ 
    public function FIN009RecalculaProjecao($D014_Id = null, $periodo = null, $limit = null, $mes = null)
    {
        global $g;

		log("entrou aqui FIN009RecalculaProjecao");

        if (!empty($mes)){
            $Mes       = $mes;
            $Mes_Final = $Mes;   
        }else{
            $Mes       = '01';
            $Mes_Final = '12';
        }
        $Ano_Atual = date("Y");
        $Ano       = $Ano_Atual - 1;
        $Ano_Final = $Ano + 2;

        if($periodo != null && is_array($periodo)){
            $Mes       = $periodo['mes'];
            $Mes_Final = $periodo['mes_final'];
            $Ano_Atual = $periodo['ano_atual'];
            $Ano       = $periodo['ano'];
            $Ano_Final = $periodo['ano_final'];
        }
        set_time_limit(0);
        
        if (!empty($D014_Id)) {
            $extraD014 = "WHERE D014_Id='{$D014_Id}'";
        } else {
            $extraD014 = "";
        }
        
        for ($countAno = $Ano; $countAno <= $Ano_Final; $countAno++) {
            for ($countMes = $Mes; $countMes <= $Mes_Final; $countMes++) {
                $mesUsar = str_pad($countMes, 2, "0", STR_PAD_LEFT);
                
                //echo "ANO->".$countAno." MES->".$mesUsar."<br />";
                mysql_query("SET AUTOCOMMIT=0");
                mysql_query("START TRANSACTION");
                
                $D014 = mysql_query("SELECT D014_Id,
                                            D014_Grupo,
                                            D014_SubConta,
                                            D014_Flag_Tipo 
                                       FROM D014 {$extraD014} {$limit}");
                $erro = mysql_error();
                if (!$D014) {
                    mysql_query("ROLLBACK");
                    return "Erro 1: " . $erro;
                }
                while ($mD014 = mysql_fetch_array($D014)) {
					log("vai processar qual ano : ".$countAno);
					log("vai processar qual mesUsar : ".$mesUsar);
                    $T018 = <<<EOT
						SELECT T018_Id,
                               T018_Flag_Confirmado,
                               T018_Valor
						  FROM T018 
						 WHERE T018_D014_Id='{$mD014['D014_Id']}'
						   AND T018_Ano='{$countAno}'
						   AND T018_Mes='{$mesUsar}'
						   AND T018_C004_Id='{$g['empresaAtual']}'
EOT;
                    $resultT018 = mysql_query($T018);
                    $erro       = mysql_error();
                    if (!$resultT018) {
                        mysql_query("ROLLBACK");
                        return "Erro 2: " . $erro;
                    }
                    $mT018 = mysql_fetch_array($resultT018);
                    
                    if ($mT018['T018_Id'] > 0) {
                        if ($mT018['T018_Flag_Confirmado'] != "S") {
                            $T018_new = <<<EOT
								SELECT T018_Valor 
                                  FROM T018
								 WHERE T018_D014_Id='{$mD014['D014_Id']}'
                                   AND concat(T018_Ano,T018_Mes)<concat('{$countAno}','{$mesUsar}')
								   AND T018_Flag_Confirmado='S'
								   AND T018_C004_Id='{$g['empresaAtual']}'
                              ORDER BY T018_Ano DESC, T018_Mes DESC    
                                 LIMIT 1
EOT;
                            $resultT018_new = mysql_query($T018_new);
                            $erro           = mysql_error();
                            if (!$resultT018_new) {
                                mysql_query("ROLLBACK");
                                return "Erro 3: " . $erro;
                            }
                            $mT018_new = mysql_fetch_array($resultT018_new);
                            
                            $upT018   = "UPDATE T018 SET T018_Valor='{$mT018_new['T018_Valor']}' WHERE T018_Id='{$mT018['T018_Id']}'";
                            $resultUp = mysql_query($upT018);
                            $erro     = mysql_error();
                            if (!$resultUp) {
                                mysql_query("ROLLBACK");
                                return "Erro 4: " . $erro;
                            }
                        }
                    } else {
                        
                        $sql  = mysqli_query("INSERT INTO T018 
                                                         (T018_Mes,
                                                          T018_Ano,
                                                          T018_D014_Id,
                                                          T018_Valor,
                                                          T018_C004_Id)
                                                    VALUES
                                                        ('{$mesUsar}',
                                                         '{$countAno}',
                                                         '{$mD014['D014_Id']}',
                                                         '{$mT018['T018_Valor']}',
                                                         '{$g['empresaAtual']}')");
                        $erro = mysql_error();
                        if (!$sql) {
                            mysql_query("ROLLBACK");
                            return "Erro 5: " . $erro;
                        }
                        
                        $mT018['T018_Id'] = $g['mysqlLastId'];
                    }
                    
                    $sql  = mysqli_query("UPDATE T015 SET T015_T018_Id='{$mT018['T018_Id']}'
                                           WHERE T015_D014_Id='{$mD014['D014_Id']}'
                                             AND T015_T018_Id is null 
                                             AND T015_C004_Id='{$g['empresaAtual']}'
                                             AND if(T015_Data_Pagamento='0000-00-00',T015_Data_Vencimento,T015_Data_Pagamento) LIKE '$countAno-$mesUsar%'");
                    $erro = mysql_error();
                    if (!$sql) {
                        mysql_query("ROLLBACK");
                        return "Erro 6: " . $erro;
                    }
                    
                    $sql  = mysqli_query("UPDATE T002 SET T002_T018_Id='{$mT018['T018_Id']}'
                                           WHERE T002_D014_Id='{$mD014['D014_Id']}'
                                             AND T002_T018_Id is null 
                                             AND if(T002_Data_Recebimento='0000-00-00',T002_Data_Vencimento,T002_Data_Recebimento) LIKE '$countAno-$mesUsar%'
                                             AND T002_C004_Id='{$g['empresaAtual']}'");
                    $erro = mysql_error();
                    if (!$sql) {
                        mysql_query("ROLLBACK");
                        return "Erro 7: " . $erro;
                    }
                    
                    $sql  = mysqli_query("UPDATE T019 SET T019_T018_Id='{$mT018['T018_Id']}'
                                           WHERE T019_D014_Id='{$mD014['D014_Id']}'
                                             AND T019_T018_Id is null 
                                             AND T019_Data_Lancamento LIKE '$countAno-$mesUsar%'
                                             AND T019_C004_Id='{$g['empresaAtual']}'");
                    $erro = mysql_error();
                    if (!$sql) {
                        mysql_query("ROLLBACK");
                        return "Erro 8: " . $erro;
                    }
                    
                    if ($mD014['D014_Flag_Tipo'] == 'PG') {
                        $sql  = mysql_query("UPDATE T018 SET T018_Valor_Receber=ifnull(T018_lancado('{$g['empresaAtual']}',{$mD014['D014_Id']},'{$mesUsar}','{$countAno}','E'),0),
						                            T018_Valor_Pagar=ifnull(T018_lancado('{$g['empresaAtual']}',{$mD014['D014_Id']},'{$mesUsar}','{$countAno}','S'),0),
						                            T018_Valor_Recebido=ifnull(T018_lancado_banco('{$g['empresaAtual']}',{$mD014['D014_Id']},'{$mesUsar}','{$countAno}','E'),0),
						                            T018_Valor_Pago=abs(ifnull(T018_lancado_banco('{$g['empresaAtual']}',{$mD014['D014_Id']},'{$mesUsar}','{$countAno}','S'),0)),
						                            T018_Valor_Saldo=T018_Valor-(T018_Valor_Pagar+T018_Valor_Pago)+(T018_Valor_Recebido+T018_Valor_Receber)
						                      WHERE T018_Id='{$mT018['T018_Id']}'");
                        $erro = mysql_error();
                        if (!$sql) {
                            mysql_query("ROLLBACK");
                            return "Erro 9: " . $erro;
                        }
                    } else {
                        $sql  = mysql_query("UPDATE T018 SET T018_Valor_Receber=ifnull(T018_lancado('{$g['empresaAtual']}',{$mD014['D014_Id']},'{$mesUsar}','{$countAno}','E'),0),
						                            T018_Valor_Pagar=ifnull(T018_lancado('{$g['empresaAtual']}',{$mD014['D014_Id']},'{$mesUsar}','{$countAno}','S'),0),
						                            T018_Valor_Recebido=ifnull(T018_lancado_banco('{$g['empresaAtual']}',{$mD014['D014_Id']},'{$mesUsar}','{$countAno}','E'),0),
						                            T018_Valor_Pago=abs(ifnull(T018_lancado_banco('{$g['empresaAtual']}',{$mD014['D014_Id']},'{$mesUsar}','{$countAno}','S'),0)),
						                            T018_Valor_Saldo=T018_Valor+(T018_Valor_Pagar+T018_Valor_Pago)-(T018_Valor_Recebido+T018_Valor_Receber)
						                      WHERE T018_Id='{$mT018['T018_Id']}'");
                        $erro = mysql_error();
                        if (!$sql) {
                            mysql_query("ROLLBACK");
                            return "Erro 10: " . $erro;
                        }
                    }
                }

                mysql_query("COMMIT");
                mysql_query("SET AUTOCOMMIT=1");                
            }
        }

        return true;
    }

}
