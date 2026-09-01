<?php
namespace hardness;
/**
* Alterar (sobre-escrever) Métodos de uma Classe
*/

class EST001 extends EST001_ {
	// defina os métodos para sobreescrever


   /**
     * incluirTransferenciaEstoque
     *
     * @param Form $form
     * @param string $locacaoDestino
     * @return true/string
     */
    public function incluirTransferenciaEstoque($form, $locacaoDestino = false)
    {
        global $g;
        $D009_Id         = $form->campoValorEnviado('T030_D009_Id');
        $T029_Id         = $form->campoValorEnviado('T030_T029_Id');
        $T066_Id         = $form->campoValorEnviado('T030_T066_Id');
        $T030_Quantidade = gCorrigeNumeroInverte($form->campoValorEnviado('T030_Quantidade'));
        $T030_Observacao = strtoupper(mysql_real_escape_string($form->campoValorEnviado('T030_Observacao')));
        $operacao        = $form->campoValorEnviado('T030_Flag_Entrada_Saida');
        $operacao        = ($operacao == 'T') ? 'S' : $operacao;
        
        $cClausula = <<<EOT
            SELECT 
                D009_Id, 
                D009_Custo_Unitario(D009_Id) as Custo_Original,
                D001_Codigo_Produto 
            FROM D009 
            LEFT JOIN D049 ON D049_Id=D009_D049_Id 
            LEFT JOIN D001 ON D001_Id=D049_D001_Id 
            LEFT JOIN D037 ON D037_Id=D001_D037_Id 
            WHERE
                D009_Id='{$D009_Id}'
EOT;
        $T030  = mysql_query($cClausula);
        $mT030 = mysql_fetch_array($T030);

        $sqlD004 = mysql_query("SELECT T066_Id FROM D009 LEFT JOIN T066 ON T066_D009_Id=D009_Id WHERE D009_Id='{$D009_Id}' and ifnull(T066_D004_Id,0)=0 and T066_Id='{$T066_Id}'");
        if(mysql_num_rows($sqlD004) > 0) {
            return "Não foi informado a LOCAÇÃO de todos os produtos.<br />VERIFIQUE!";
        }
        
        // Insert na locação origem
        $D004_Id = getD004($T066_Id);
        $cClausula = <<<EOT
            INSERT INTO T030 (
                T030_T029_Id, 
                T030_D009_Id, 
                T030_C004_Id, 
                T030_C007_Id, 
                T030_T066_Id, 
                T030_Codigo_Produto, 
                T030_Quantidade, 
                T030_Flag_Entrada_Saida, 
                T030_Data_Inclusao, 
                T030_Valor_Custo_Unitario_Origem,
                T030_D004_Id,
                T030_Observacao
            ) VALUES ( 
                '$T029_Id', 
                '$D009_Id', 
                '{$g['empresaAtual']}', 
                '{$g['usuarioAtual']}', 
                '{$T066_Id}', 
                '{$mT030['D001_Codigo_Produto']}', 
                '{$T030_Quantidade}', 
                '{$operacao}', 
                current_date(), 
                '$mT030[Custo_Original]',
                '{$D004_Id}',
                '{$T030_Observacao}'
            )
EOT;
        mysql_query($cClausula);
        $cErro = mysql_error();
        if (!empty($cErro)) {
            return "Ocorreu um erro: " . $cErro . "<br ></EOT>Por favor entre em contato com o suporte.";
        }
        $T030_Id = $g['mysqlLastId'];
        
        if (!$locacaoDestino) {
            return true;
        }

        $sqlD004 = mysql_query("SELECT T066_Id FROM D009 LEFT JOIN T066 ON T066_D009_Id=D009_Id WHERE D009_Id='{$D009_Id}' and ifnull(T066_D004_Id,0)=0 and T066_Id='{$locacaoDestino}'");
        if(mysql_num_rows($sqlD004) > 0) {
            return "Não foi informado a locação de destino do produto<br />VERIFIQUE!";
        }
        // Insert na locação destino
        $D004_Id = getD004($locacaoDestino);
        $cClausula = <<<EOT
            INSERT INTO T030 (
                T030_T029_Id, 
                T030_D009_Id, 
                T030_C004_Id, 
                T030_C007_Id, 
                T030_T066_Id, 
                T030_Codigo_Produto, 
                T030_Quantidade, 
                T030_Flag_Entrada_Saida, 
                T030_Data_Inclusao, 
                T030_Valor_Custo_Unitario_Origem,
                T030_D004_Id,
                T030_T030_Id_Transferencia
            ) VALUES ( 
                '$T029_Id', 
                '$D009_Id', 
                '{$g['empresaAtual']}', 
                '{$g['usuarioAtual']}', 
                '{$locacaoDestino}', 
                '{$mT030['D001_Codigo_Produto']}', 
                '{$T030_Quantidade}', 
                'E', 
                current_date(), 
                '{$mT030['Custo_Original']}',
                '{$D004_Id}',
                '{$T030_Id}'
            )
EOT;
        mysql_query($cClausula);
        $cErro = mysql_error();
        if (!empty($cErro)) {
            return "Ocorreu um erro: " . $cErro . "<br ></EOT>Por favor entre em contato com o suporte.";
        }
        
        return true;
    }

	 /**
     * atualizarEstoque
     *
     * @param int $T029_Id
     * @return true/string
     */
    public function atualizarEstoque($T029_Id)
    {
        global $g;
        $D048_Id = 1;
        
        $cSql_T029 = mysql_query("select * from T029 where T029_Id='{$T029_Id}'");
        $mT029     = mysql_fetch_array($cSql_T029);
        
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //-- VALIDACOES
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if ($mT029['T029_Flag_Ok'] != 'N') {
            return "Divergência de estoque já finalizada, operação não permitida";
        }
        
        $cSql_T030 = mysql_query("select T030_Id from T030 where T030_T029_Id='{$T029_Id}' and ifnull(T030_D009_Id,0)=0 and T030_Flag_Cancelada!='S'");
        if (mysql_num_rows($cSql_T030) > 0) {
            return "Existem itens não cadastrados.<br />VERIFIQUE!";
        }
        
        $cSql_T030 = mysql_query("select T030_Id from T030 where T030_T029_Id='{$T029_Id}' and ifnull(T030_D004_Id,0)=0 and T030_Flag_Cancelada!='S'");
        if (mysql_num_rows($cSql_T030) > 0) {
            return "Não foi informado a LOCAÇÃO de todos os produtos.<br />VERIFIQUE!";
        }

        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //-- VERIFICA SE TODOS OS ITENS ESTAO ATIVOS
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $sql = <<<EOT
            SELECT
                D001_Codigo_Produto,
                D002_Descricao_Produto
            FROM T030
            left join D009 on T030_D009_Id=D009_Id 
            left join D049 on D009_D049_Id=D049_Id 
            left join D001 on D049_D001_Id=D001_Id 
            left join D002 on D001_D002_Id=D002_Id 
            WHERE
                T030_T029_Id='{$T029_Id}' 
                AND D001_Flag_Ativo='N'
                AND T030_Flag_Cancelada!='S'
EOT;
        $cSql_T030 = mysql_query($sql);
        
        if (mysql_num_rows($cSql_T030) > 0) {
            while ($mT030 = mysql_fetch_array($cSql_T030)) {
                $mInativados[] = "{$mT030['D001_Codigo_Produto']} - {$mT030['D002_Descricao_Produto']}";
            }
            $Inativados = implode('<br />', $mInativados);
            return "PROCESSO NÃO PODE SER REALIZADO, POIS OS ITENS ABAIXO ESTÃO INATIVOS:<br /><br />$Inativados";
        }
        
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //-- VERIFICA SE TODOS OS ITENS TEM CUSTO PARA ALTERACAO
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $sql = <<<EOT
            SELECT
                D001_Codigo_Produto,
                D002_Descricao_Produto
            FROM T030
               left join D009 on T030_D009_Id=D009_Id 
            left join D049 on D009_D049_Id=D049_Id 
            left join D001 on D049_D001_Id=D001_Id 
            left join D002 on D001_D002_Id=D002_Id 
            WHERE 
                T030_T029_Id='{$T029_Id}' 
                AND T030_Valor_Custo_Unitario<0
                AND T030_Flag_Cancelada!='S'
                AND T030_Flag_Alterar_Custo='S'
EOT;
        $cSql_T030 = mysql_query($sql);
        
        if (mysql_num_rows($cSql_T030) > 0) {
            while ($mT030 = mysql_fetch_array($cSql_T030)) {
                $mInativados[] = "{$mT030['D001_Codigo_Produto']} - {$mT030['D002_Descricao_Produto']}";
            }
            $Inativados = implode('<br />', $mInativados);
            return "PROCESSO NÃO PODE SER REALIZADO, POIS OS ITENS ABAIXO ESTAO COM O CUSTO DE ALTERAÇÃO ABAIXO DE ZERO:<br /><br />$Inativados";
        }
        
        
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // VERIFICA A QUANTIDADE EM ESTOQUE DOS ITENS
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $sql = <<<EOT
            SELECT
                T030_Codigo_Produto,
                D001_Descricao_Produto
            FROM T030 
            left join D009 on D009_Id=T030_D009_Id
            left join T066 on T066_Id=T030_T066_Id
            left join D049 on D049_Id=D009_D049_Id
            left join D001 on D001_Id=D049_D001_Id
            WHERE
                T030_T029_Id='{$T029_Id}' 
                and T030_Flag_Entrada_Saida='S'
                and T030_Flag_Cancelada!='S'
                and T066_Quantidade_Estoque<T030_Quantidade
EOT;
        $cSql_T030 = mysql_query($sql);
        if (mysql_num_rows($cSql_T030) > 0) {
            while ($mT030 = mysql_fetch_array($cSql_T030)) {
                $mInativados[] = "{$mT030['T030_Codigo_Produto']} - {$mT030['D001_Descricao_Produto']}";
            }
            $Inativados = implode('<br />', $mInativados);
            return "EXISTEM ITENS COM ESTOQUE INSUFICIENTE<br /><br />{$Inativados}";
        }
        
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // VERIFICA A QUANTIDADE EM ESTOQUE DOS ITENS
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $sql = <<<EOT
            SELECT
                T030_Codigo_Produto
            FROM T030
            left join D009 on D009_Id=T030_D009_Id
            WHERE
                T030_T029_Id='{$T029_Id}'
                AND (T030_Flag_Entrada_Saida='S' OR T030_Flag_Entrada_Saida='E')
                AND T030_Flag_Cancelada!='S'
                AND T030_Quantidade=0
EOT;
        $cSql_T030 = mysql_query($sql);
        if (mysql_num_rows($cSql_T030) > 0) {
            return "PARA OPERAÇÃO CREDITAR OU DEBITAR ESTOQUE É NECESSÁRIO INFORMAR A QUANTIDADE. EXISTEM ITENS SEM QUANTIDADE INFORMADA";
        }
        
        // Busca os dados do usuario
        $resultado = mysql_query("select * from C007 where C007_Id='{$g['usuarioAtual']}'");
        $mUsuario  = mysql_fetch_array($resultado);
        
        if ($_SESSION['Modulo_NFe'] != 'S') {
            //-- VERIFICA SE O USUARIO TEM ACESSO A OPCAO DE CANCELAR PEDIDO PENDENTE
            $cSql_C030 = mysql_query("select * from C030 where C030_C029_Id=9 and C030_Id_Perfil='{$g['perfilUsuario']}'");
            if (mysql_num_rows($cSql_C030) == 0) {
                return "ACESSO NÃO DISPONIVEL";
            }
        }

        require_once("bibliotecas/classes/CAD002.php");
        $CAD002 = new CAD002();

        mysql_query("START TRANSACTION");
        
        $cClausula = "select * from T030 left join T066 on T066_Id=T030_T066_Id where T030_T029_Id='{$T029_Id}' and T030_Flag_Cancelada!='S'";
        $cSql_T030 = mysql_query($cClausula);
        $cErro     = mysql_error();
        if (!empty($cErro)) {
            mysql_query("ROLLBACK");
            return "Ocorreu um erro: " . str_replace(array("'",'"'), "", $cErro) . "<br />Clausula: " . str_replace(array("'",'"'), "", $cClausula);
        }
        
        while ($mT030 = mysql_fetch_array($cSql_T030)) {
            $cClausula = "select D049_D024_Id, D001_Codigo_Produto, D009_Id from D009 left join D049 on D049_Id=D009_D049_Id left join D001 on D049_D001_Id=D001_Id where D009_Id='{$mT030['T066_D009_Id']}'";
            $cSql_D009 = mysql_query("$cClausula");
            $cErro     = mysql_error();
            if (!empty($cErro)) {
                mysql_query("ROLLBACK");
                return "Ocorreu um erro: " . str_replace(array("'",'"'), "", $cErro) . "<br />Clausula: " . str_replace(array("'",'"'), "", $cClausula);
            }
            $mD009 = mysql_fetch_array($cSql_D009);

            if($mT030['T030_T030_Id_Transferencia'] > 0){
                // Busca o lote da locação que vai ser debitada
				$lote = mysql_query("SELECT IFNULL(T066A_Numero_Lote, '') AS T066A_Numero_Lote,
											T066A_Data_Validade
									   FROM T030
								  LEFT JOIN T066 ON T066_Id = T030_T066_Id
								  LEFT JOIN T066A ON T066_Id = T066A_T066_Id
								   	  WHERE T030_Id = '{$mT030['T030_T030_Id_Transferencia']}'");
				$mLote = mysql_fetch_array($lote);
				
                // Busca dados da locação que vai receber a quantidade
				$T066 = mysql_query("SELECT T066_D004_Id,
											T066_Quantidade_Estoque,
											T030_D009_Id
									   FROM T030
								  LEFT JOIN T066 ON T066_Id = T030_T066_Id
								  	  WHERE T030_Id = '{$mT030['T030_Id']}'");

				$mT066 = mysql_fetch_array($T066);

                // Se na locação debitada (origem) tiver numero lote, usa ele, se não cria um lote novo
				$numeroLote = ($mLote['T066A_Numero_Lote'] != '') ? $mLote['T066A_Numero_Lote'] : date("YmdHi") . rand(1000, 9999);  
				$tmpD004_Id = getD004($mT030['T030_T066_Id']);

				if ($mT066['T066_Quantidade_Estoque'] > 0) {
					//insere nova locação no produto para lançar o lote
					mysql_query("INSERT INTO T066 (T066_D009_Id, T066_D004_Id) VALUES ('{$mT066['T030_D009_Id']}', '{$tmpD004_Id}')");
					$T066_Id = $g['mysqlLastId'];                    
					mysql_query("INSERT INTO T066A (T066A_T066_Id, T066A_Numero_Lote, T066A_Data_Validade) VALUES ('{$T066_Id}','{$numeroLote}','{$mLote['T066A_Data_Validade']}')");
					mysql_query("UPDATE T030 SET T030_T066_Id = '{$T066_Id}' WHERE T030_Id = '{$mT030['T030_Id']}'");

					$mT030['T030_T066_Id'] = $T066_Id;
				} else {
					$T066A = mysql_query("SELECT T066A_Id FROM T066A WHERE T066A_T066_Id = '{$mT030['T030_T066_Id']}'");
					if(mysql_num_rows($T066A) > 0){
						mysqli_query("UPDATE T066A SET T066A_Numero_Lote = '{$numeroLote}', T066A_Data_Validade = '{$mLote['T066A_Data_Validade']}' WHERE T066A_T066_Id = '{$mT030['T030_T066_Id']}'");
					} else {
						mysqli_query("INSERT INTO T066A (T066A_T066_Id, T066A_Numero_Lote, T066A_Data_Validade) VALUES ('{$mT030['T030_T066_Id']}', '{$numeroLote}', '{$mLote['T066A_Data_Validade']}')");
					}
				}          
            }
            
            ////////////////////////////////////////////////////////////////////////////////////////////
            // ENCONTRA O ID DO T066
            ////////////////////////////////////////////////////////////////////////////////////////////
            /*$cClausula = "select T066_Id from T066 where T066_D004_Id='{$mT030['T030_D004_Id']}' and T066_D009_Id='{$mT030['T030_D009_Id']}'";
            $cSql_T066 = mysql_query("$cClausula");
            $cErro     = mysql_error();
            if (!empty($cErro)) {
                mysql_query("ROLLBACK");
                return "Ocorreu um erro: " . str_replace(array(
                    "'",
                    '"'
                ), "", $cErro) . "/pClausula: " . str_replace(array(
                    "'",
                    '"'
                ), "", $cClausula);
            }
            $mT066 = mysql_fetch_array($cSql_T066);*/
            
            ////////////////////////////////////////////////////////////////////////////////////////////
            // HISTORICO DO PRODUTO
            ////////////////////////////////////////////////////////////////////////////////////////////
            $Descricao_Operacao = "DE-{$T029_Id}";
            if ($mT030['T030_Flag_Alterar_Custo'] == 'S') {
                $Descricao_Operacao .= " (AC)";
            }
            
            $cClausula = <<<EOT
                insert into T001 ( 
                    T001_D024_Id_Cliente, 
                    T001_D024_Id, 
                    T001_T030_Id, 
                    T001_T029_Id, 
                    T001_D009_Id, 
                    T001_T066_Id, 
                    T001_C004_Id, 
                    T001_Codigo_Produto, 
                    T001_Data_Lancamento, 
                    T001_Descricao_Operacao, 
                    T001_Quantidade, 
                    T001_Flag_Complemento, 
                    T001_Flag_Operacao, 
                    T001_Valor_Preco_Unitario, 
                    T001_Observacao, 
                    T001_Valor_Custo_Unitario, 
                    T001_Flag_Atualiza_Custo, 
                    T001_Quantidade_Saldo, 
                    T001_D004_Id 
                ) values ( 
                    0, 
                    '{$mD009['D049_D024_Id']}', 
                    '{$mT030['T030_Id']}', 
                    '{$T029_Id}', 
                    '{$mD009['D009_Id']}', 
                    '{$mT030['T030_T066_Id']}', 
                    '{$mT030['T030_C004_Id']}', 
                    '{$mD009['D001_Codigo_Produto']}', 
                    current_date(), 
                    '{$Descricao_Operacao}', 
                    '{$mT030['T030_Quantidade']}', 
                    '', 
                    '{$mT030['T030_Flag_Entrada_Saida']}', 
                    '{$mT030['T030_Valor_Custo_Unitario']}', 
                    '', 
                    '{$mT030['T030_Valor_Custo_Unitario']}', 
                    '{$mT030['T030_Flag_Alterar_Custo']}',
                    0, 
                    '{$tmpD004_Id}' 
                )
EOT;
            mysql_query($cClausula);
            $T001_Id = $g['mysqlLastId'];
            
            $h = fopen("/tmp/logEstoqueDE", 'a');
            fwrite($h, "T029: $T029_Id - T030: {$mT030['T030_Id']} - T001: $T001_Id - Insert T001: $cClausula");
            fclose($h);

            $cErro   = mysql_error();
            if (!empty($cErro)) {
                mysql_query("ROLLBACK");
                return "Ocorreu um erro: " . str_replace(array("'",'"'), "", $cErro) . "<br ></EOT>Clausula: " . str_replace(array("'",'"'), "", $cClausula);
            }
            
            $T066_Id   = $mT030['T030_T066_Id'];

            // NÃO EXECUTAR MAIS ESTE SCRIPT QUE ALTERA OU CADASTRA A LOCACAO INDICADA NO DE
            /* 
            if ($mT066['T066_Id'] > 0) {
                $cClausula = "update T066 set T066_Locacao='{$mT030['T030_Locacao']}' where T066_Id='{$mT066['T066_Id']}'";
                mysql_query("$cClausula");
                $cErro = mysql_error();
                if (!empty($cErro)) {
                    mysql_query("ROLLBACK");
                    return "Ocorreu um erro: " . str_replace(array(
                        "'",
                        '"'
                    ), "", $cErro) . "/pClausula: " . str_replace(array(
                        "'",
                        '"'
                    ), "", $cClausula);
                }
            } else {
                $cClausula = <<<EOT
                    insert into T066 ( 
                        T066_D009_Id, 
                        T066_D048_Id, 
                        T066_Locacao 
                    ) values ( 
                        '{$mT030['T030_D009_Id']}', 
                        '{$D048_Id}', 
                        '{$mT030['T030_Locacao']}' 
                    )
EOT;
                mysql_query($cClausula);
                $T066_Id = $g['mysqlLastId'];
                $cErro   = mysql_error();
                if (!empty($cErro)) {
                    mysql_query("ROLLBACK");
                    return "Ocorreu um erro: " . str_replace(array(
                        "'",
                        '"'
                    ), "", $cErro) . "/pClausula: " . str_replace(array(
                        "'",
                        '"'
                    ), "", $cClausula);
                }
            }
            */

            $CAD002->D001_reprocessa_historico(0, $mD009['D009_Id'], 100, true);
            $cClausula = <<<EOT
                insert into T186 ( 
                    T186_T066_Id, 
                    T186_T001_Id, 
                    T186_Quantidade 
                ) values ( 
                    '{$T066_Id}', 
                    '{$T001_Id}', 
                    '{$mT030['T030_Quantidade']}'
                )
EOT;
            mysql_query($cClausula);
            $cErro = mysql_error();
            if (!empty($cErro)) {
                mysql_query("ROLLBACK");
                return "Ocorreu um erro: " . str_replace(array("'",'"'), "", $cErro) . "<br ></EOT>Clausula: " . str_replace(array("'",'"'), "", $cClausula);
            }
        }
        
        $cClausula = "update T029 set T029_Flag_Ok='S' where T029_Id='{$T029_Id}'";
        mysql_query("$cClausula");
        $cErro = mysql_error();
        if (!empty($cErro)) {
            mysql_query("ROLLBACK");
            return "Ocorreu um erro: " . str_replace(array("'",'"'), "", $cErro) . "<br />Clausula: " . str_replace(array("'",'"'), "", $cClausula);
        }
        mysql_query("COMMIT");
        
        return true;
    }
}








