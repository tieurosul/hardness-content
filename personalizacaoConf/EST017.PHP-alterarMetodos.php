<?php
namespace hardness;
/**
* Alterar (sobre-escrever) Métodos de uma Classe
*/

class EST017 extends EST017_ {
	// defina os métodos para sobreescrever

    public function processarProdutosBaixa($T255_Id, $transacao = true) {
        global $g;
        $paramlocacaoPadraoProducao = $g['C031']['locacaoPadraoProducao'];

        require_once("bibliotecas/classes/CAD002.php");
        $CAD002 = new CAD002();

        if ($transacao) {
            mysql_query("SET AUTOCOMMIT=0");
            mysql_query("START TRANSACTION");
        }

        mysql_query("DELETE FROM T264 WHERE T264_T255_Id = '{$T255_Id}'");
        $erro = mysql_error();
        if(!empty($erro)) {
            if ($transacao) {
                mysql_query("ROLLBACK");
            }
            return "Erro ao processar produtos, por favor entre em contato com o suporte técnico.";
        }

        $sql = mysql_query("SELECT D009_Id, Quantidade FROM
                                (SELECT D009_Id, 
                                        SUM(T261_Quantidade*T256_Quantidade) AS Quantidade 
                                   FROM T261 
                              LEFT JOIN T256 ON T256_Id = T261_T256_Id
                              LEFT JOIN D001 ON D001_Id = T261_D001_Id_Composicao 
                              LEFT JOIN D049 ON D001_Id = D049_D001_Id
                              LEFT JOIN D009 ON D049_Id = D009_D049_Id
                                  WHERE T256_T255_Id = '{$T255_Id}'
                                    AND D009_C004_Id='{$g['empresaAtual']}'
                               GROUP BY D009_Id
                            UNION ALL
                                SELECT T263_D009_Id AS D009_Id, SUM(T263_Quantidade*T256_Quantidade) AS Quantidade FROM T263 LEFT JOIN T262 ON T262_Id=T263_T262_Id LEFT JOIN T256 ON T256_Id=T262_T256_Id WHERE T256_T255_Id = '{$T255_Id}' GROUP BY D009_Id) Produtos
                            GROUP BY D009_Id");
        while ($row = mysql_fetch_assoc($sql)) {
            $T264_T066_Id = null;
            $locacoesPrioridade = $CAD002->getLocacoesPrioridadeFIFO($row['D009_Id']);

            if (!empty($paramlocacaoPadraoProducao)) {
                // Entre as locações padrão, mantém a primeira prioridade FIFO.
                foreach ($locacoesPrioridade as $locacao) {
                    if ($locacao['D004_Id'] == $paramlocacaoPadraoProducao) {
                        $T264_T066_Id = $locacao['T066_Id'];
                        break;
                    }
                }
            } else {
                // Sem locação padrão configurada, utiliza a primeira prioridade FIFO.
                $locacao = reset($locacoesPrioridade);
                if ($locacao !== false) {
                    $T264_T066_Id = $locacao['T066_Id'];
                }
            }

            $sqlInsert = "INSERT INTO T264 (
                T264_T255_Id,
                T264_D009_Id,
                T264_T066_Id,
                T264_Quantidade)
              VALUES (
                '{$T255_Id}',
                '{$row['D009_Id']}',
                '{$T264_T066_Id}',
                '{$row['Quantidade']}')";

            mysql_query($sqlInsert);
            $erro = mysql_error();
            if(!empty($erro)){
                mysql_query("ROLLBACK");
                return "Erro ao processar produtos, por favor entre em contato com o suporte técnico.";
            }
        }

        if ($transacao) {
            mysql_query("COMMIT");
        }

        return true;
    }

	    /**
     * processarEntrada
     *
     * @param string $extra
     * @return true/string
     */
    public function processarEntrada($extra)
    {
        global $g;
        require_once("bibliotecas/classes/CAD002.php");
        $CAD002 = new CAD002();

        $sql = mysql_query("SELECT T256_Id, 
                                   T257_Id, 
                                   T257_Quantidade, 
                                   T257_T066_Id, 
                                   T256_D009_Id, 
                                   T256_T255_Id, 
                                   D001_Id, 
                                   D001_Codigo_Produto, 
                                   D001_Descricao_Produto, 
                                   T005_Id 
                                   FROM T256 
                                   {$extra}"); 
        $erro = mysql_error();
        if(!empty($erro)){
            return "Erro ao verificar entradas, por favor entre em contato com o suporte técnico." .$erro;
        }

        if(mysql_num_rows($sql) <= 0){
            return "Nenhuma entrada de produção pendente foi encontrada, por favor verifique! ";
        }

        //Verifica entradas sem locação
        $entradasSemLocacao = '';
        while($row = mysql_fetch_array($sql)){
            if(empty($row['T257_T066_Id'])){
                $entradasSemLocacao = "{$row['D001_Codigo_Produto']} - {$row['D001_Descricao_Produto']}<br>";
            }
        }
        if(!empty($entradasSemLocacao)){
            return "Atenção<br>As entradas abaixo estãos em locação selecionada, por favor verifique.<br><br>$entradasSemLocacao";
        }
        mysql_data_seek($sql, 0);

        mysql_query("SET AUTOCOMMIT=0");
        mysql_query("START TRANSACTION");

        $pedidosAtualizados = array();
        while($row = mysql_fetch_array($sql)){
            
            $D004_Id = getD004($row['T257_T066_Id']);
            $T066_Id = $row['T257_T066_Id'];
            
            $locacao = mysql_query("SELECT T066_Quantidade_Estoque
                                      FROM T066
                                     WHERE T066_Id = '{$T066_Id}'");
            
            $mLocacao = mysql_fetch_array($locacao);
            $numeroLote = date("YmdHi") . rand(1000, 9999);

            if ($mLocacao['T066_Quantidade_Estoque'] > 0) {
                //insere nova locação no produto para lançar o lote
                mysql_query("INSERT INTO T066 (T066_D009_Id, T066_D004_Id) VALUES ('{$row['T256_D009_Id']}', '{$D004_Id}')");
                $T066_Id    = $g['mysqlLastId'];                    
                mysql_query("INSERT INTO T066A (T066A_T066_Id, T066A_Numero_Lote) VALUES ('{$T066_Id}','{$numeroLote}')");
                mysqli_query("UPDATE T257 SET T257_T066_Id = '{$T066_Id}' WHERE T257_Id = '{$row['T257_Id']}'");
            } else {
                $T066A = mysql_query("SELECT T066A_Id FROM T066A WHERE T066A_T066_Id = '{$T066_Id}'");
                if(mysql_num_rows($T066A) > 0){
                    mysqli_query("UPDATE T066A SET T066A_Numero_Lote = '{$numeroLote}' WHERE T066A_T066_Id = '{$T066_Id}'");
                } else {
                    mysqli_query("INSERT INTO T066A (T066A_T066_Id,T066A_Numero_Lote) VALUES ('{$T066_Id}','{$numeroLote}' )");
                }
            }

            //Custo total da Composição
            $composicao = "SELECT SUM(D009_Valor_Custo_Unitario * D074_Quantidade) as Custo
                             FROM D074
                        LEFT JOIN D001 ON D001_Id = D074_D001_Id_Composicao
                        LEFT JOIN D049 ON D001_Id = D049_D001_Id
                        LEFT JOIN D009 ON D049_Id = D009_D049_Id
                        LEFT JOIN D037 ON D037_Id = D001_D037_Id
                            WHERE D074_D001_Id = '{$row['D001_Id']}'
                              AND D009_C004_Id='{$g['empresaAtual']}'";
            $rComposicao = mysql_query($composicao);
            $composicao = mysql_fetch_array($rComposicao);
            $composicao = $composicao['Custo'];

            //Custo total e tempo dos processos
            $processo = "SELECT SUM(D075_Lead_Time) as Tempo, SUM(D075_Lead_Time * (D079_Custo_Minuto + D078_Custo_Minuto)) as Custo
                            FROM D075
                            LEFT JOIN D078 on D078_Id = D075_D078_Id
                            LEFT JOIN D079 on D079_Id = D075_D079_Id
                            WHERE D075_D001_Id = '{$row['D001_Id']}'";
            $rProcesso = mysql_query($processo);
            $processo = mysql_fetch_array($rProcesso);
            $custoProcesso = $processo['Custo'];
            $tempoProducao = $processo['Tempo'];

            //Custo total do material de consumo utilizado nos processos
            $consumo = "SELECT SUM(D076_Quantidade * D009_Valor_Custo_Unitario) as Custo
                            FROM D076
                            LEFT JOIN D009 ON D009_Id = D076_D009_Id
                            LEFT JOIN D001 ON D001_Id = D009_D001_Id
                            LEFT JOIN D037 ON D037_Id = D001_D037_Id
                            LEFT JOIN D075 ON D075_Id = D076_D075_Id
                            WHERE D075_D001_Id = '{$row['D001_Id']}'";
            $rConsumo = mysql_query($consumo);
            $consumo = mysql_fetch_array($rConsumo);
            $consumo = $consumo['Custo'];
            //Custo final
            $custo_final = $composicao + $custoProcesso + $consumo;

            $insert = <<<EOT
                INSERT INTO T001 (
                            T001_D009_Id,
                            T001_C004_Id,
                            T001_T257_Id,
                            T001_T066_Id,
                            T001_D004_Id,
                            T001_Quantidade,
                            T001_Flag_Operacao,
                            T001_Data_Lancamento,
                            T001_Descricao_Operacao,
                            T001_Flag_Atualiza_Custo,
                            T001_Valor_Custo_Unitario,
                            T001_Valor_Preco_Unitario
                        ) VALUES (
                            '{$row['T256_D009_Id']}',
                            '{$g['empresaAtual']}',
                            '{$row['T257_Id']}',
                            '{$T066_Id}',
                            '{$D004_Id}',
                            '{$row['T257_Quantidade']}',
                            'E',
                            current_date(),
                            'PRODUÇÃO-{$row['T256_T255_Id']}',
                            'S',
                            '{$custo_final}',
                            '{$custo_final}'
                        )
EOT;
            mysql_query($insert);
            $erro = mysql_error();
            if(!empty($erro)){
                mysql_query("ROLLBACK");
                return "Erro ao realizar entrada de produção, por favor entre em contato com o suporte técnico." . $erro;
            }
            $CAD002->D001_reprocessa_historico(0, $row['T256_D009_Id']);

            //Update para invocar a trigger after update no T256
            mysql_query("UPDATE T257 SET T257_Id=T257_Id WHERE T257_Id = '{$row['T257_Id']}'");

            //Atualiza o pedido com notificação de estoque
            if(isset($row['T005_Id']) && $row['T005_Id'] > 0 && !in_array($row['T005_Id'], $pedidosAtualizados)) {
                $pedidosAtualizados[] = $row['T005_Id'];
                mysql_query("UPDATE T005 SET T005_Flag_Notificacao_Estoque = 'S' WHERE T005_Id = '{$row['T005_Id']}'");
            }

            $T256=mysql_query("SELECT SUM(IFNULL(T256_Quantidade_Saldo,0)) as Saldo FROM T256  WHERE T256_T255_Id='{$row['T256_T255_Id']}'");
            $mT256=mysql_fetch_array($T256);

            if(empty($mT256['Saldo']) || $mT256['Saldo']==0 || $mT256['Saldo']=''){
                log("UPDATE T255 set T255_Flag_Status='99' WHERE T255_Id='{$row['T256_T255_Id']}'");
                $T255=mysql_query("UPDATE T255 set T255_Flag_Status='99' WHERE T255_Id='{$row['T256_T255_Id']}'");
            }

        }

        mysql_query("COMMIT");
        return true;
    }
}


