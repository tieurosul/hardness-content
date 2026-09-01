<?php
namespace hardness;
/**
* Alterar (sobre-escrever) Métodos de uma Classe
*/

class EST004 extends EST004_ {
	// defina os métodos para sobreescrever

    // Felipe Kadanos - 25/02/2026 - Melhoria EUROSUL FORNECEDO - 46468
    // Ao vincular OC no item, seta locação para PENDENTE
    public function popularOCNotaFiscal($T013_Id, $D024_Id)
    {
        global $g;

        // busca se o produto possui mais de uma locação
        $T014 = mysql_query("SELECT T014_Id, 
                                    D009_Id,
                                    T014_Valor_Custo_Unitario,
                                    T014_Quantidade,
                                    T014_Classificacao_Fiscal,
                                    T014_Descricao_Produto,
                                    T224_Id,
                                    T014_T225_Id
                               FROM T014
                          LEFT JOIN D009 ON T014_D009_Id=D009_Id
                          LEFT JOIN D049 ON D009_D049_Id=D049_Id
                          LEFT JOIN D001 ON D049_D001_Id=D001_ID
                          LEFT JOIN T013 ON T013_Id=T014_T013_Id
                          LEFT JOIN T224 ON T224_Id=TRIM(LEADING 0 FROM TRIM(REGEX_REPLACE('[^0-9 ]','',T014_Numero_Pedido_Compra))) AND T224_D024_Id=T013_D024_Id
                              WHERE T014_T013_Id='$T013_Id'");
        
        $erro=mysql_error();
        if (!empty($erro)) {
            return "Erro: $erro";
        }

        $T225_Sql ="select D009_Id,
                                    T225_Valor_Preco_Sem_Desconto_Unitario,
                                    T225_Quantidade_Pendente_3(T225_Id) as pendente,
                                    T225_D006_Id,
                                    T225_Id,
                                    T224_Id,
                                    T014_Quantidade,
                                    D005_Classificacao_Fiscal,
                                    D001_Descricao_Produto
                               from T225    
                          left join T224 on T224_Id=T225_T224_Id
                          left join D009 on T225_D009_Id=D009_Id
                          left join D049 on D049_Id=D009_D049_Id
                          left join D082 on D082_Id=D049_D082_Id
                          left join D001 on D049_D001_Id=D001_Id
                          left join D083 on D049_Id=D083_D049_Id and D083_D024_Id='{$D024_Id}'
                          left join T014 on T014_T225_Id=T225_Id
                          left join C007 on C007_Id=T224_C007_Id_Vendedor_Interno
                          left join D005 on D005_Id=T225_D005_Id
                              where T224_C004_Id='{$g['empresaAtual']}'
                                and D009_C004_Id='{$g['empresaAtual']}'
                                and (T224_Flag_Status_Liberacao = '2')
                                and T224_Flag_Status != '10'
                                and T225_Flag_Cancelada!='S'
                                and T224_D024_Id='{$D024_Id}'
                                and (ifnull(T225_Quantidade_Pendente_3(T225_Id),0) > 0 OR ifnull(T014_Flag_Divergencia,'N') = 'S')
                                and T224_Data_Emissao>=date_sub(current_date(), interval 6 month)
                           group by T225_Id
                           order by T224_Id,T225_Descricao_Produto";

        $erro=mysql_error();
        if (!empty($erro)) {
            return "Erro: $erro";
        }

        $erros = '';

        require_once('bibliotecas/classes/GFormWrap.php');
        while ($mT014 = mysql_fetch_array($T014)) {
            if ($mT014['T014_T225_Id']>0) {
                // Já possui vinculo com uma OC
                continue;
            }
            $T225 = mysql_query($T225_Sql);
            while ($mT225 = mysql_fetch_array($T225)) {
                if ($mT014['D009_Id']<=0) {
                    if ($mT014['T014_Classificacao_Fiscal']==$mT225['D005_Classificacao_Fiscal']){
                        if (gCorrigeNumero($mT014['T014_Valor_Custo_Unitario'])==gCorrigeNumero($mT225['T225_Valor_Preco_Sem_Desconto_Unitario'])){
                            if (gCorrigeNumero($mT014['T014_Quantidade'])==gCorrigeNumero($mT225['pendente'])){
                                similar_text($mT014['T014_Descricao_Produto'],$mT225['D001_Descricao_Produto'],$percentualDescricao);
                                if ($percentualDescricao>50){
                                    $form = new GFormWrap(array('D009_Id' => $mT225['D009_Id'], 'T014_Id' => $mT014['T014_Id']));
                                    $retorno = $this->associarProduto($form);
                                    if ($retorno!==true){
                                        $erros .= "Erro ao vincular produto $retorno";
                                    } else {
                                        $mT014['D009_Id'] = $mT225['D009_Id'];
                                    }
                                }
                            }
                        }
                    }
                }
                if ($mT014['D009_Id'] == $mT225['D009_Id']) {
                    // Verifica se existe OC no XML
                    if (!empty($mT014['T224_Id']) && $mT014['T224_Id']!=$mT225['T224_Id']) {
                        continue;
                    }
                    // Associa o item da nota com o item da OC
                    if (($mT014['T014_Quantidade']<=$mT225['pendente']) || ($g['C031']['vincularProdutoNFRecebidaOCQtdDiferentes']=="S")){
                        if($g['C031']['associarCfopOcParaNf'] == 'S'){
                            mysql_query("UPDATE T014 SET T014_T225_Id='{$mT225['T225_Id']}', T014_Flag_Divergencia='S', T014_D006_Id = '{$mT225['T225_D006_Id']}' WHERE T014_Id='{$mT014['T014_Id']}'");
                        } else {
                            mysql_query("UPDATE T014 SET T014_T225_Id='{$mT225['T225_Id']}', T014_Flag_Divergencia='S' WHERE T014_Id='{$mT014['T014_Id']}'");
                        }
                        mysql_query("UPDATE T225 SET T225_Quantidade_Recebida='{$mT014['T014_Quantidade']}' WHERE T225_Id='{$mT225['T225_Id']}'");
                        
                        // Felipe Kadanos - 25/02/2026 - Melhoria EUROSUL FORNECEDO - 46468
                        // Seta a locação do item para a que possui a T066_D004_Id = 1882
                        $T066 = mysql_query("SELECT T066_Id FROM T066 WHERE T066_D004_Id = '1882' AND T066_D009_Id = '{$mT014['D009_Id']}'");
                        $loc = mysql_fetch_array($T066);
                        if($loc['T066_Id'] > 0){
                            mysql_query("UPDATE T014 SET T014_T066_Id = '{$loc['T066_Id']}' WHERE T014_Id='{$mT014['T014_Id']}'");
                            if ($erro = mysql_error()) {
                                $erros .= "Erro ao atualizar locação (D004=1882) do item {$mT014['T014_Id']}: {$erro}\n"; break 2;
                            }
                        } else {
                            // Se não encontrou com D004_Id = 1882, tenta buscar localização com D004_Local = 'PENDENTE'
                            $T066_Pendente = mysql_query("SELECT T066_Id FROM T066 LEFT JOIN D004 ON D004_Id = T066_D004_Id WHERE T066_D009_Id = '{$mT014['D009_Id']}' AND D004_Local = 'PENDENTE'");
                            $loc_pendente = mysql_fetch_array($T066_Pendente);
                            if($loc_pendente['T066_Id'] > 0){
                                mysql_query("UPDATE T014 SET T014_T066_Id = '{$loc_pendente['T066_Id']}' WHERE T014_Id='{$mT014['T014_Id']}'");
                                if ($erro = mysql_error()) {
                                    $erros .= "Erro ao atualizar locação (PENDENTE) do item {$mT014['T014_Id']}: {$erro}\n"; break 2;
                                }
                            } else {
                                // Se não encontrou, cria uma nova locação com D004_Local = 'PENDENTE'
                                $ins = "INSERT INTO T066 (T066_D004_Id, T066_D009_Id) VALUES ('1882', '{$mT014['D009_Id']}')";
                                mysqli_query($ins);
                                if ($erro = mysql_error()) {
                                    $erros .= "Erro ao inserir locação (PENDENTE) do item {$mT014['T014_Id']}: {$erro}\n"; break 2;
                                }
                                mysql_query("UPDATE T014 SET T014_T066_Id = '{$g['mysqlLastId']}' WHERE T014_Id='{$mT014['T014_Id']}'");
                                if ($erro = mysql_error()) {
                                    $erros .= "Erro ao atualizar locação (PENDENTE) do item {$mT014['T014_Id']}: {$erro}\n"; break 2;
                                }
                            }
                        }
                        //                        
                        break;
                    }
                }
             }
             mysql_data_seek($T225, 0);
        }
        
        if (!empty($erros)){
            return "Erros $erros";
        } else {
            return true;
        }

    }

	    /**
     * est004ConcluirInclusaoItensNovo
     *
     * @param int $T013_Id
     * @param string $extraSql
     * @return true/string
     */
    public function est004ConcluirInclusaoItensNovo($T013_Id, $extraSql)
    {
        global $g;
        $extra = base64_decode($extraSql);
        
        if (empty($T013_Id)) {
            return 'T013_Id não recebido';
        }
        
        //-- CONSULTA DA NOTA FISCAL
        $T013 = mysql_query("select T013_Flag_Travar_Nota,
                                    T013_D024_Id,
                                    T013_C004_Id
                               from T013  
                          left join C004 on C004_Id=T013_C004_Id 
                              where T013_Id='$T013_Id'");

        $mT013 = mysql_query($T013);
        $erro     = mysql_error();
        if (!empty($erro)) {
            return $erro;
        }
        $mT013 = mysql_fetch_array($T013);
        
        //-- VERIFICA SE A NOTA FISCAL PODE SER ALTERADA
        if ($mT013['T013_Flag_Travar_Nota'] == 'S') {
            return "Nota fiscal já foi finalizada";
        }
        
        $D024 = mysql_query("select D018_Id,
                                    D018_UF
                               from D024
                          left join D020 on D020_Id=D024_D020_Id
                          left join D018 on D018_Id=D020_D018_Id
                              where D024_Id='{$mT013['T013_D024_Id']}'");
        $D024 = mysql_query($D024);
        $mD024     = mysql_fetch_array($D024);
        
        $T225 = mysql_query("select T224_C004_Id
                               from T013 
                          left join T014 on T014_T013_Id=T013_Id
                          left join T225 on T225_Id=T014_T225_Id
                          left join T224 on T224_Id=T225_T224_Id
                          left join D009 on D009_Id=T225_D009_Id
                              WHERE T013_Id='$T013_Id'
                                AND (T225_C004_Id!='{$mT013['T013_C004_Id']}'
                                 OR T224_C004_Id!='{$mT013['T013_C004_Id']}')");

        $mT225 = mysql_query($T225);
        $erro = mysql_error();
        if (!empty($erro)) {
            return $erro;
        }
        
        if (mysql_num_rows($T225) > 0) {
            return "Existem itens de outras empresas na oc, por favor avise o suporte técnico";
        }
        
        $extraNovo = gInsertExtraWhere($extra, "T225_Quantidade_Recebida>0");
        
        $T225 = mysql_query("SELECT T014_Id,
                                    T014_Valor_Total_Custo,
                                    T014_Valor_Total_Desconto,
                                    T014_Valor_Total_Preco_Recebida,
                                    T014_Valor_ICMS,
                                    T014_Valor_ICMS_Recebida,
                                    T014_Valor_IPI,
                                    T014_Valor_IPI_Recebida,
                                    T014_Valor_ICMS_Substituicao,
                                    T014_Valor_ICMS_Substituicao_Retencao_Recebida,
                                    T014_Valor_Custo_Final_Unitario,
                                    T014_Valor_Custo_Unitario_Recebida,
                                    D005_Classificacao_Fiscal,
                                    T014_Classificacao_Fiscal,
                                    T225_Flag_Autorizado,
                                    T014_Codigo_Produto,
                                    T225_Id,
                                    T225_D006_Id,
                                    T224_C004_Id,
                                    T225_D009_Id,
                                    D001_Codigo_Produto,
                                    D083_Codigo_Produto_Fornecedor,
                                    D001_Descricao_Produto,
                                    T225_Quantidade_Recebida,
                                    T225_Valor_Custo_Unitario,
                                    T225_Aliquota_IPI,
                                    T225_Aliquota_ICMS,
                                    T225_Aliquota_PIS,
                                    T225_Aliquota_COFINS,
                                    T225_Valor_Total_Custo,
                                    T225_Valor_Custo_Final_Unitario,
                                    T225_Valor_Total_Custo_Final,
                                    T225_Valor_Desconto_Unitario,
                                    T225_Valor_Total_Desconto,
                                    T225_D005_Id,
                                    T225_Valor_Frete_Unitario,
                                    T225_Valor_Despesas_Acessorias_Unitario,
                                    T225_Percentual_Reducao_ICMS,
                                    T225_Valor_Base_ICMS,
                                    T225_Valor_ICMS,
                                    T225_Valor_ICMS_Substituicao,
                                    T225_Percentual_Reducao_IPI,
                                    T225_Valor_Base_IPI,
                                    T225_Valor_IPI,
                                    T225_D037_Id,
                                    T225_Modalidade_ICMS_Substituicao,
                                    T225_Modalidade_ICMS,
                                    T225_ICMS_Inter_Estadual,
                                    T225_Situacao_Tributaria_IPI,
                                    T225_Valor_Base_PIS,
                                    T225_Valor_PIS,
                                    T225_Flag_Isento_PIS,
                                    T225_Valor_Base_ICMS_Substituicao,
                                    T225_Situacao_Tributaria_COFINS,
                                    T225_Flag_Situacao_Tributaria,
                                    T225_Flag_Isento_COFINS,
                                    T225_Valor_Base_COFINS,
                                    T225_Valor_COFINS,T224_Id,
                                    (SELECT SUM(T014_Valor_ICMS_Recebida) FROM T225 T225B left join T014 T014B on T014B.T014_T225_Id=T225B.T225_Id WHERE T014B.T014_Id=T014.T014_Id OR T014B.T014_Id_Duplicado=T014.T014_Id OR T014B.T014_Id=T014.T014_Id_Duplicado OR T014B.T014_Id_Duplicado=T014.T014_Id_Duplicado) as T014_Valor_ICMS_Recebida,
                                    (SELECT SUM(T014_Valor_IPI_Recebida) FROM T225 T225B left join T014 T014B on T014B.T014_T225_Id=T225B.T225_Id WHERE T014B.T014_Id=T014.T014_Id OR T014B.T014_Id_Duplicado=T014.T014_Id OR T014B.T014_Id=T014.T014_Id_Duplicado OR T014B.T014_Id_Duplicado=T014.T014_Id_Duplicado) as T014_Valor_IPI_Recebida,
                                    (SELECT SUM(T014_Valor_ICMS_Substituicao_Retencao_Recebida) FROM T225 T225B left join T014 T014B on T014B.T014_T225_Id=T225B.T225_Id WHERE T014B.T014_Id=T014.T014_Id OR T014B.T014_Id_Duplicado=T014.T014_Id OR T014B.T014_Id=T014.T014_Id_Duplicado OR T014B.T014_Id_Duplicado=T014.T014_Id_Duplicado) as T014_Valor_ICMS_Substituicao_Retencao_Recebida
                                    FROM T014
                                    {$extraNovo}");

        $erro = mysql_error();
        if (!empty($erro)) {
            return $erro;
        }
        if (mysql_num_rows($T225) <= 0) {
            return "Não existem itens vinculados da NF vinculados com a OC.";
        }
        
        $count = array(
            'ok' => 0,
            'erro' => 0
        );

        mysql_query("SET AUTOCOMMIT=0");
        mysql_query("START TRANSACTION");
      

        while ($mT225 = mysql_fetch_array($T225)) {
            /**
             * Se a nota veio de um XML, verifica se tem algum produto para importar que já tenha sido importado pelo XML e apenas associa.
             */
            //if($mT013['T013_Chave_Acesso_Nfe'] != ""){
            $difTotalPreco = abs($mT225['T014_Valor_Total_Custo'] - $mT225['T014_Valor_Total_Desconto'] - $mT225['T014_Valor_Total_Preco_Recebida']);
            $difTotalICMS  = abs($mT225['T014_Valor_ICMS'] - $mT225['T014_Valor_ICMS_Recebida']);
            $difTotalIPI   = abs($mT225['T014_Valor_IPI'] - $mT225['T014_Valor_IPI_Recebida']);
            $difTotalST    = abs($mT225['T014_Valor_ICMS_Substituicao'] - $mT225['T014_Valor_ICMS_Substituicao_Retencao_Recebida']);
            $difCusto      = abs($mT225['T014_Valor_Custo_Final_Unitario'] - $mT225['T014_Valor_Custo_Unitario_Recebida']);
            $difNCM        = '';
            if ($mT225['D005_Classificacao_Fiscal'] != $mT225['T014_Classificacao_Fiscal']) {
                $difNCM = 'NCM Divergente';
            }
            $toleranciaDiferencaOCNF = str_replace(",", ".", $g['C031']['toleranciaDiferencaOCNF']);
            
            if ($mT225['T225_Flag_Autorizado'] != 'S' and ($difTotalPreco > $toleranciaDiferencaOCNF or (($difTotalICMS > $toleranciaDiferencaOCNF or $difTotalIPI > $toleranciaDiferencaOCNF or $difTotalST > $toleranciaDiferencaOCNF or !empty($difNCM) or $difCusto > $toleranciaDiferencaOCNF))) && $g['C031']['verificarImpostosNotaRecebida'] == 'S' ) {
                
                mysql_query("UPDATE T014 SET T014_Flag_Divergencia='S', T014_Observacao_Divergencia='Diferenca total preco: $difTotalPreco<br>Diferença total ICMS: $difTotalICMS<br>Diferença total IPI: $difTotalIPI<br>Diferença total ST: $difTotalST<br>Diferença total IPI: $difTotalIPI <br> $difNCM' WHERE T014_Id='{$mT225['T014_Id']}'");
                $count['erro']++;
                
            } else {
                
                mysql_query("UPDATE T014 SET T014_Flag_Divergencia='N',T014_Observacao_Divergencia='' WHERE T014_Id='{$mT225['T014_Id']}'");
                
                mysqli_query("UPDATE T225 SET T225_Quantidade_Recebida=0, T225_Flag_OC_Importada = 'S' WHERE T225_Id='{$mT225['T225_Id']}'");
                $count['ok']++;

            }
            $erro = mysql_error();
            if (!empty($erro)) {
                mysql_query("ROLLBACK");
                return $erro;
            }
            
            mysql_query("call T014_Gravar_Totalizacao({$mT225['T014_Id']})");
            $erro = mysql_error();
            if (!empty($erro)) {
                mysql_query("ROLLBACK");
                return $erro;
            }
            
            mysql_query("call T224_Gravar_Totalizacao_3({$mT225['T224_Id']})");
            $erro = mysql_error();
            if (!empty($erro)) {
                mysql_query("ROLLBACK");
                return $erro;
            }
            
        }
        
        mysql_query("COMMIT");
        
        return $count;
    }

    /**
     * finalizarNotaEstoque
     *
     * @param int $T013_Id
     * @return true/string
     */
     // Melhoria atualizar numero lote
    public function finalizarNotaEstoque($T013_Id)
    {
        global $g;
        //-- Conexão com o banco de dados
        //require "old/lib/gravar_entidade.php3";

        //-- INCLUSAO DA CLASSE DE PROCESSAMENTOS
        //require "old/lib/processamentos.class.php3";

        // Classe produtos
        require_once("bibliotecas/classes/CAD002.php");
        $CAD002 = new CAD002();

        if (empty($T013_Id)) {
            return "Erro: T013_Id não encontrado.";
        }

        mysql_query("SET AUTOCOMMIT=0");
        mysql_query("START TRANSACTION");

        $cSql_T013 = mysql_query("select * 
                                from T013 
                           left join D006 on D006_Id=T013_D006_Id
                           left join D024 on D024_Id=T013_D024_Id
                               where T013_Id='$T013_Id'");
        $mT013     = mysql_fetch_array($cSql_T013);

        if ($mT013['T013_Flag_Travar_Nota'] == 'N' or empty($mT013['T013_Flag_Travar_Nota'])) {
            return "É necessário finalizar compras antes de finalizar o estoque";
        }

        /*
        if ($mT013['T013_Flag_Financeiro_Finalizado']=='N' or empty($mT013['T013_Flag_Financeiro_Finalizado'])){
        return "É necessário finalizar o financeiro antes de finalizar o estoque";  
        }
        */

        if ($mT013['T013_Flag_Estoque_Finalizado'] == 'S') {
            return "Esta nota já foi finalizada no estoque. Não é possível executar novamente esse processo!";
        }

        // Felipe Kadanos - 12/03/2026 - EUROSUL FORNECEDO - 46468
        // Personalizado para se a Nota estiver vinculada a uma OC seja obrigado as dimensões estarem preenchidas
            $sql = "SELECT 
                        T014_Id,
                        D001_Codigo_Produto
                    FROM T014
                        LEFT JOIN D009 ON D009_Id = T014_D009_Id
                        LEFT JOIN D049 ON D049_Id = D009_D049_Id
                        LEFT JOIN D001 ON D001_Id = D049_D001_Id
                        LEFT JOIN D001A ON D001_Id = D001A_D001_Id
                        LEFT JOIN T225 ON T225_Id = T014_T225_Id
                        LEFT JOIN D006 ON D006_Id = T014_D006_Id
                    WHERE
                        T014_T013_Id = '{$T013_Id}'
                        AND D006_Flag_Estoque != 'N'
                        AND IF(T014_T225_Id > 0, 
                                (IFNULL(D001A_Altura, 0) <= 0 OR IFNULL(D001A_Largura, 0) <= 0 OR IFNULL(D001A_Comprimento, 0) <= 0 OR IFNULL(D001_Peso_Unitario_Kg, 0) <= 0), 
                                (1=0)
                            )";

            $sql = mysqli_query($sql);
            if (mysql_num_rows($sql) > 0) {
                $pdts = '';
                while ($row = mysql_fetch_assoc($sql)) {
                    $pdts .= $row['D001_Codigo_Produto'] . ', ';
                }
                return "A nota foi vinculada a uma OC, por esse motivo é necessário preencher<br>a <b>Altura</b>, <b>Largura</b>, <b>Comprimento</b> e <b>Peso Unitário</b> do(s) produto(s): " . rtrim($pdts, ', ');
            }
        //

        ///////////////////////////////////////////////////////////////////////////////////
        //-- LANCAMENTO NO HISTORICO E NO ESTOQUE
        ///////////////////////////////////////////////////////////////////////////////////
        $cSql_T014 = mysql_query("SELECT T014_Id,
                                        T014_Id_Duplicado,
                                        T014_T013_Id,
                                        T014_D009_Id,
                                        T014_Quantidade,
                                        D006_Flag_Estoque,
                                        T014_Valor_Custo_Unitario,
                                        T014_Valor_Desconto_Unitario,
                                        T014_Aliquota_ICMS,
                                        T014_Valor_ICMS,
                                        D006_Flag_Credita_IPI,
                                        T014_Aliquota_IPI,
                                        T014_Valor_IPI,
                                        D006_Flag_Substituicao_Tributaria,
                                        D006_Flag_Venda_Compra_Outros,
                                        T014_Valor_ICMS_Substituicao_Unitario,
                                        T014_Valor_Frete,
                                        T014_Valor_Seguro,
                                        T014_Valor_Despesas_Acessorias,
                                        T014_Valor_Custo_Final_Unitario,
                                        D049_Flag_Nacional_Importado,
                                        T014_Percentual_Reducao_ICMS,
                                        T014_Valor_Base_ICMS,
                                        T014_Percentual_Reducao_IPI,
                                        T014_Valor_Base_IPI,
                                        T014_Valor_ICMS_Substituicao,
                                        T014_Valor_PIS,
                                        T014_Valor_COFINS,
                                        D001_Codigo_Produto,
                                        T014_Valor_ICMS_Diferenca_Aliquotas,
                                        T014_Quantidade,
                                        T014_Aliquota_ICMS_Diferenca_Aliquotas,
                                        T014_T066_Id,
                                        T066_Quantidade_Estoque,
                                        T014_C004_Id,
                                        T014_D006_Id,
                                        T014_Codigo_Produto,
                                        T014_Quantidade,
                                        D006_Flag_Atualiza_Custo,
                                        D006_Flag_Devolucao,
                                        T014_Aliquota_PIS,
                                        T014_Aliquota_COFINS,
                                        T014_Flag_Origem_Produto,
                                        D049_Id,
                                        T014_Codigo_Produto_Fornecedor,
                                        T014A_Valor_Custo_Final_Liquido_Unitario,
                                        T014A_Valor_Acrescimo_Custo,
                                        T014A_Ficha_Conteudo_Importacao
                                    from T014
                              left join D009 on D009_Id=T014_D009_Id
                              left join D049 on D049_Id=D009_D049_Id
                              left join D001 on D001_Id=D049_D001_Id
                              left join D006 on D006_Id=T014_D006_Id
                              left join T001 on T001_T014_Id=T014_Id
                              left join T066 on T066_Id=T014_T066_Id
                              left join T014A ON T014_Id=T014A_T014_Id
                                  where T014_T013_Id='$T013_Id'
                                    and T001_Id IS NULL");
                                     //and D006_Flag_Estoque!='N'

        $D006_Flag_Estoque = "N";
        while ($mT014 = mysql_fetch_array($cSql_T014)) {
            //Verifica se o item foi duplicado na entrada da nota fiscal, caso o item tenha sido duplicado, o calculo unitário dos impostos deve ser feito sobre a quantidade total do produto na nota fiscal
            // !!! Se o item já veio duplicado na nota fiscal (XML), o valor unitario do impostos não precisam ser calculados sobre a quantidade total do produto na nota fiscal !!!
            $sqlItemDuplicado = mysql_query("SELECT T014_Id FROM T014 WHERE T014_Id_Duplicado = '{$mT014['T014_Id']}'");
            if(mysql_num_rows($sqlItemDuplicado) > 0 || $mT014['T014_Id_Duplicado'] > 0){
                $sqlQtdSoma = "SELECT SUM(T014_Quantidade) as T014_Quantidade FROM T014 WHERE T014_T013_Id = '{$mT014['T014_T013_Id']}' AND T014_D009_Id = '{$mT014['T014_D009_Id']}'";
                $resQtdSoma = mysql_query($sqlQtdSoma);
                $mQtdSoma = mysql_fetch_assoc($resQtdSoma);
                $QtdSoma = $mQtdSoma['T014_Quantidade'];
            } else {
                $QtdSoma = $mT014['T014_Quantidade'];
            }

            ///////////////////////////////////////////////////////////////////////////////////
            // ENCONTRA O ID DO T066
            ///////////////////////////////////////////////////////////////////////////////////
            $cSql_T066 = mysql_query("select T066_Id from T066 where T066_D048_Id='$D048_Id' and T066_D009_Id='{$mT014['T014_D009_Id']}'");
            $mT066     = mysql_fetch_array($cSql_T066);

            ///////////////////////////////////////////////////////////////////////////////////
            // MOVIMENTA��O DO ESTOQUE
            ///////////////////////////////////////////////////////////////////////////////////
            if ($mT014['D006_Flag_Estoque'] == 'D') {
                $Flag_Operacao_Historico = "S";
                $D006_Flag_Estoque       = "S";
            } elseif ($mT014['D006_Flag_Estoque'] == 'C') {
                $Flag_Operacao_Historico = "E";
                $D006_Flag_Estoque       = "S";
            } elseif ($mT014['D006_Flag_Estoque'] == 'N') {
                $Flag_Operacao_Historico = "N";
            }

            $QtdSoma = ($QtdSoma < 0) ? 1 : $QtdSoma;
            unset($mDemonstrativo_Custo);
            $mDemonstrativo_Custo[] = " ,,Preço,," . number_format($mT014['T014_Valor_Custo_Unitario'], 2, ',', '.');
            $mDemonstrativo_Custo[] = "-,,Desconto nos Itens,," . number_format($mT014['T014_Valor_Desconto_Unitario'], 2, ',', '.');
            $mDemonstrativo_Custo[] = " ,,Preço Liquido,," . number_format($mT014['T014_Valor_Custo_Unitario'] - $mT014['T014_Valor_Desconto_Unitario'], 2, ',', '.');
            if ($mC004['C004_Flag_Empresa_Simples'] != '1') {
                $mDemonstrativo_Custo[] = "-,,{$mT014['T014_Aliquota_ICMS']}% ICMS,," . number_format($mT014['T014_Valor_ICMS'] / $QtdSoma, 2, ',', '.');
            }

            if ($mT014['D006_Flag_Credita_IPI'] != 'S') {
                $mDemonstrativo_Custo[] = "+,,{$mT014['T014_Aliquota_IPI']}% IPI,," . number_format($mT014['T014_Valor_IPI'] / $QtdSoma, 2, ',', '.');
            }
            if ($mT013['D006_Flag_Substituicao_Tributaria'] == 'S' && $mT013['D006_Flag_Venda_Compra_Outros'] == 'T' && $mT013['T013_C004_Id'] == 2) {
                $mDemonstrativo_Custo[] = " ,,ICMS Substituição,," . number_format($mT014['T014_Valor_ICMS_Substituicao_Unitario'], 2, ',', '.');
            } else {
                $mDemonstrativo_Custo[] = "+,,ICMS Substituição,," . number_format($mT014['T014_Valor_ICMS_Substituicao_Unitario'], 2, ',', '.');
            }
            $mDemonstrativo_Custo[] = "+,,Frete,," . number_format($mT014['T014_Valor_Frete'] / $QtdSoma, 2, ',', '.');
            $mDemonstrativo_Custo[] = "+,,Seguro,," . number_format($mT014['T014_Valor_Seguro'] / $QtdSoma, 2, ',', '.');
            $mDemonstrativo_Custo[] = "+,,Despesas Acessórias,," . number_format($mT014['T014_Valor_Despesas_Acessorias'] / $QtdSoma, 2, ',', '.');

            if($mT014['T014A_Valor_Acrescimo_Custo'] > 0){
                $mDemonstrativo_Custo[] = "+,,Acrescimo,," . number_format($mT014['T014A_Valor_Acrescimo_Custo'], 2, ',', '.');
            }
            $mDemonstrativo_Custo[] = "=,,Custo Final,," . number_format($mT014['T014_Valor_Custo_Final_Unitario'], 2, ',', '.');

            if ($mT014['D049_Flag_Nacional_Importado'] == 'I') {
                $mT014['T014_Valor_Custo_Final_Unitario'] = $mT014['T014_Valor_Custo_Final_Unitario'] / $mT013['T013_Valor_Dolar'];
                $mDemonstrativo_Custo[]                   = "=,,Dólar,," . number_format($mT013['T013_Valor_Dolar'], 4, ',', '.');
                $mDemonstrativo_Custo[]                   = "=,,Custo Final US,," . number_format($mT014['T014_Valor_Custo_Final_Unitario'], 2, ',', '.');
            }

            $mDemonstrativo_Custo[] = "";
            $mDemonstrativo_Custo[] = ",,% Redução ICMS,," . number_format($mT014['T014_Percentual_Reducao_ICMS'], 2, ',', '.');
            $mDemonstrativo_Custo[] = ",,Base ICMS,," . number_format($mT014['T014_Valor_Base_ICMS'] / $QtdSoma, 2, ',', '.');

            $mDemonstrativo_Custo[] = "";
            $mDemonstrativo_Custo[] = ",,% Redução IPI,," . number_format($mT014['T014_Percentual_Reducao_IPI'], 2, ',', '.');
            $mDemonstrativo_Custo[] = ",,Base IPI,," . number_format($mT014['T014_Valor_Base_IPI'] / $QtdSoma, 2, ',', '.');

            $Demonstrativo_Custo = implode("\n", $mDemonstrativo_Custo);

            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // HISTORICO DO PRODUTO
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // '{$mT066['T066_Id']}',

            $valorICMSUnitario     = $mT014['T014_Valor_ICMS'] / $QtdSoma;
            $valorIPIUnitario      = $mT014['T014_Valor_IPI'] / $QtdSoma;
            $valorSTUnitario       = $mT014['T014_Valor_ICMS_Substituicao'] / $QtdSoma;
            $valorFreteUnitario    = $mT014['T014_Valor_Frete'] / $QtdSoma;
            $valorSeguroUnitario   = $mT014['T014_Valor_Seguro'] / $QtdSoma;
            $valorDespesasUnitario = $mT014['T014_Valor_Despesas_Acessorias'] / $QtdSoma;
            $valorPISUnitario      = $mT014['T014_Valor_PIS'] / $QtdSoma;
            $valorCOFINSUnitario   = $mT014['T014_Valor_COFINS'] / $QtdSoma;
            $valorICMSUnitario     = empty($valorICMSUnitario) ? 0 : $valorICMSUnitario;
            $valorIPIUnitario      = empty($valorIPIUnitario) ? 0 : $valorIPIUnitario;
            $valorSTUnitario       = empty($valorSTUnitario) ? 0 : $valorSTUnitario;
            $valorFreteUnitario    = empty($valorFreteUnitario) ? 0 : $valorFreteUnitario;
            $valorSeguroUnitario   = empty($valorSeguroUnitario) ? 0 : $valorSeguroUnitario;
            $valorDespesasUnitario = empty($valorDespesasUnitario) ? 0 : $valorDespesasUnitario;
            $valorPISUnitario      = empty($valorPISUnitario) ? 0 : $valorPISUnitario;
            $valorCOFINSUnitario   = empty($valorCOFINSUnitario) ? 0 : $valorCOFINSUnitario;

            $percentualSTUnitario  = ($valorSTUnitario / ($mT014['T014_Valor_Custo_Unitario'] + $valorIPIUnitario))*100;
            $percentualSTUnitario  = ($percentualSTUnitario > 0) ? $percentualSTUnitario : 0;

            $sqlT001 = mysql_query("SELECT T001_Id FROM T001 WHERE T001_D009_Id = '{$mT014['T014_D009_Id']}' AND T001_T014_Id = '{$mT014['T014_Id']}' AND T001_Flag_Operacao != 'C'");
            if(mysql_num_rows($sqlT001) > 0){
                mysql_query("ROLLBACK");
                return "Erro ao inserir histórico do produto. O produto {$mT014['D001_Codigo_Produto']} já possui um registro no histórico para esta nota fiscal.";
            }

            $mT014['T014_Valor_ICMS_Diferenca_Aliquotas'] = $mT014['T014_Valor_ICMS_Diferenca_Aliquotas'] / $mT014['T014_Quantidade'];
            $mT014['T014_Aliquota_ICMS_Diferenca_Aliquotas'] = $mT014['T014_Aliquota_ICMS_Diferenca_Aliquotas'];

            $tmpD004_Id = getD004($mT014['T014_T066_Id']);
           
            // Se for uma NF de compra lança o numero do lote
            if($mT014['D006_Flag_Venda_Compra_Outros'] == 'C'){
                $numeroLote = date("YmdHi") . rand(1000, 9999);
                if ($mT014['T066_Quantidade_Estoque'] > 0) {
                    //insere nova locação no produto para lançar o lote
                    mysql_query("INSERT INTO T066 (T066_D009_Id, T066_D004_Id) VALUES ('{$mT014['T014_D009_Id']}', '{$tmpD004_Id}')");
                    $mT014['T014_T066_Id'] = $g['mysqlLastId'];                    
                    mysql_query("INSERT INTO T066A (T066A_T066_Id, T066A_Numero_Lote) VALUES ('{$mT014['T014_T066_Id']}','{$numeroLote}')");
                    mysqli_query("UPDATE T014 SET T014_T066_Id = '{$mT014['T014_T066_Id']}' WHERE T014_Id = '{$mT014['T014_Id']}'");
                } else {
                    $T066A = mysql_query("SELECT T066A_Id FROM T066A WHERE T066A_T066_Id = '{$mT014['T014_T066_Id']}'");
                    if(mysql_num_rows($T066A) > 0){
                        mysqli_query("UPDATE T066A SET T066A_Numero_Lote = '{$numeroLote}' WHERE T066A_T066_Id = '{$mT014['T014_T066_Id']}'");
                    } else {
                        mysqli_query("INSERT INTO T066A (T066A_T066_Id,T066A_Numero_Lote) VALUES ('{$mT014['T014_T066_Id']}','{$numeroLote}' )");
                    }
                }
            }
            
            
            $T001_Id    = mysql_query("insert into T001 (T001_D024_Id,
                                                              T001_T014_Id,
                                                              T001_T013_Id,
                                                              T001_D009_Id,
                                                              T001_C004_Id,
                                                              T001_D006_Id,
                                                              T001_T066_Id,
                                                              T001_Codigo_Produto,
                                                              T001_Data_Lancamento,
                                                              T001_Descricao_Operacao,
                                                              T001_Quantidade,
                                                              T001_Flag_Operacao,
                                                              T001_Valor_Preco_Unitario,
                                                              T001_Valor_Custo_Unitario,
                                                              T001_Flag_Atualiza_Custo,
                                                              T001_Quantidade_Saldo,
                                                              T001_Demonstrativo_Custo,
                                                              T001_Flag_Devolucao,
                                                              T001_Valor_ST_Unitario,
                                                              T001_Valor_ICMS,
                                                              T001_Aliquota_ICMS,
                                                              T001_Valor_IPI,
                                                              T001_Aliquota_IPI,
                                                              T001_Valor_ST,
                                                              T001_Percentual_ST,
                                                              T001_Valor_Frete,
                                                              T001_Valor_Seguro,
                                                              T001_Valor_Despesas_Acessorias,
                                                              T001_Valor_Desconto_Unitario,
                                                              T001_Valor_ICMS_Diferenca_Aliquotas,
                                                              T001_Aliquota_PIS,
                                                              T001_Valor_PIS,
                                                              T001_Aliquota_COFINS,
                                                              T001_Valor_COFINS,
                                                              T001_D004_Id,
                                                              T001_Aliquota_ICMS_Diferenca_Aliquotas
                                                              )
                                                              values 
                                                              ('{$mT013['T013_D024_Id']}',
                                                              '{$mT014['T014_Id']}',
                                                              '{$mT013['T013_Id']}',
                                                              '{$mT014['T014_D009_Id']}',
                                                              '{$mT014['T014_C004_Id']}',
                                                              '{$mT014['T014_D006_Id']}',
                                                              '{$mT014['T014_T066_Id']}',
                                                              '{$mT014['T014_Codigo_Produto']}',
                                                              curdate(),
                                                              'NF-{$mT013['T013_Numero_Nota_Fiscal']}',
                                                              '{$mT014['T014_Quantidade']}',
                                                              '$Flag_Operacao_Historico',
                                                              '{$mT014['T014_Valor_Custo_Unitario']}',
                                                              '{$mT014['T014_Valor_Custo_Final_Unitario']}',
                                                              '{$mT014['D006_Flag_Atualiza_Custo']}',
                                                              '0',
                                                              '$Demonstrativo_Custo',
                                                              '{$mT014['D006_Flag_Devolucao']}',
                                                              '{$mT014['T014_Valor_ICMS_Substituicao_Unitario']}',
                                                              '{$valorICMSUnitario}',
                                                              '{$mT014['T014_Aliquota_ICMS']}',
                                                              '{$valorIPIUnitario}',
                                                              '{$mT014['T014_Aliquota_IPI']}',
                                                              '{$valorSTUnitario}',
                                                              '{$percentualSTUnitario}',
                                                              '{$valorFreteUnitario}',
                                                              '{$valorSeguroUnitario}',
                                                              '{$valorDespesasUnitario}',
                                                              '{$mT014['T014_Valor_Desconto_Unitario']}',
                                                              '{$mT014['T014_Valor_ICMS_Diferenca_Aliquotas']}',
                                                              '{$mT014['T014_Aliquota_PIS']}',
                                                              '{$valorPISUnitario}',
                                                              '{$mT014['T014_Aliquota_COFINS']}',
                                                              '{$valorCOFINSUnitario}',
                                                              '{$tmpD004_Id}',
                                                              '{$mT014['T014_Aliquota_ICMS_Diferenca_Aliquotas']}'
                                                              )");
            $erro = mysql_error();
            if (!empty($erro)) {
                mysql_query("ROLLBACK");
                return 'Erro ao inserir o histórico do produto. <br />Erro: '.$erro;
            }

            $T014_ID = $mT014['T014_Id'];
            $D009_ID = $mT014['T014_D009_Id'];
            $T066_ID = $mT014['T014_T066_Id'];
            
            $T001_Id = $g['mysqlLastId'];

            $T001_Id    = mysql_query("INSERT INTO T001A (T001A_T001_Id,
                                                          T001A_Valor_Custo_Unitario_Liquido,
                                                          T001A_Valor_Acrescimo_Custo
                                                  )VALUES(
                                                          '{$T001_Id}', 
                                                          '{$mT014['T014A_Valor_Custo_Final_Liquido_Unitario']}',
                                                          '{$mT014['T014A_Valor_Acrescimo_Custo']}')");

            if (strlen($mT014['T014_Flag_Origem_Produto']) > 0 and $mT014['D006_Flag_Venda_Compra_Outros'] == 'C' && $g['C031']['atualizarOrigemProdutoComNotaRecebida'] == 'S') {

                $origemProduto = $this->conversaoOrigemProdutoEntrada($mT014['T014_Flag_Origem_Produto']);

                mysql_query("UPDATE D049 LEFT JOIN D009 ON D009_D049_Id = D049_Id SET D049_Origem_Mercadoria = '{$origemProduto}'  WHERE D009_Id = '{$mT014['T014_D009_Id']}'");

                $erro = mysql_error();
                if (!empty($erro)) {
                    mysql_query("ROLLBACK");
                    return 'Erro ao finalizar o estoque da nota fiscal.';
                }
            }

            if($g['C031']['zeraCustoManualFinalizarEstoqueEntrada'] == 'S' && $mT014['D006_Flag_Atualiza_Custo'] == 'S'){
                mysqli_query("UPDATE D009 SET D009_Valor_Preco_Tabela = 0 WHERE D009_Id = '{$mT014['T014_D009_Id']}' AND D009_Valor_Preco_Tabela > 0");
            }

            if(!empty($mT014['T014A_Ficha_Conteudo_Importacao'])) {
                mysqli_query("UPDATE D009A SET D009A_Ficha_Conteudo_Importacao = '{$mT014['T014A_Ficha_Conteudo_Importacao']}' WHERE D009A_D009_Id = '{$mT014['T014_D009_Id']}'");
            }
        }

        // Verifica se vai escriturar no SPED Contribuições
        // Só não lança se todas as CFOPs dos produtos estiverem para não escriturar
        $sqlContribuicoes = mysql_query("SELECT T014_Id
                                           FROM T014
                                      LEFT JOIN D006 ON D006_Id = T014_D006_Id
                                          WHERE T014_T013_Id = '{$T013_Id}'
                                            AND D006_Flag_Escritura_Sped_Contribuicoes = 'S'
                                          LIMIT 1");
                                              
        if(mysql_num_rows($sqlContribuicoes) <= 0){
            mysqli_query("UPDATE T013A SET T013A_Flag_Escritura_Sped_Contribuicoes = 'N' WHERE T013A_T013_Id = '{$T013_Id}' AND T013A_Flag_Escritura_Sped_Contribuicoes != 'N'");
        } else {
            mysqli_query("UPDATE T013A SET T013A_Flag_Escritura_Sped_Contribuicoes = 'S' WHERE T013A_T013_Id = '{$T013_Id}' AND T013A_Flag_Escritura_Sped_Contribuicoes != 'S'");
        }

        mysql_query("UPDATE T013 set T013_Flag_Estoque_Finalizado='S', T013_Data_Entrada_Estoque = NOW() WHERE T013_Id='$T013_Id'");
        $erro = mysql_error();
        if (!empty($erro)) {
            mysql_query("ROLLBACK");
            return 'Erro ao finalizar o estoque da nota fiscal.';
        }

        $T014 = mysql_query("select T014_Id
                               from T014
                          left join D006 on D006_Id=T014_D006_Id
                          left join T001 on T001_T014_Id=T014_Id
                              where T014_T013_Id='$T013_Id'
                                and D006_Flag_Estoque!='N'
                                and (T001_Id IS NULL OR T001_Id='')");

        if(mysql_num_rows($T014)>0){
            mysql_query("ROLLBACK");
            return "Houve algum erro ao incluir o histórico dos produtos. Por favor, tente novamente. Se o problema persistir entre em contato com nosso suporte técnico";
        }

        mysql_query("COMMIT");
        mysql_query("SET AUTOCOMMIT=1");

        $cSql_T014 = mysql_query("SELECT T014_Id,
                                        T014_Id_Duplicado,
                                        T014_T013_Id,
                                        T014_D009_Id,
                                        T014_Quantidade,
                                        D006_Flag_Estoque,
                                        T014_Valor_Custo_Unitario,
                                        T014_Valor_Desconto_Unitario,
                                        T014_Aliquota_ICMS,
                                        T014_Valor_ICMS,
                                        D006_Flag_Credita_IPI,
                                        T014_Aliquota_IPI,
                                        T014_Valor_IPI,
                                        D006_Flag_Substituicao_Tributaria,
                                        D006_Flag_Venda_Compra_Outros,
                                        T014_Valor_ICMS_Substituicao_Unitario,
                                        T014_Valor_Frete,
                                        T014_Valor_Seguro,
                                        T014_Valor_Despesas_Acessorias,
                                        T014_Valor_Custo_Final_Unitario,
                                        D049_Flag_Nacional_Importado,
                                        T014_Percentual_Reducao_ICMS,
                                        T014_Valor_Base_ICMS,
                                        T014_Percentual_Reducao_IPI,
                                        T014_Valor_Base_IPI,
                                        T014_Valor_ICMS_Substituicao,
                                        T014_Valor_PIS,
                                        T014_Valor_COFINS,
                                        D001_Codigo_Produto,
                                        T014_Valor_ICMS_Diferenca_Aliquotas,
                                        T014_Quantidade,
                                        T014_Aliquota_ICMS_Diferenca_Aliquotas,
                                        T014_T066_Id,
                                        T014_C004_Id,
                                        T014_D006_Id,
                                        T014_Codigo_Produto,
                                        T014_Quantidade,
                                        D006_Flag_Atualiza_Custo,
                                        D006_Flag_Devolucao,
                                        T014_Aliquota_PIS,
                                        T014_Aliquota_COFINS,
                                        T014_Flag_Origem_Produto,
                                        D049_Id,
                                        T014_Codigo_Produto_Fornecedor,
                                        D001_Id,
                                        T014_Codigo_Barras,
                                        T014A_Codigo_Barras_Tributavel
                                    from T014
                              left join T014A on T014A_T014_Id = T014_Id
                              left join D009 on D009_Id=T014_D009_Id
                              left join D049 on D049_Id=D009_D049_Id
                              left join D001 on D001_Id=D049_D001_Id
                              left join D006 on D006_Id=T014_D006_Id
                              left join T001 on T001_T014_Id=T014_Id
                                  where T014_T013_Id='$T013_Id'");
                                     //and D006_Flag_Estoque!='N'

        $D006_Flag_Estoque = "N";
        while ($mT014 = mysql_fetch_array($cSql_T014)) {

            $sqlT225 = mysql_query("SELECT IFNULL(T219_T006_Id,0) as T219_T006_Id, 
                                                  T225_Id, 
                                                  T219_Id 
                                             FROM T014 
                                        LEFT JOIN T225 ON T225_Id=T014_T225_Id 
                                        LEFT JOIN T219 ON T219_Id=T225_T219_Id 
                                            WHERE T014_Id = '{$mT014['T014_Id']}'");
            $rowT225 = mysql_fetch_array($sqlT225);
            if(!empty($rowT225['T219_T006_Id'])){
                mysql_query("UPDATE T006 SET T006_T219_Id = '{$rowT225['T219_Id']}', T006_T225_Id = '{$rowT225['T225_Id']}' WHERE T006_Id = '{$rowT225['T219_T006_Id']}'");
                $erro = mysql_error();
                if (!empty($erro)) {
                    return 'Erro ao finalizar o estoque da nota fiscal.';
                }
            
                mysql_query("UPDATE T006 SET T006_T219_Id = '{$rowT225['T219_Id']}', T006_T225_Id = '{$rowT225['T225_Id']}' WHERE T006_T006_Id_Dividir = '{$rowT225['T219_T006_Id']}'");
                $erro = mysql_error();
                if (!empty($erro)) {
                    return 'Erro ao finalizar o estoque da nota fiscal.';
                }
            }
            //reprocessa histórico agora será executado no content em etapas - Lígia Nakazato
            //$CAD002->D001_reprocessa_historico(0, $mT014['T014_D009_Id']);
            
            mysql_query("UPDATE D049 SET D049_ICF=0, D049_Flag_ICF_Temporario='X' WHERE D049_Id='{$mT014['D049_Id']}' AND D049_Flag_ICF_Temporario='S'");
            $erro = mysql_error();
            if (!empty($erro)) {
                return 'Atualizando ICF Temporario';
            }
            
            /*
            $D083 = mysql_query("select D083_Id,D083_Flag_Fornecedor from D083 where D083_D001_Id='{$mT014['D049_D001_Id']}' and D083_Flag_Fornecedor='S' and D083_D049_Id='{$mT014['D049_Id']}' and D083_D024_Id='{$mT013['T013_D024_Id']}'");
            if (mysql_num_rows($D083) <= 0) {
                mysql_query("insert into D083 (D083_D049_Id,D083_D001_Id,D083_D024_Id,D083_Data_Cadastro,D083_Flag_Fornecedor,D083_Codigo_Produto_Fornecedor) values ('{$mT014['D049_Id']}','{$mT014['D049_D001_Id']}','{$mT013['T013_D024_Id']}',curdate(),'S','{$mT014['T014_Codigo_Produto_Fornecedor']}')");
            } else {
                $mD083 = mysql_fetch_array($D083);
                mysql_query("update D083 set D083_Flag_Fornecedor='S',D083_Codigo_Produto_Fornecedor='{$mT014['T014_Codigo_Produto_Fornecedor']}' where D083_Id='{$mD083['D083_Id']}'");
            }
            */

            
            /* Caso a opção em Estoque->Nota de Entrada->"Cadastrar GTIN com nota de entrada" estiver ativa
            ao importar um XML de nota de entrada verifica se o produto tem GTIN(cEAN e cEANTrib) se tiver substitui
            no cadastro do produto(D001_Codigo_Barras e D001_Codigo_Barras_Tributavel), se não mantém o que já existe no produto. */
            if($g['C031']['cadastrarGtinApartirDeNF'] == 'S' AND !empty($mT014['D001_Id']))
            {
                $GTIN_D001_Id = $mT014['D001_Id'];
                $cEAN = $mT014['T014_Codigo_Barras'];
                $cEANTrib = $mT014['T014A_Codigo_Barras_Tributavel'];

                $clausula_cean = array();
                if(!empty($cEAN))
                {
                    $clausula_cean[] = "D001_Codigo_Barras = '$cEAN'";
                }

                if(!empty($cEANTrib))
                {
                    $clausula_cean[] = "D001_Codigo_Barras_Tributavel = '$cEANTrib'";
                }

                if(count($clausula_cean) > 0)
                {
                    $clausula_cean = implode(",", $clausula_cean);

                    $cClausula = "UPDATE D001 SET $clausula_cean WHERE D001_Id = '$GTIN_D001_Id'"; 
                    mysql_query($cClausula);
                    if($cErro = mysql_error())
                    {
                        $cErro = "Erro Cadastro GTIN query $cErro";
                        log($cErro);
                        return "Erro: " . $cErro . "\n" . $cClausula;
                    }
                }
            }   
            //Já realiza o processo mais a baixo
            //$CAD002->definirCodigoFornecedor($mT014['T014_D009_Id'], $mT013['T013_D024_Id'], $mT014['T014_Codigo_Produto_Fornecedor'], '', false, true);
            
        }

        //////////////////////////////////////////////////////////////////
        //                                                              //
        //       ATUALIZA CLASSIFICACOES DA NOTA RECEBIDA               //
        //                                                              //
        //////////////////////////////////////////////////////////////////
        $Empresa_Grupo = 'N';
        // COMPRA
        if (($mT013['D006_Flag_Entrada_Saida'] == 'E') && ($mT013['D006_Flag_Devolucao'] != 'S') && (($mT013['D006_Flag_Venda_Compra_Outros'] == 'C') || ($mT013['D006_Flag_Venda_Compra_Outros'] == 'T')) && ($mT013['D006_Flag_Gera_Contas'] == 'P')) {
            $Classificacao = 1;
        }

        // DEVOLUCAO VENDA
        if (($mT013['D006_Flag_Entrada_Saida'] == 'E') && ($mT013['D006_Flag_Venda_Maquina'] != 'S') && ($mT013['D006_Flag_Devolucao'] == 'S') && ($mT013['D006_Flag_Venda_Compra_Outros'] == 'C') && ($mT013['D024_Flag_Mono'] != '1') && ($Empresa_Grupo == 'N')) {
            $Classificacao = 2;
        }

        if ($D006_Flag_Estoque == 'N') {
            //Verificação ocorre antes de chamar o metodo
            //$Msg .= "<br />Não foram gerados movimentaçães no estoque";
        }
        //atualiza os codigos dos forncedores

        $T014 = mysql_query("select T014_Codigo_Produto,
                                  D001_Codigo_Produto,
                                  D001_Id,
                                  D049_Id,
                                  D024_Id,
                                  T014_D009_Id
                             from T014
                        LEFT JOIN D009 ON D009_Id=T014_D009_Id
                        left join D049 on D049_Id=D009_D049_Id
                        left join D001 on D001_Id=D049_D001_Id
                        left join T013 on T013_Id=T014_T013_Id
                        left join D024 on D024_Id=T013_D024_Id
                        where T014_T013_Id='$T013_Id'
                          and T014_Codigo_Produto!=D001_Codigo_Produto
                         group by T014_Codigo_Produto");
        while ($mT014 = mysql_fetch_array($T014)) {
            /*
            $D083 = mysql_query("select D083_Id,D083_Flag_Cliente from D083 where D083_D001_Id='{$mT014['D001_Id']}' and D083_D049_Id='{$mT014['D049_Id']}' and D083_D024_Id='{$mT014['D024_Id']}'");
            if (mysql_num_rows($D083) <= 0) {
                mysql_query("insert into D083 (D083_D049_Id,D083_D001_Id,D083_D024_Id,D083_Data_Cadastro,D083_Flag_Fornecedor,D083_Codigo_Produto_Fornecedor) values ('{$mT014['D049_Id']}','{$mT014['D001_Id']}','{$mT014['D024_Id']}',curdate(),'S','{$mT014['T014_Codigo_Produto']}')");
            }
            */
            $CAD002->definirCodigoFornecedor($mT014['T014_D009_Id'], $mT014['D024_Id'], $mT014['T014_Codigo_Produto'], '', false, true);
        }

        if($mT013['T013_Flag_Finalidade_NFe']==1){
            // Cadastra os fornecedores dos produtos
            $T014 = mysql_query("SELECT D001_Id, 
                                        T013_D024_Id, 
                                        D006_Flag_Venda_Compra_Outros
                                   FROM T014 
                              LEFT JOIN D009 ON D009_Id=T014_D009_Id
                              LEFT JOIN D049 ON D049_Id=D009_D049_Id
                              LEFT JOIN D001 ON D001_Id=D049_D001_Id
                              LEFT JOIN T013 ON T013_Id=T014_T013_Id
                              LEFT JOIN T241 ON T241_D001_Id=D001_Id AND T241_D024_Id=T013_D024_Id
                              LEFT JOIN D006 ON T014_D006_Id=D006_Id
                                  WHERE T014_T013_Id = '{$T013_Id}'
                                    AND T241_Id IS NULL
                               GROUP BY D001_Id");
            while ($mT014 = mysql_fetch_array($T014)) {
                if($mT014['D006_Flag_Venda_Compra_Outros'] == 'C'){
                    $CAD002->cadastrarFornecedorProduto($mT014['D001_Id'], $mT014['T013_D024_Id']);
                }
            }
        }
        
        //-- PROCESSA AS PENDENCIAS
        // $Pendencias -> processarPendencias();
        if (!empty($Msg)) {
            return "Devido a Natureza da operação que foi selecionada," . $Msg;
        }

		$retornoAlterarOC = $this->alterarStatusOCVinculada($T013_Id);

        if($retornoAlterarOC!==true){
            return $retornoAlterarOC;
        }

        $this->atualizaLote($T013_Id);

        $alocarItensNaLocacao = $this->AlocarSaldoLocacao($D009_ID, $T014_ID, $T066_ID);

        return true;
    }
}









