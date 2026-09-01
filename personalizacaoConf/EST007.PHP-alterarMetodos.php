<?php
namespace hardness;
/**
* Alterar (sobre-escrever) Métodos de uma Classe
*/

class EST007 extends EST007_ {
	// defina os métodos para sobreescrever

    // Melhoria para lançar o numero do lote
    public function est007ProcessarMontagem($T047_Id)
    {
        global $g;
        require_once("bibliotecas/classes/CAD002.php");
        $CAD002 = new CAD002();
        
        if ($T047_Id <= 0)
            return 'Falha no processo, não foi recebido o T047_Id';
        
        $D048_Id = 1;
        
        $cSql_T047 = mysql_query("select *
		                            from T047
		                            where T047_Id='$T047_Id'");
        $mT047     = mysql_fetch_array($cSql_T047);
        if ($mT047['T047_Flag_Status'] == 'A')
            return "Esta montagem já foi feita. VERIFIQUE!!!";
        
        /////////////////////////////////////////////////////////////////////
        // VERIFICA SE A MONTAGEM NAO ESTA VAZIA OU SEM SUB-ITENS
        /////////////////////////////////////////////////////////////////////
        $cSql_T071 = mysql_query("select sum(if(T071_Id is not null,1,0)) as Total_Sub_Itens,
		                                 D001_Codigo_Produto
		                            from T048
		                       left join T071 on T071_T048_Id=T048_Id
		                       left join T047 on T048_T047_Id=T047_Id
		                       left join D049 on T048_D049_Id=D049_Id
		                       left join D001 on D049_D001_Id=D001_Id
		                           where T047_Id='$T047_Id'
		                        group by T048_Id");
        if (mysql_num_rows($cSql_T071) == 0)
            return "Não existem itens nesta montagem!";
        
        while ($mT071 = mysql_fetch_array($cSql_T071)) {
            if ($mT071['Total_Sub_Itens'] == 0) {
                $mMsgSubItem[] = $mT071['D001_Codigo_Produto'];
            }
            
        }
        if (count($mMsgSubItem) > 0)
            return "Os itens abaixo não possuem sub-itens:<br />" . implode("<br>", $mMsgSubItem) . "<br />VERIQUE POR FAVOR!";
        /*
        //////////////////////////////////////////////////////////////////////
        //-- VERIFICA SE EXISTEM SUB-ITENS SEM CUSTO
        //////////////////////////////////////////////////////////////////////
        $cSql_T071=mysql_query("select D001_Codigo_Produto,
        D002_Descricao_Produto
        from T048
        left join T071 on T071_T048_Id=T048_Id
        left join T047 on T048_T047_Id=T047_Id
        left join D009 on T071_D009_Id=D009_Id
        left join D049 on D009_D049_Id=D049_Id
        left join D001 on D049_D001_Id=D001_Id
        left join D002 on D001_D002_Id=D002_Id
        where T047_Id='$T047_Id'
        and D009_Valor_Custo_Compra<=0");
        if (mysql_num_rows($cSql_T071)>0) {
        while ($mT071=mysql_fetch_array($cSql_T071)) {
        $mInativados[]="{$mT071['D001_Codigo_Produto']} - {$mT071['D002_Descricao_Produto']}";
        }
        $Inativados=implode('<br />',$mInativados);
        return "OS SUB-ITENS ABAIXO ESTÃO SEM CUSTO:<br />$Inativados";
        }
        */
        
        //////////////////////////////////////////////////////////////////////
        //-- VERIFICA SE EXISTEM SUB-ITENS INATIVOS
        //////////////////////////////////////////////////////////////////////
        $cSql_T071 = mysql_query("select D001_Codigo_Produto,
		                                 D002_Descricao_Produto
		                           from T048
		                      left join T071 on T071_T048_Id=T048_Id
		                      left join T047 on T048_T047_Id=T047_Id
		                      left join D009 on T071_D009_Id=D009_Id
		                      left join D049 on D009_D049_Id=D049_Id
		                      left join D001 on D049_D001_Id=D001_Id
		                      left join D002 on D001_D002_Id=D002_Id
		                          where T047_Id='$T047_Id'
		                            and D001_Flag_Ativo='N'");
        if (mysql_num_rows($cSql_T071) > 0) {
            while ($mT071 = mysql_fetch_array($cSql_T071)) {
                $mInativados[] = "{$mT071['D001_Codigo_Produto']} - {$mT071['D002_Descricao_Produto']}";
            }
            $Inativados = implode('/p', $mInativados);
            return "OS SUB-ITENS ABAIXO ESTÃO INATIVOS:<br />$Inativados";
            
        }
        
        //////////////////////////////////////////////////////////////////////
        //-- VERIFICA SE EXISTEM ITENS INATIVOS
        //////////////////////////////////////////////////////////////////////
        $cSql_T048 = mysql_query("select D001_Codigo_Produto,
                                         D002_Descricao_Produto
                                    from T048
                               left join D049 on T048_D049_Id=D049_Id
                               left join D001 on D049_D001_Id=D001_Id
                               left join D002 on D001_D002_Id=D002_Id
                                   where T048_T047_Id='$T047_Id'
                                     and D001_Flag_Ativo='N'");
        if (mysql_num_rows($cSql_T048) > 0) {
            while ($mT048 = mysql_fetch_array($cSql_T048)) {
                $mInativados[] = "{$mT048['D001_Codigo_Produto']} - {$mT048['D002_Descricao_Produto']}";
            }
            $Inativados = implode('<br />', $mInativados);
            return "OS ITENS ABAIXO ESTÃO INATIVOS:<br />$Inativados";
        }
        
        //////////////////////////////////////////////////////////////////////
        //-- VERIFICA SE EXISTEM ITENS SEM O CAMPO TIPO PREENCHIDO
        //////////////////////////////////////////////////////////////////////
        $cSql_T048 = mysql_query("select D001_Codigo_Produto,
                                         D001_Descricao_Produto
                                    from T048
                               left join D049 on T048_D049_Id=D049_Id
                               left join D001 on D049_D001_Id=D001_Id
                               left join D002 on D001_D002_Id=D002_Id
                                   where T048_T047_Id='$T047_Id'
                                     and (T048_Flag_Tipo_Montagem='' or T048_Flag_Tipo_Montagem is null)");
        if (mysql_num_rows($cSql_T048) > 0) {
            while ($mT048 = mysql_fetch_array($cSql_T048)) {
                $mrestricaoTipo[] = "{$mT048['D001_Codigo_Produto']} - {$mT048['D001_Descricao_Produto']}";
            }
            $restricaoTipo = implode('<br />', $mrestricaoTipo);
            return "Você deve preencher a coluna \"Tipo\" dos itens abaixo:<br />$restricaoTipo";
        }
        
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //-- VERFICA SE O ESTOQUE � SUFICIENTE PARA MONTAR OS ITENS
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $cSql_T048 = mysql_query("select *
		                            from T048
		                       left join D049 on D049_Id=T048_D049_Id
                               left join D001 on D001_Id=D049_D001_Id
		                       left join D009 on D049_Id=D009_D049_Id and D009_C004_Id='{$g['C004']['C004_Id']}'
                               left join T066 on T066_Id=T048_T066_Id
		                           where T048_T047_Id='$T047_Id'");
        if (mysql_num_rows($cSql_T048) == 0)
            return "Não existem ítens para montar.<br />VERIFIQUE!!!";
        
        while ($mT048 = mysql_fetch_array($cSql_T048)) {
            
            if (empty($mT048['D009_Id']))
                return 'O Código: ' . $mT048['T048_Codigo_Produto'] . ' não está ligado ao Estoque (D009_Id)' . "select * from T048 left join D049 on D049_Id=T048_D049_Id left join D009 on D049_Id=D009_D049_Id and D009_C004_Id='{$g['C004']['C004_Id']}' where T048_T047_Id='$T047_Id'";
            
            $cSql_T071 = mysql_query("select *
		                                from T071
		                           left join D009 on D009_Id=T071_D009_Id
		                           left join D049 on D049_Id=D009_D049_Id
		                           left join D001 on D001_Id=D049_D001_Id
                                   left join T066 on T066_Id=T071_T066_Id
		                               where T071_T048_Id={$mT048['T048_Id']}");
            
            while ($mT071 = mysql_fetch_array($cSql_T071)) {
                
                if (empty($mT071['D009_Id'])) {
                    $mMsg_Erro[] = "Codigo: {$mD071['D001_Codigo_Produto']}";
                }
                if ($mT048['T048_Flag_Tipo_Montagem'] == 'M') {
                    if($mT071['T071_T066_Id'] > 0){
                        $quantidadeEstoque = $mT071['T066_Quantidade_Estoque'];
                    } else{
                        // $quantidadeEstoque = $mT071['D009_Quantidade_Estoque'];
                        return "Não foi selecionado o lote no Sub-Item: {$mT071['D001_Codigo_Produto']} - {$mT071['D001_Descricao_Produto']}";
                    }
                    $Quantidade_Necessaria = $mT071['T071_Quantidade'] * $mT048['T048_Quantidade'];
                    if (round($Quantidade_Necessaria, 2) > round($quantidadeEstoque, 2)) {
                        if($mT071['T071_T066_Id'] > 0){
                            $mMsg[] = "Produto: {$mT048['D001_Codigo_Produto']} - Sub-Item: {$mT071['D001_Codigo_Produto']} | Estoque Locação: " . number_format($quantidadeEstoque, 2, ',', '.') . " | Necessário para Montagem: " . number_format($Quantidade_Necessaria, 2, ',', '.');
                        } else {
                            $mMsg[] = "Produto: {$mT048['D001_Codigo_Produto']} - Sub-Item: {$mT071['D001_Codigo_Produto']} | Estoque: " . number_format($quantidadeEstoque, 2, ',', '.') . " | Necessário para Montagem: " . number_format($Quantidade_Necessaria, 2, ',', '.');
                        }
                    }
                } else {
                    if($mT048['T048_T066_Id'] > 0){
                        $quantidadeEstoque = $mT048['T066_Quantidade_Estoque'];
                    } else {
                        $quantidadeEstoque = $mT048['D009_Quantidade_Estoque'];
                    }
                    $Quantidade_Necessaria = $mT048['T048_Quantidade'];
                    if (round($Quantidade_Necessaria, 2) > round($quantidadeEstoque, 2)) {
                        if($mT048['T048_T066_Id'] > 0){
                            $mMsg[] = "Produto: {$mT048['D001_Codigo_Produto']} - Sub-Item: {$mT048['D001_Codigo_Produto']} | Estoque Locação: " . number_format($quantidadeEstoque, 2, ',', '.') . " | Necessário para Desmontagem: " . number_format($Quantidade_Necessaria, 2, ',', '.');
                        } else {
                            $mMsg[] = "Produto: {$mT048['D001_Codigo_Produto']} - Sub-Item: {$mT048['D001_Codigo_Produto']} | Estoque: " . number_format($quantidadeEstoque, 2, ',', '.') . " | Necessário para Desmontagem: " . number_format($Quantidade_Necessaria, 2, ',', '.');
                        }                        
                    }
                }
            }
            
        }
        if ((count($mMsg) > 0) or (count($mMsg_Erro) > 0)) {
            
            if (count($mMsg_Erro) > 0) {
                $cMsg_Erro = implode('<br />', $mMsg_Erro);
                $cMensagem = "Os ítens abaixo não têm relacionamento com Estoque:<br />$cMsg_Erro";
                if (count($mMsg) > 0) {
                    $cMensagem .= "<br />" . str_repeat("-", 80) . "<br />";
                }
            }
            
            if (count($mMsg) > 0) {
                $cMsg = implode('<br />', $mMsg);
                $cMensagem .= "Quantidade insuficiente para montar/desmontar os itens abaixo:<br />$cMsg";
            }
            return $cMensagem;
            
        }
        
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //-- ATUALIZA O HISTORICO
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        mysql_query("SET AUTOCOMMIT=0");
        mysql_query("START TRANSACTION");
        $cSql_Dolar = mysql_query("select D016_Dolar('') as Dolar");
        $mDolar     = mysql_fetch_array($cSql_Dolar);

        // Array para pegar o D009 dos subitens e processar o estoque depois que for processado o estoque do item montado
        $T075_D009_Id = array();
        $dir = "{$g['pathDados']}certificados/";
                
        // Pega os Itens a serem montados/desmontados        
        $cSql_T048 = mysql_query("SELECT *
		                            FROM T048
		                       LEFT JOIN D049 ON D049_Id=T048_D049_Id
		                       LEFT JOIN D001 ON D001_Id=D049_D001_Id
		                       LEFT JOIN D009 ON D049_Id=D009_D049_Id AND D009_C004_Id='{$g['C004']['C004_Id']}'
                               LEFT JOIN T066 ON T066_Id = T048_T066_Id
		                           WHERE T048_T047_Id='{$T047_Id}'");

        while ($mT048 = mysql_fetch_array($cSql_T048)) {

            if ($mT048['T048_Flag_Tipo_Montagem'] == 'M') {
                $del = "DELETE FROM T140 WHERE T140_D001_Id = {$mT048['D001_Id']}";
                mysql_query($del);
                if (mysql_error()) {
                    $error = mysql_error();
                    mysql_query("ROLLBACK");
                    return "Erro ao deletar documentos no item: " . $mT048['D001_Codigo_Produto'] . ": " . $error;
                }
            }            
            unset($mDemonstrativo_Custo);
            
            $cSql_T071 = mysql_query("SELECT *,
                                             D009_Valor_Custo_Compra AS Valor_Custo_Certo
		                                FROM T071
		                           LEFT JOIN D009 ON D009_Id=T071_D009_Id
		                           LEFT JOIN D049 ON D049_Id=D009_D049_Id
		                           LEFT JOIN D001 ON D001_Id=D049_D001_Id
		                           LEFT JOIN D002 ON D002_Id=D001_D002_Id
		                               WHERE T071_T048_Id='{$mT048['T048_Id']}'");
            
            $D009_Valor_Custo_Unitario_Principal = 0;

            while ($mT071 = mysql_fetch_array($cSql_T071)) {
                
                if ($mT048['T048_Flag_Tipo_Montagem'] == 'M') {
                    $T001_Observacao         = "Montagem Ok";
                    $T001_Descricao_Operacao = "Mont-$T047_Id";
                    $Flag_OperacaoT071       = "S";
                    $Flag_OperacaoT048       = "E";
                    $Flag_MMF                = "S";
                } else {
                    $T001_Observacao         = "Desmontagem Ok";
                    $T001_Descricao_Operacao = "Desmont-$T047_Id";
                    $Flag_OperacaoT071       = "E";
                    $Flag_OperacaoT048       = "S";
                    $Flag_MMF                = "N";
                }
                
                $Quantidade_Necessaria = $mT071['T071_Quantidade'] * $mT048['T048_Quantidade'];
                
                //                  if ( $mT071[D009_Quantidade_Estoque]>=$Quantidade_Necessaria ) {
                
                $Custo_Sub_Item = $mT071['Valor_Custo_Certo'] * $mT071['T071_Quantidade'];
                
                $D009_Valor_Custo_Unitario_Principal += $Custo_Sub_Item;
                
                $mDemonstrativo_Custo[] = "{$mT071['T071_Quantidade']},,{$mT071['D001_Codigo_Produto']},," . number_format($Custo_Sub_Item, 2, ',', '.');
                
                
                $cSql_D009 = mysql_query("SELECT *
		                                    FROM D009
		                               LEFT JOIN D049 ONT D009_D049_Id=D049_Id
		                                   WHERE D009_Id='{$mT071['D009_Id']}'");
                $mD009     = mysql_fetch_array($cSql_D009);
                
                ///////////////////////////////////////////////////////////////////////////////////////////////
                // ENCONTRA O ID DO T066
                ///////////////////////////////////////////////////////////////////////////////////////////////
                
                //$cSql_T066 = mysql_query("select T066_Id from T066 where T066_D048_Id='$D048_Id' and T066_D009_Id='{$mT071['D009_Id']}'");
                //$mT066     = mysql_fetch_array($cSql_T066);
                if($mT071['T071_T066_Id'] > 0){
                    $mT066['T066_Id'] = $mT071['T071_T066_Id'] ;
                } else {
                    $sqlT066 = <<<EOT
                                    SELECT T066_Id
                                      FROM T066
                                 LEFT JOIN D004 ON D004_Id = T066_D004_Id
                                 LEFT JOIN D146 ON D146_Id = D004_D146_Id
                                     WHERE T066_D009_Id='{$mT071['D009_Id']}'
                                  ORDER BY D146_Flag_Principal DESC
EOT;
                    $mT066 = mysql_fetch_array(mysql_query($sqlT066));              
                }

                $tmpD004_Id = getD004($mT066['T066_Id']);

                // ATUALIZA O HISTORICO DO PRODUTO

                $T001_Id    = mysql_query("insert into T001 (
                                                T001_D024_Id,
                                                T001_D009_Id,
                                                T001_C004_Id,
                                                T001_T048_Id,
                                                T001_T071_Id,
                                                T001_T066_Id,
                                                T001_Codigo_Produto,
                                                T001_Data_Lancamento,
                                                T001_Descricao_Operacao,
                                                T001_Quantidade,
                                                T001_Flag_Operacao,
                                                T001_Flag_Atualiza_Custo,
                                                T001_Valor_Preco_Unitario,
                                                T001_Observacao,
                                                T001_Valor_Custo_Unitario,
                                                T001_Quantidade_Saldo,
                                                T001_Flag_MMF,
                                                T001_D004_Id
                                            ) values (
                                                '{$mD009['D049_D024_Id']}',
                                                '{$mT071['D009_Id']}',
                                                '{$g['empresaAtual']}',
                                                '{$mT048['T048_Id']}',
                                                '{$mT071['T071_Id']}',
                                                '{$mT066['T066_Id']}',
                                                '{$mD42['D001_Codigo_Produto']}',
                                                current_date(),
                                                '$T001_Descricao_Operacao',
                                                '$Quantidade_Necessaria',
                                                '$Flag_OperacaoT071',
                                                'N',
                                                '0',
                                                '$T001_Observacao',
                                                '{$mT071['Valor_Custo_Certo']}',
                                                '0',
                                                '{$Flag_MMF}',
                                                '{$tmpD004_Id}'
                                            )");

                // Array para pegar o D009 dos subitens e processar o estoque depois que for processado o estoque do item montado
                $T075_D009_Id[] = $mT071['D009_Id'];
            
                //                }

                if ($mT048['T048_Flag_Tipo_Montagem'] == 'M') {
                    $select = "SELECT T140.* FROM T140 left join T303 ON T140_Id = T303_T140_Id WHERE (T140_D001_Id = {$mT071['D001_Id']} OR T303_D001_Id = {$mT071['D001_Id']}) GROUP BY T140_Id";
                    log($select);
                    $T140 = mysql_query($select);
                    if(mysql_num_rows($T140) > 0){
                        while($mT140 = mysql_fetch_assoc($T140)) {
                            $mT140['T140_D001_Id'] = $mT048['D001_Id']; // Id do item montado
                            $T140_Id_Antigo = $mT140['T140_Id'];
                            unset($mT140['T140_Id']); // Exclui o elemento do Id pois ele é auto increment e vai quebrar o insert a baixo

                            // Monta o insert dinamicamente
                            $insert = "INSERT INTO T140 (" . implode(",", array_keys($mT140)) . ") VALUES (" . implode(",", array_map(function($value) { return "'" . mysql_real_escape_string($value) . "'"; }, $mT140)) . ")";
                            log($insert);
                            
                            mysql_query($insert);
                            if (mysql_error()) {
                                $error = mysql_error();
                                mysql_query("ROLLBACK");
                                return "Erro ao inserir documentos no item: " . $mT048['T048_Codigo_Produto'] . ": " . $error;
                            }

                            //Copia arquivo
                            $pathinfo = pathinfo($mT140['T140_Nome_Arquivo']);
                            $fileAntigo = $dir . $T140_Id_Antigo . '.'. $pathinfo['extension'];
                            $fileNovo = $dir . $g['mysqlLastId'] . '.'. $pathinfo['extension'];
                            if(file_exists($fileAntigo)){
                                copy($fileAntigo, $fileNovo);
                            }
                        }
                    }
                }               
            }            
            $mDemonstrativo_Custo[] = ",,,,TOTAL,," . number_format($D009_Valor_Custo_Unitario_Principal, 2, ',', '.');
            $Demonstrativo_Custo    = implode("<br ></EOT>", $mDemonstrativo_Custo);
            unset($mDemonstrativo_Custo);
            
            $cSql_D009 = mysql_query("select *
		                              from D009
		                         left join D049 on D009_D049_Id=D049_Id
		                             where D009_Id='{$mT048['D009_Id']}'");
            $mD009     = mysql_fetch_array($cSql_D009);
            
            
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // ENCONTRA O ID DO T066
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            //$cSql_T066 = mysql_query("select T066_Id from T066 where T066_D048_Id='$D048_Id' and T066_D009_Id='{$mT048['D009_Id']}'");
            //$mT066     = mysql_fetch_array($cSql_T066);

            if($mT048['T048_T066_Id'] > 0){
                $mT066['T066_Id'] = $mT048['T048_T066_Id'];
            } else {
                $sqlT066 = <<<EOT
                                SELECT T066_Id
                                 FROM T066
                            LEFT JOIN D004 ON D004_Id = T066_D004_Id
                            LEFT JOIN D146 ON D146_Id = D004_D146_Id
                                WHERE T066_D009_Id='{$mT048['D009_Id']}'
                            ORDER BY D146_Flag_Principal DESC
EOT;
                $mT066 = mysql_fetch_array(mysql_query($sqlT066));
            }

            
            $tmpD004_Id = getD004($mT066['T066_Id']);
            $T066_Id    = $mT066['T066_Id'];
            $numeroLote = date("YmdHi") . rand(1000, 9999);
            if ($mT048['T048_Flag_Tipo_Montagem'] == 'M') {
                error_log("Quantidade Estoque Montado : ".$mT048['T066_Quantidade_Estoque']);
                if ($mT048['T066_Quantidade_Estoque'] > 0) {
                    //insere nova locação no produto para lançar o lote
                    mysql_query("INSERT INTO T066 (T066_D009_Id, T066_D004_Id) VALUES ('{$mT048['D009_Id']}', '{$tmpD004_Id}')");
                    $T066_Id    = $g['mysqlLastId'];                    
                    mysql_query("INSERT INTO T066A (T066A_T066_Id, T066A_Numero_Lote) VALUES ('{$T066_Id}','{$numeroLote}')");
                    mysqli_query("UPDATE T048 SET T048_T066_Id = '{$T066_Id}' WHERE T048_Id = '{$mT048['T048_Id']}'");
                } else {
                    $T066A = mysql_query("SELECT T066A_Id FROM T066A WHERE T066A_T066_Id = '{$T066_Id}'");
                    if(mysql_num_rows($T066A) > 0){
                        mysqli_query("UPDATE T066A SET T066A_Numero_Lote = '{$numeroLote}' WHERE T066A_T066_Id = '{$T066_Id}'");
                    } else {
                        mysqli_query("INSERT INTO T066A (T066A_T066_Id,T066A_Numero_Lote) VALUES ('{$T066_Id}','{$numeroLote}' )");
                    }
                }
            }

            // ATUALIZA O HISTORICO DO PRODUTO
            $T001_Id    = mysql_query("insert into T001 (
		                                          T001_D024_Id,
		                                          T001_D009_Id,
		                                          T001_C004_Id,
		                                          T001_T048_Id,
		                                          T001_T066_Id,
		                                          T001_Codigo_Produto,
		                                          T001_Data_Lancamento,
		                                          T001_Descricao_Operacao,
		                                          T001_Quantidade,
		                                          T001_Flag_Operacao,
		                                          T001_Valor_Preco_Unitario,
		                                          T001_Observacao,
		                                          T001_Valor_Custo_Unitario,
		  					                      T001_Flag_Atualiza_Custo,
		                                          T001_Quantidade_Saldo,
		                                          T001_Demonstrativo_Custo,
		                                          T001_D004_Id
		                                          )
		                                  values
		                                          ('{$mD009['D049_D024_Id']}',
		                                          '{$mT048['D009_Id']}',
		                                          '{$g['empresaAtual']}',
		                                          '{$mT048['T048_Id']}',
		                                          '{$T066_Id}',
		                                          '{$mT048['D001_Codigo_Produto']}',
		                                          current_date(),
		                                          '$T001_Descricao_Operacao',
		                                          '{$mT048['T048_Quantidade']}',
		                                          '$Flag_OperacaoT048',
		                                          '0',
		                                          '$T001_Observacao',
		                                          '$D009_Valor_Custo_Unitario_Principal',
		                                          'S',
		                                          '0',
		                                          '$Demonstrativo_Custo',
		                                          '{$tmpD004_Id}')");
            
            $retorno = $CAD002->D001_reprocessa_historico(false, $mT048['D009_Id'], 100, true);
            if ($retorno !== true) {
                return $retorno;
            }
        }
        
        // recalcula o historido dos subitens
        foreach($T075_D009_Id as $D009_Id){
            $retorno = $CAD002->D001_reprocessa_historico(false, $D009_Id, 100, true);
            if ($retorno !== true) {
                return $retorno;
            }
        }
        
        mysql_query("UPDATE T047 SET T047_Flag_Status='A' WHERE T047_Id='{$T047_Id}'");        
        mysql_query("COMMIT");
        
        return true;
    }

	/** est007ProcessarMontagem
     *
     * @param int $T047_Id
     */
    public function xxxxest007ProcessarMontagem($T047_Id)
    {
        global $g;
        require_once("bibliotecas/classes/CAD002.php");
        $CAD002 = new CAD002();
        
        if ($T047_Id <= 0)
            return 'Falha no processo, não foi recebido o T047_Id';
        
        $D048_Id = 1;
        
        $cSql_T047 = mysql_query("select *
		                            from T047
		                            where T047_Id='$T047_Id'");
        $mT047     = mysql_fetch_array($cSql_T047);
        if ($mT047['T047_Flag_Status'] == 'A')
            return "Esta montagem já foi feita. VERIFIQUE!!!";
        
        /////////////////////////////////////////////////////////////////////
        // VERIFICA SE A MONTAGEM NAO ESTA VAZIA OU SEM SUB-ITENS
        /////////////////////////////////////////////////////////////////////
        $cSql_T071 = mysql_query("select sum(if(T071_Id is not null,1,0)) as Total_Sub_Itens,
		                                 D001_Codigo_Produto
		                            from T048
		                       left join T071 on T071_T048_Id=T048_Id
		                       left join T047 on T048_T047_Id=T047_Id
		                       left join D049 on T048_D049_Id=D049_Id
		                       left join D001 on D049_D001_Id=D001_Id
		                           where T047_Id='$T047_Id'
		                        group by T048_Id");
        if (mysql_num_rows($cSql_T071) == 0)
            return "Não existem itens nesta montagem!";
        
        while ($mT071 = mysql_fetch_array($cSql_T071)) {
            if ($mT071['Total_Sub_Itens'] == 0) {
                $mMsgSubItem[] = $mT071['D001_Codigo_Produto'];
            }
            
        }
        if (count($mMsgSubItem) > 0)
            return "Os itens abaixo não possuem sub-itens:<br />" . implode("<br>", $mMsgSubItem) . "<br />VERIQUE POR FAVOR!";
        /*
        //////////////////////////////////////////////////////////////////////
        //-- VERIFICA SE EXISTEM SUB-ITENS SEM CUSTO
        //////////////////////////////////////////////////////////////////////
        $cSql_T071=mysql_query("select D001_Codigo_Produto,
        D002_Descricao_Produto
        from T048
        left join T071 on T071_T048_Id=T048_Id
        left join T047 on T048_T047_Id=T047_Id
        left join D009 on T071_D009_Id=D009_Id
        left join D049 on D009_D049_Id=D049_Id
        left join D001 on D049_D001_Id=D001_Id
        left join D002 on D001_D002_Id=D002_Id
        where T047_Id='$T047_Id'
        and D009_Valor_Custo_Compra<=0");
        if (mysql_num_rows($cSql_T071)>0) {
        while ($mT071=mysql_fetch_array($cSql_T071)) {
        $mInativados[]="{$mT071['D001_Codigo_Produto']} - {$mT071['D002_Descricao_Produto']}";
        }
        $Inativados=implode('<br />',$mInativados);
        return "OS SUB-ITENS ABAIXO ESTÃO SEM CUSTO:<br />$Inativados";
        }
        */
        
        //////////////////////////////////////////////////////////////////////
        //-- VERIFICA SE EXISTEM SUB-ITENS INATIVOS
        //////////////////////////////////////////////////////////////////////
        $cSql_T071 = mysql_query("select D001_Codigo_Produto,
		                                 D002_Descricao_Produto
		                           from T048
		                      left join T071 on T071_T048_Id=T048_Id
		                      left join T047 on T048_T047_Id=T047_Id
		                      left join D009 on T071_D009_Id=D009_Id
		                      left join D049 on D009_D049_Id=D049_Id
		                      left join D001 on D049_D001_Id=D001_Id
		                      left join D002 on D001_D002_Id=D002_Id
		                          where T047_Id='$T047_Id'
		                            and D001_Flag_Ativo='N'");
        if (mysql_num_rows($cSql_T071) > 0) {
            while ($mT071 = mysql_fetch_array($cSql_T071)) {
                $mInativados[] = "{$mT071['D001_Codigo_Produto']} - {$mT071['D002_Descricao_Produto']}";
            }
            $Inativados = implode('/p', $mInativados);
            return "OS SUB-ITENS ABAIXO ESTÃO INATIVOS:<br />$Inativados";
            
        }
        
        //////////////////////////////////////////////////////////////////////
        //-- VERIFICA SE EXISTEM ITENS INATIVOS
        //////////////////////////////////////////////////////////////////////
        $cSql_T048 = mysql_query("select D001_Codigo_Produto,
                                         D002_Descricao_Produto
                                    from T048
                               left join D049 on T048_D049_Id=D049_Id
                               left join D001 on D049_D001_Id=D001_Id
                               left join D002 on D001_D002_Id=D002_Id
                                   where T048_T047_Id='$T047_Id'
                                     and D001_Flag_Ativo='N'");
        if (mysql_num_rows($cSql_T048) > 0) {
            while ($mT048 = mysql_fetch_array($cSql_T048)) {
                $mInativados[] = "{$mT048['D001_Codigo_Produto']} - {$mT048['D002_Descricao_Produto']}";
            }
            $Inativados = implode('<br />', $mInativados);
            return "OS ITENS ABAIXO ESTÃO INATIVOS:<br />$Inativados";
        }
        
        //////////////////////////////////////////////////////////////////////
        //-- VERIFICA SE EXISTEM ITENS SEM O CAMPO TIPO PREENCHIDO
        //////////////////////////////////////////////////////////////////////
        $cSql_T048 = mysql_query("select D001_Codigo_Produto,
                                         D001_Descricao_Produto
                                    from T048
                               left join D049 on T048_D049_Id=D049_Id
                               left join D001 on D049_D001_Id=D001_Id
                               left join D002 on D001_D002_Id=D002_Id
                                   where T048_T047_Id='$T047_Id'
                                     and (T048_Flag_Tipo_Montagem='' or T048_Flag_Tipo_Montagem is null)");
        if (mysql_num_rows($cSql_T048) > 0) {
            while ($mT048 = mysql_fetch_array($cSql_T048)) {
                $mrestricaoTipo[] = "{$mT048['D001_Codigo_Produto']} - {$mT048['D001_Descricao_Produto']}";
            }
            $restricaoTipo = implode('<br />', $mrestricaoTipo);
            return "Você deve preencher a coluna \"Tipo\" dos itens abaixo:<br />$restricaoTipo";
        }
        
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //-- VERFICA SE O ESTOQUE � SUFICIENTE PARA MONTAR OS ITENS
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $cSql_T048 = mysql_query("select *
		                            from T048
		                       left join D049 on D049_Id=T048_D049_Id
                               left join D001 on D001_Id=D049_D001_Id
		                       left join D009 on D049_Id=D009_D049_Id and D009_C004_Id='{$g['C004']['C004_Id']}'
                               left join T066 on T066_Id=T048_T066_Id
		                           where T048_T047_Id='$T047_Id'");
        if (mysql_num_rows($cSql_T048) == 0)
            return "Não existem ítens para montar.<br />VERIFIQUE!!!";
        
        while ($mT048 = mysql_fetch_array($cSql_T048)) {
            
            if (empty($mT048['D009_Id']))
                return 'O Código: ' . $mT048['T048_Codigo_Produto'] . ' não está ligado ao Estoque (D009_Id)' . "select * from T048 left join D049 on D049_Id=T048_D049_Id left join D009 on D049_Id=D009_D049_Id and D009_C004_Id='{$g['C004']['C004_Id']}' where T048_T047_Id='$T047_Id'";
            
            $cSql_T071 = mysql_query("select *
		                                from T071
		                           left join D009 on D009_Id=T071_D009_Id
		                           left join D049 on D049_Id=D009_D049_Id
		                           left join D001 on D001_Id=D049_D001_Id
                                   left join T066 on T066_Id=T071_T066_Id
		                               where T071_T048_Id={$mT048['T048_Id']}");
            
            while ($mT071 = mysql_fetch_array($cSql_T071)) {
                
                if (empty($mT071['D009_Id'])) {
                    $mMsg_Erro[] = "Codigo: {$mD071['D001_Codigo_Produto']}";
                }
                if ($mT048['T048_Flag_Tipo_Montagem'] == 'M') {
                    if($mT071['T071_T066_Id'] > 0){
                        $quantidadeEstoque = $mT071['T066_Quantidade_Estoque'];
                    } else{
                         $quantidadeEstoque = $mT071['D009_Quantidade_Estoque'];
                    }
                    $Quantidade_Necessaria = $mT071['T071_Quantidade'] * $mT048['T048_Quantidade'];
                    if (round($Quantidade_Necessaria, 2) > round($quantidadeEstoque, 2)) {
                        if($mT071['T071_T066_Id'] > 0){
                            $mMsg[] = "Produto: {$mT048['D001_Codigo_Produto']} - Sub-Item: {$mT071['D001_Codigo_Produto']} | Estoque Locação: " . number_format($quantidadeEstoque, 2, ',', '.') . " | Necessário para Montagem: " . number_format($Quantidade_Necessaria, 2, ',', '.');
                        } else {
                            $mMsg[] = "Produto: {$mT048['D001_Codigo_Produto']} - Sub-Item: {$mT071['D001_Codigo_Produto']} | Estoque: " . number_format($quantidadeEstoque, 2, ',', '.') . " | Necessário para Montagem: " . number_format($Quantidade_Necessaria, 2, ',', '.');
                        }
                    }
                } else {
                    if($mT048['T048_T066_Id'] > 0){
                        $quantidadeEstoque = $mT048['T066_Quantidade_Estoque'];
                    } else {
                        $quantidadeEstoque = $mT048['D009_Quantidade_Estoque'];
                    }
                    $Quantidade_Necessaria = $mT048['T048_Quantidade'];
                    if (round($Quantidade_Necessaria, 2) > round($quantidadeEstoque, 2)) {
                        if($mT048['T048_T066_Id'] > 0){
                            $mMsg[] = "Produto: {$mT048['D001_Codigo_Produto']} - Sub-Item: {$mT048['D001_Codigo_Produto']} | Estoque Locação: " . number_format($quantidadeEstoque, 2, ',', '.') . " | Necessário para Desmontagem: " . number_format($Quantidade_Necessaria, 2, ',', '.');
                        } else {
                            $mMsg[] = "Produto: {$mT048['D001_Codigo_Produto']} - Sub-Item: {$mT048['D001_Codigo_Produto']} | Estoque: " . number_format($quantidadeEstoque, 2, ',', '.') . " | Necessário para Desmontagem: " . number_format($Quantidade_Necessaria, 2, ',', '.');
                        }                        
                    }
                }
            }
            
        }
        if ((count($mMsg) > 0) or (count($mMsg_Erro) > 0)) {
            
            if (count($mMsg_Erro) > 0) {
                $cMsg_Erro = implode('<br />', $mMsg_Erro);
                $cMensagem = "Os ítens abaixo não têm relacionamento com Estoque:<br />$cMsg_Erro";
                if (count($mMsg) > 0) {
                    $cMensagem .= "<br />" . str_repeat("-", 80) . "<br />";
                }
            }
            
            if (count($mMsg) > 0) {
                $cMsg = implode('<br />', $mMsg);
                $cMensagem .= "Quantidade insuficiente para montar/desmontar os itens abaixo:<br />$cMsg";
            }
            return $cMensagem;
            
        }
        
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //-- ATUALIZA O HISTORICO
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        mysql_query("SET AUTOCOMMIT=0");
        mysql_query("START TRANSACTION");
        $cSql_Dolar = mysql_query("select D016_Dolar('') as Dolar");
        $mDolar     = mysql_fetch_array($cSql_Dolar);

        // Array para pegar o D009 dos subitens e processar o estoque depois que for processado o estoque do item montado
        $T075_D009_Id = array();
        $dir = "{$g['pathDados']}certificados/";
                
        // Pega os Itens a serem montados/desmontados        
        $cSql_T048 = mysql_query("SELECT *
		                            FROM T048
		                       LEFT JOIN D049 ON D049_Id=T048_D049_Id
		                       LEFT JOIN D001 ON D001_Id=D049_D001_Id
		                       LEFT JOIN D009 ON D049_Id=D009_D049_Id AND D009_C004_Id='{$g['C004']['C004_Id']}'
		                           WHERE T048_T047_Id='{$T047_Id}'");

        while ($mT048 = mysql_fetch_array($cSql_T048)) {

            if ($mT048['T048_Flag_Tipo_Montagem'] == 'M') {
                $del = "DELETE FROM T140 WHERE T140_D001_Id = {$mT048['D001_Id']}";
                mysql_query($del);
                if (mysql_error()) {
                    $error = mysql_error();
                    mysql_query("ROLLBACK");
                    return "Erro ao deletar documentos no item: " . $mT048['D001_Codigo_Produto'] . ": " . $error;
                }
            }            
            unset($mDemonstrativo_Custo);
            
            $cSql_T071 = mysql_query("SELECT *,
                                             D009_Valor_Custo_Compra AS Valor_Custo_Certo
		                                FROM T071
		                           LEFT JOIN D009 ON D009_Id=T071_D009_Id
		                           LEFT JOIN D049 ON D049_Id=D009_D049_Id
		                           LEFT JOIN D001 ON D001_Id=D049_D001_Id
		                           LEFT JOIN D002 ON D002_Id=D001_D002_Id
		                               WHERE T071_T048_Id='{$mT048['T048_Id']}'");
            
            $D009_Valor_Custo_Unitario_Principal = 0;

            while ($mT071 = mysql_fetch_array($cSql_T071)) {
                
                if ($mT048['T048_Flag_Tipo_Montagem'] == 'M') {
                    $T001_Observacao         = "Montagem Ok";
                    $T001_Descricao_Operacao = "Mont-$T047_Id";
                    $Flag_OperacaoT071       = "S";
                    $Flag_OperacaoT048       = "E";
                } else {
                    $T001_Observacao         = "Desmontagem Ok";
                    $T001_Descricao_Operacao = "Desmont-$T047_Id";
                    $Flag_OperacaoT071       = "E";
                    $Flag_OperacaoT048       = "S";
                }
                
                $Quantidade_Necessaria = $mT071['T071_Quantidade'] * $mT048['T048_Quantidade'];
                
                //                  if ( $mT071[D009_Quantidade_Estoque]>=$Quantidade_Necessaria ) {
                
                $Custo_Sub_Item = $mT071['Valor_Custo_Certo'] * $mT071['T071_Quantidade'];
                
                $D009_Valor_Custo_Unitario_Principal += $Custo_Sub_Item;
                
                $mDemonstrativo_Custo[] = "{$mT071['T071_Quantidade']},,{$mT071['D001_Codigo_Produto']},," . number_format($Custo_Sub_Item, 2, ',', '.');
                
                
                $cSql_D009 = mysql_query("SELECT *
		                                    FROM D009
		                               LEFT JOIN D049 ONT D009_D049_Id=D049_Id
		                                   WHERE D009_Id='{$mT071['D009_Id']}'");
                $mD009     = mysql_fetch_array($cSql_D009);
                
                ///////////////////////////////////////////////////////////////////////////////////////////////
                // ENCONTRA O ID DO T066
                ///////////////////////////////////////////////////////////////////////////////////////////////
                
                //$cSql_T066 = mysql_query("select T066_Id from T066 where T066_D048_Id='$D048_Id' and T066_D009_Id='{$mT071['D009_Id']}'");
                //$mT066     = mysql_fetch_array($cSql_T066);
                if($mT071['T071_T066_Id'] > 0){
                    $mT066['T066_Id'] = $mT071['T071_T066_Id'] ;
                } else {
                    $sqlT066 = <<<EOT
                                    SELECT T066_Id
                                      FROM T066
                                 LEFT JOIN D004 ON D004_Id = T066_D004_Id
                                 LEFT JOIN D146 ON D146_Id = D004_D146_Id
                                     WHERE T066_D009_Id='{$mT071['D009_Id']}'
                                  ORDER BY D146_Flag_Principal DESC
EOT;
                    $mT066 = mysql_fetch_array(mysql_query($sqlT066));              
                }

                $tmpD004_Id = getD004($mT066['T066_Id']);
                // ATUALIZA O HISTORICO DO PRODUTO

                $T001_Id    = mysql_query("insert into T001 (
		                                                            T001_D024_Id,
		                                                            T001_D009_Id,
		                                                            T001_C004_Id,
		                                                            T001_T048_Id,
		                                                            T001_T071_Id,
		                                                            T001_T066_Id,
		                                                            T001_Codigo_Produto,
		                                                            T001_Data_Lancamento,
		                                                            T001_Descricao_Operacao,
		                                                            T001_Quantidade,
		                                                            T001_Flag_Operacao,
		                                                            T001_Flag_Atualiza_Custo,
		                                                            T001_Valor_Preco_Unitario,
		                                                            T001_Observacao,
		                                                            T001_Valor_Custo_Unitario,
		                                                            T001_Quantidade_Saldo,
		                                                            T001_Flag_MMF,
		                                                            T001_D004_Id
		                                                            )
		                                                    values
		                                                            ('{$mD009['D049_D024_Id']}',
		                                                            '{$mT071['D009_Id']}',
		                                                            '{$g['empresaAtual']}',
		                                                            '{$mT048['T048_Id']}',
		                                                            '{$mT071['T071_Id']}',
		                                                            '{$mT066['T066_Id']}',
		                                                            '{$mD42['D001_Codigo_Produto']}',
		                                                            current_date(),
		                                                            '$T001_Descricao_Operacao',
		                                                            '$Quantidade_Necessaria',
		                                                            '$Flag_OperacaoT071',
		                                                            'N',
		                                                            '0',
		                                                            '$T001_Observacao',
		                                                            '{$mT071['Valor_Custo_Certo']}',
		                                                            '0',
		                                                            'S',
		                                                            '{$tmpD004_Id}')");

                // Array para pegar o D009 dos subitens e processar o estoque depois que for processado o estoque do item montado
                $T075_D009_Id[] = $mT071['D009_Id'];
            
                //                }

                if ($mT048['T048_Flag_Tipo_Montagem'] == 'M') {
                    $select = "SELECT T140.* FROM T140 left join T303 ON T140_Id = T303_T140_Id WHERE (T140_D001_Id = {$mT071['D001_Id']} OR T303_D001_Id = {$mT071['D001_Id']}) GROUP BY T140_Id";
                    log($select);
                    $T140 = mysql_query($select);
                    if(mysql_num_rows($T140) > 0){
                        while($mT140 = mysql_fetch_assoc($T140)) {
                            $mT140['T140_D001_Id'] = $mT048['D001_Id']; // Id do item montado
                            $T140_Id_Antigo = $mT140['T140_Id'];
                            unset($mT140['T140_Id']); // Exclui o elemento do Id pois ele é auto increment e vai quebrar o insert a baixo

                            // Monta o insert dinamicamente
                            $insert = "INSERT INTO T140 (" . implode(",", array_keys($mT140)) . ") VALUES (" . implode(",", array_map(function($value) { return "'" . mysql_real_escape_string($value) . "'"; }, $mT140)) . ")";
                            log($insert);
                            
                            mysql_query($insert);
                            if (mysql_error()) {
                                $error = mysql_error();
                                mysql_query("ROLLBACK");
                                return "Erro ao inserir documentos no item: " . $mT048['T048_Codigo_Produto'] . ": " . $error;
                            }

                            //Copia arquivo
                            $pathinfo = pathinfo($mT140['T140_Nome_Arquivo']);
                            $fileAntigo = $dir . $T140_Id_Antigo . '.'. $pathinfo['extension'];
                            $fileNovo = $dir . $g['mysqlLastId'] . '.'. $pathinfo['extension'];
                            if(file_exists($fileAntigo)){
                                copy($fileAntigo, $fileNovo);
                            }
                        }
                    }
                }               
            }            
            $mDemonstrativo_Custo[] = ",,,,TOTAL,," . number_format($D009_Valor_Custo_Unitario_Principal, 2, ',', '.');
            $Demonstrativo_Custo    = implode("<br ></EOT>", $mDemonstrativo_Custo);
            unset($mDemonstrativo_Custo);
            
            $cSql_D009 = mysql_query("select *
		                              from D009
		                         left join D049 on D009_D049_Id=D049_Id
		                             where D009_Id='{$mT048['D009_Id']}'");
            $mD009     = mysql_fetch_array($cSql_D009);
            
            
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // ENCONTRA O ID DO T066
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            //$cSql_T066 = mysql_query("select T066_Id from T066 where T066_D048_Id='$D048_Id' and T066_D009_Id='{$mT048['D009_Id']}'");
            //$mT066     = mysql_fetch_array($cSql_T066);

            if($mT048['T048_T066_Id'] > 0){
                $mT066['T066_Id'] = $mT048['T048_T066_Id'];
            } else {
                $sqlT066 = <<<EOT
                                SELECT T066_Id
                                 FROM T066
                            LEFT JOIN D004 ON D004_Id = T066_D004_Id
                            LEFT JOIN D146 ON D146_Id = D004_D146_Id
                                WHERE T066_D009_Id='{$mT048['D009_Id']}'
                            ORDER BY D146_Flag_Principal DESC
EOT;
                $mT066 = mysql_fetch_array(mysql_query($sqlT066));
            }

            
            $tmpD004_Id = getD004($mT066['T066_Id']);
            // ATUALIZA O HISTORICO DO PRODUTO
            $T001_Id    = mysql_query("insert into T001 (
		                                          T001_D024_Id,
		                                          T001_D009_Id,
		                                          T001_C004_Id,
		                                          T001_T048_Id,
		                                          T001_T066_Id,
		                                          T001_Codigo_Produto,
		                                          T001_Data_Lancamento,
		                                          T001_Descricao_Operacao,
		                                          T001_Quantidade,
		                                          T001_Flag_Operacao,
		                                          T001_Valor_Preco_Unitario,
		                                          T001_Observacao,
		                                          T001_Valor_Custo_Unitario,
		  					                      T001_Flag_Atualiza_Custo,
		                                          T001_Quantidade_Saldo,
		                                          T001_Demonstrativo_Custo,
		                                          T001_D004_Id
		                                          )
		                                  values
		                                          ('{$mD009['D049_D024_Id']}',
		                                          '{$mT048['D009_Id']}',
		                                          '{$g['empresaAtual']}',
		                                          '{$mT048['T048_Id']}',
		                                          '{$mT066['T066_Id']}',
		                                          '{$mT048['D001_Codigo_Produto']}',
		                                          current_date(),
		                                          '$T001_Descricao_Operacao',
		                                          '{$mT048['T048_Quantidade']}',
		                                          '$Flag_OperacaoT048',
		                                          '0',
		                                          '$T001_Observacao',
		                                          '$D009_Valor_Custo_Unitario_Principal',
		                                          'S',
		                                          '0',
		                                          '$Demonstrativo_Custo',
		                                          '{$tmpD004_Id}')");
            
            $retorno = $CAD002->D001_reprocessa_historico(false, $mT048['D009_Id'], 10);
            if ($retorno !== true) {
                return $retorno;
            }
        }
        
        // recalcula o historido dos subitens
        foreach($T075_D009_Id as $D009_Id){
            $retorno = $CAD002->D001_reprocessa_historico(false, $D009_Id, 10);
            if ($retorno !== true) {
                return $retorno;
            }
        }
        
        mysql_query("UPDATE T047 SET T047_Flag_Status='A' WHERE T047_Id='{$T047_Id}'");        
        mysql_query("COMMIT");
        
        return true;
    }
}










