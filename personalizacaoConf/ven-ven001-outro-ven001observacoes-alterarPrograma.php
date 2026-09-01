<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /ven/ven001/outro/ven001observacoes/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

        global $g;
        $T003_Id = isset($r_acaoId) ? $r_acaoId : false;
        $divAjuste = isset($r_divAjuste) ? $r_divAjuste : false;

        // Retirado o gravar _totalizacao dos produtos pois estava gerando muita lentidão ao carregar a tela de orçamentos
        // $cSql_T004=mysql_query("select T004_Id from T004 where T004_T003_Id='$T003_Id'");
        // while ($mT004=mysql_fetch_array($cSql_T004)) {
        //     mysql_query("call T004_Gravar_Totalizacao_4('{$mT004['T004_Id']}')");
        // }

        mysql_query("call T003_Gravar_Totalizacao_4($T003_Id);");
        
        $T003 = "SELECT T003_Id,
                        T003_Valor_Total_Produtos,
                        T003_Flag_Frete,
                        T003_Valor_Frete,
                        T003_Valor_Despesas_Acessorias,
                        T003_Valor_Total_Custo,
                        T003_Valor_Desconto,
                        T003_Valor_Total_Produtos_Sem_Desconto,
                        T003_Valor_Desconto_Manual,
                        T003_Valor_Total_ICMS,
                        T003A_Valor_Total_FCP,
                        T003_Valor_Total_ICMS_Credito,
                        T003_Valor_Total_IPI,
                        T003_Valor_ST,
                        T003A_Valor_FCP_ST,
                        T003_Valor_Total,
                        T003_IPV,
                        T003_Percentual_Margem,
                        T003_Peso_Total,
                        T003_Peso_Bruto_Total,
                        T003_Flag_Status_Orcamento,
                        T003_Valor_Total_Produtos_Confirmado,
                        T003_Valor_Total_ICMS_Confirmado,
                        T003_Valor_Total_IPI_Confirmado,
                        T003_Valor_ST_Confirmado,
                        T003_Valor_Total_Confirmado,
                        T003_Percentual_Comissao,
                        T003_Valor_Total_Comissao,
                        T003_Flag_Status_Cotacao
                        T003_Aliquota_ICMS_Credito,
                        T003_Valor_Total_PIS,
                        T003_Valor_Total_COFINS,
                        T003_Valor_Total_IRPJ,
                        T003_Valor_Total_CSLL,
                        T003_Valor_Total_Partilha_ICMS_Destino,
                        T003_Valor_Total_Partilha_ICMS_Origem,
                        D024_Id,
                        D024_Observacao_Comercial,
                        D024_Observacao_Financeiro,
                        D024_Valor_Credito_Antecipado,
                        D024_Valor_Credito_Troca,
                        T003_Prazos(T003_Id) AS Prazo, 
                        ROUND(SUM(T004_Valor_Frete_Total),2) AS FreteProdutos, 
                        GROUP_CONCAT(DISTINCT T005_Id) AS pedidos, 
                        SUM(T004_Valor_Lucro) AS Lucro,
                        Liberou.C007_Primeiro_Nome AS Liberou,
                        SUM(T004_Quantidade) AS quantidadeTotal,
                        SUM(T004_Cubagem_Unitaria*T004_Quantidade) AS cubagemTotal
                   FROM T003
              LEFT JOIN D024 ON D024_Id = T003_D024_Id
              LEFT JOIN T004 ON T004_T003_Id = T003_Id
              LEFT JOIN T005 ON T005_T003_Id=T003_Id and T005_Flag_Status!=8
              LEFT JOIN C007 AS Liberou ON Liberou.C007_Id=T003_Liberou_Alcada_C007_Id
              LEFT JOIN T003A ON T003_Id = T003A_T003_Id
                  WHERE T003_Id='{$T003_Id}'
               GROUP BY T003_Id";

        $T003  = mysql_query($T003);
        $mT003 = mysql_fetch_array($T003);

        $D024_Id      = $mT003['D024_Id'];    

        $dadosCliente = mysql_query("SELECT D024_Data_Cadastro, 
                                            D024_Valor_Limite, 
                                            D024_Valor_Credito_Antecipado, 
                                            D024_Valor_Credito_Troca,
                                            D024_Cnpj_Matriz 
                                       FROM D024
                                      WHERE D024_Id = '{$D024_Id}'");

        $dadosCliente = mysql_fetch_array($dadosCliente);

        $filtroSaldo = "D024_Id = '{$D024_Id}'";
        //calcula a soma dos limites das empresas vinculadas a um Cnpj Matriz
        if(($g['C031']['somarLimiteCnpjMatriz'] == 'S') && (!empty($dadosCliente['D024_Cnpj_Matriz']))){

            $filtroSaldo = "D024_Cnpj_Matriz = '{$dadosCliente['D024_Cnpj_Matriz']}'";

            $dadosLimite = mysql_query("SELECT SUM(D024_Valor_Limite) AS D024_Valor_Limite, 
                                               SUM(D024_Valor_Credito_Antecipado) AS D024_Valor_Credito_Antecipado, 
                                               SUM(D024_Valor_Credito_Troca) AS D024_Valor_Credito_Troca 
                                          FROM D024 
                                         WHERE $filtroSaldo");

            $dadosLimite = mysql_fetch_array($dadosLimite);

            $dadosCliente['D024_Valor_Limite']             = $dadosLimite['D024_Valor_Limite'];
            $dadosCliente['D024_Valor_Credito_Antecipado'] = $dadosLimite['D024_Valor_Credito_Antecipado'];
            $dadosCliente['D024_Valor_Credito_Troca']      = $dadosLimite['D024_Valor_Credito_Troca'];           
        } 


        $obsComercial    = substr($mT003['D024_Observacao_Comercial'], 0, 140);
        $obsFinanceiro   = substr($mT003['D024_Observacao_Financeiro'], 0, 180);
        $valorLimite     = $dadosCliente['D024_Valor_Limite'];

        //Calcula saldo do cliente
        $sqlSaldo = "SELECT IFNULL(SUM(IF(T002_Data_Vencimento<curdate(),T002_Valor_Total,0)),0) AS Vencido,
                            IFNULL(SUM(IF(T002_Data_Vencimento>=curdate(),T002_Valor_Total,0)),0) AS Vencer
                       FROM T002
                  LEFT JOIN D024 ON D024_Id=T002_D024_Id
                      WHERE $filtroSaldo
                        AND T002_Data_Recebimento='0000-00-00'
                        AND (T002_T002_Id_Agrupado<=0 OR T002_T002_Id_Agrupado IS NULL)";

        $resultSaldo = mysql_query($sqlSaldo);
        $linhaSaldo  = mysql_fetch_array($resultSaldo);

/*         $sqlAtraso = mysql_query("SELECT SUM(T002_Valor_Total) As Atraso
                                    FROM T002
                               LEFT JOIN D024 ON D024_Id = T002_D024_Id
                                   WHERE $filtroSaldo 
                                     AND T002_Data_Recebimento = '0000-00-00'
                                     AND T002_Data_Vencimento < CURDATE()
                                     AND (T002_T002_Id_Agrupado<=0 OR T002_T002_Id_Agrupado is null)");

        $linhaAtraso           = mysql_fetch_array($sqlAtraso); */
        $linhaAtraso['Atraso'] = gCorrigeNumero($linhaSaldo['Vencido']);
  
        //$linhaAtraso = gCorrigeNumero($atraso);

        //Verifica os pedidos em aberto
        $sqlPedidos   = mysql_query("SELECT IFNULL(SUM(T005_Valor_Total),0) as Pedidos
                                       FROM T005 
                                  LEFT JOIN T007 ON T007_T005_Id=T005_Id 
                                  LEFT JOIN D024 ON D024_Id=T005_D024_Id
                                      WHERE $filtroSaldo
                                        AND T005_Flag_Status !=8
                                        AND (T005_T005_Id_Agrupado<=0 or T005_T005_Id_Agrupado is null) 
                                        AND T007_Id IS NULL");
        $linhaPedidos = mysql_fetch_array($sqlPedidos);
        
        // CALCULA SALDO LIMITE
        $saldoAtual = ($valorLimite-($linhaSaldo['Vencido']+$linhaSaldo['Vencer']+$linhaPedidos['Pedidos']));
        $saldoAtual = gCorrigeNumero($saldoAtual);        

        $valorCreditoAntecipado = gCorrigeNumero($mT003['D024_Valor_Credito_Antecipado']);
        $valorCreditoTroca      = gCorrigeNumero($mT003['D024_Valor_Credito_Troca']);
        $valorLimite            = gCorrigeNumero($valorLimite);      

        if($mT003['T003_Valor_Total_Produtos'] > 0 && $g['C031']['ratearFreteVendaNoCusto'] == 'S' && $mT003['T003_Flag_Frete'] == '0' && $mT003['FreteProdutos'] != $mT003['T003_Valor_Frete']) {
            $mT003['T003_Valor_Frete'] = '<span style="color: var(--BGVermelho)">Atualizar preço tabela</span>';
        } else {
            $mT003['T003_Valor_Frete'] = gCorrigeNumero($mT003['T003_Valor_Frete']);
        }

        $mT003['T003_Valor_Despesas_Acessorias'] = gCorrigeNumero($mT003['T003_Valor_Despesas_Acessorias']);
        $lucro                                   = gCorrigeNumero(($mT003['Lucro']/$mT003['T003_Valor_Total_Produtos'])*100);
        $mT003['Lucro']                          = gCorrigeNumero($mT003['Lucro']);
        $margem                                  = gCorrigeNumero((($mT003['T003_Valor_Total_Produtos']-$mT003['T003_Valor_Total_Custo'])/$mT003['T003_Valor_Total_Produtos'])*100);
        // Miguel - Só mostra a linha da margem para perfil Supervisor (S) ou Diretor (D)
          $perfilUsuario = $g['C007']['C007_Flag_Vendedor'];
          $linhaMargem = '';
          if ($perfilUsuario == 'S' || $perfilUsuario == 'D') {
              $linhaMargem = <<<EOT
                <tr>
                  <td class='observacaoTotaisTd' style='border-bottom:1px solid #F4F4F4; font-size:11px;'>
                      % Margem
                  </td>
                  <td class='observacaoTotaisTd' style='border-bottom:1px solid #F4F4F4; font-size:11px;' align='right'>
                      {$margem}%
                  </td>
                </tr>
  EOT;
          }
        $percentualDesconto                      = gCorrigeNumero(($mT003['T003_Valor_Desconto']/$mT003['T003_Valor_Total_Produtos_Sem_Desconto'])*100);

        require_once('bibliotecas/classes/VEN001.php');
        $VEN001 = new VEN001();
        $percentualDesconto = $VEN001->ven001ModObservacaoPercentualDesconto($percentualDesconto, $mT003);

        $percentualDescontoManual               = gCorrigeNumero(($mT003['T003_Valor_Desconto_Manual']/$mT003['T003_Valor_Total_Produtos_Sem_Desconto'])*100);
        $ipv                                    = gCorrigeNumero($mT003['T003_Valor_Total_Produtos']/$mT003['T003_Valor_Total_Custo'],4);
        $mT003['T003_Valor_Total_Custo']        = gCorrigeNumero($mT003['T003_Valor_Total_Custo']);
        $mT003['T003_Valor_Desconto']           = gCorrigeNumero($mT003['T003_Valor_Desconto']);
        //$mT003['T003_Valor_Total_Produtos_Sem_Desconto'] = gCorrigeNumero($mT003['T003_Valor_Total_Produtos_Sem_Desconto']);
        $mT003['T003_Valor_Total_Produtos']     = gCorrigeNumero($mT003['T003_Valor_Total_Produtos']);
        $valorTotalICMS                         = gCorrigeNumero($mT003['T003_Valor_Total_ICMS'] + $mT003['T003A_Valor_Total_FCP']);
        $mT003['T003_Valor_Total_ICMS']         = gCorrigeNumero($mT003['T003_Valor_Total_ICMS']);
        $mT003['T003A_Valor_Total_FCP']         = gCorrigeNumero($mT003['T003A_Valor_Total_FCP']);
        $mT003['T003_Valor_Total_ICMS_Credito'] = gCorrigeNumero($mT003['T003_Valor_Total_ICMS_Credito']);
        $mT003['T003_Valor_Total_IPI']          = gCorrigeNumero($mT003['T003_Valor_Total_IPI']);
        $valortotalST                           = gCorrigeNumero($mT003['T003_Valor_ST'] + $mT003['T003A_Valor_FCP_ST']);
        $mT003['T003A_Valor_FCP_ST']            = gCorrigeNumero($mT003['T003A_Valor_FCP_ST']);
        $mT003['T003_Valor_ST']                 = gCorrigeNumero($mT003['T003_Valor_ST']);        
        $titleST                                = "ST:   {$mT003['T003_Valor_ST']}\nFCP ST:   {$mT003['T003A_Valor_FCP_ST']}";
        $mT003['T003_Valor_Total']              = gCorrigeNumero($mT003['T003_Valor_Total']);
        $mT003['T003_IPV']                      = gCorrigeNumero($mT003['T003_IPV'],4);
        $mT003['T003_Percentual_Margem']        = gCorrigeNumero($mT003['T003_Percentual_Margem'],2);
        $mT003['T003_Valor_Desconto_Manual']    = gCorrigeNumero($mT003['T003_Valor_Desconto_Manual']*-1);
        $mT003['quantidadeTotal']               = gCorrigeNumero($mT003['quantidadeTotal']);
        $mT003['cubagemTotal']                  = gCorrigeNumero($mT003['cubagemTotal']);
        $mT003['pesoLiquido']                   = gCorrigeNumero($mT003['T003_Peso_Total']);
        $mT003['pesoBruto']                     = gCorrigeNumero($mT003['T003_Peso_Bruto_Total']);

        require_once('prog/crm/crm001/grid_func-callback.php');
        if ($mT003['T005_Id']>0){
            $pedido = "<div class='blue' style='border-radius:5px;background:blue;color:white; padding: 1px;margin-top: 14px;height: 16px;font-size: 13px;' align=center>Gerou pedido n. {$mT003['pedidos']}</div>";
        } else {
            $arrayStatus = array();
            $arrayStatus['todosCampos']['T003_Flag_Status_Orcamento'] = $mT003['T003_Flag_Status_Orcamento'];
            $statusOrcamento = campoStatus($arrayStatus);
            $pedido = "<div class='blue' style='border-radius:5px;background:blue;color:white; padding: 1px;margin-top: 14px;height: 16px;font-size: 13px;' align=center>{$statusOrcamento}</div>";
        }

        $mT003['T003_Valor_Total_Produtos_Confirmado'] = gCorrigeNumero($mT003['T003_Valor_Total_Produtos_Confirmado']);
        $mT003['T003_Valor_Total_ICMS_Confirmado']     = gCorrigeNumero($mT003['T003_Valor_Total_ICMS_Confirmado']);
        $mT003['T003_Valor_Total_IPI_Confirmado']      = gCorrigeNumero($mT003['T003_Valor_Total_IPI_Confirmado']);
        $mT003['T003_Valor_ST_Confirmado']             = gCorrigeNumero($mT003['T003_Valor_ST_Confirmado']);
        $mT003['T003_Valor_Total_Confirmado']          = gCorrigeNumero($mT003['T003_Valor_Total_Confirmado']);
        $mT003['T003_Percentual_Comissao']             = gCorrigeNumero($mT003['T003_Percentual_Comissao']);
        $mT003['T003_Valor_Total_Comissao']            = gCorrigeNumero($mT003['T003_Valor_Total_Comissao']);

        
        //$valorOrcamento = $VEN001->calculaTotalGeral($T003_Id);
        $exibirLimites = $g['C031']['exibirLimitesOrcamentoPedido'];
        //if($exibirLimites == false){

        require_once('bibliotecas/funcoesCallback/campoPrecoTabelaGridOrcamento.php');
        $array['todosCampos']['T003_Id']                  = $T003_Id;
        $array['todosCampos']['T003_Flag_Status_Cotacao'] = $mT003['T003_Flag_Status_Cotacao'];
        $array['campoAtual']                              = $mT003['T003_Valor_Total_Produtos_Sem_Desconto'];
        $valorPrecoTabelaOrcamento = str_replace('R$ ','',campoPrecoTabelaGridOrcamento($array, true));

        $onclick = '';
        if(isset($g['c029Ids'][215])){
            $onclick = "abrirJanela(false, '{$g['divIdRoot']}', '{$g['divId']}' , unique(), '', 'Demonstrativo resultado', '/ven/ven001/content/ven001contentMontagemIPV/', '&acaoId={$T003_Id}' , [400,620]);";
        }
        
        $heightComercial=60;
        $heightFinanceiro=59;

        if(!(isset($g['c029Ids'][217])) && (isset($g['c029Ids'][218]))){
            $heightComercial=120;
        }
        if((isset($g['c029Ids'][217])) && !(isset($g['c029Ids'][218]))){
            $heightFinanceiro=135;
        }

        $C004  = mysql_query("SELECT C004_Codigo_Regime_Tributario 
                                FROM C004
                               WHERE C004_Id='{$g['empresaAtual']}'");

        $mC004 = mysql_fetch_array($C004);

        if($mC004['C004_Codigo_Regime_Tributario'] == 1 ){
            $ICMS         = 'ICMS Crédito';
            $aliquotaICMS = "(".$mT003['T003_Aliquota_ICMS_Credito']."%) ";
            $valorICMS    = $mT003['T003_Valor_Total_ICMS_Credito'];
            $titleICMS    = "ICMS:   {$mT003['T003_Valor_Total_ICMS']}";
        }else{
            $ICMS         = 'ICM';
            $aliquotaICMS = '';
            $valorICMS    = $valorTotalICMS;
            $titleICMS    = "ICMS:   {$mT003['T003_Valor_Total_ICMS']}\nFCP:   {$mT003['T003A_Valor_Total_FCP']}";

        }

        $somaTributos = $mT003['T003_Valor_Total_PIS']+$mT003['T003_Valor_Total_COFINS']+$mT003['T003_Valor_Total_IRPJ']+$mT003['T003_Valor_Total_CSLL']+$mT003['T003_Valor_Total_Partilha_ICMS_Destino']+$mT003['T003_Valor_Total_Partilha_ICMS_Origem'];
        $somaTributos = gCorrigeNumero($somaTributos);

        $mT003['T003_Valor_Total_PIS']                   = gCorrigeNumero($mT003['T003_Valor_Total_PIS']);
        $mT003['T003_Valor_Total_COFINS']                = gCorrigeNumero($mT003['T003_Valor_Total_COFINS']);
        $mT003['T003_Valor_Total_IRPJ']                  = gCorrigeNumero($mT003['T003_Valor_Total_IRPJ']);
        $mT003['T003_Valor_Total_CSLL']                  = gCorrigeNumero($mT003['T003_Valor_Total_CSLL']);
        $mT003['T003_Valor_Total_Partilha_ICMS_Destino'] = gCorrigeNumero($mT003['T003_Valor_Total_Partilha_ICMS_Destino']);
        $mT003['T003_Valor_Total_Partilha_ICMS_Origem']  = gCorrigeNumero($mT003['T003_Valor_Total_Partilha_ICMS_Origem']);
        $title = "PIS:   {$mT003['T003_Valor_Total_PIS']}\nCOFINS:   {$mT003['T003_Valor_Total_COFINS']}\nIRPJ:   {$mT003['T003_Valor_Total_IRPJ']}\nCSLL:    {$mT003['T003_Valor_Total_CSLL']}\nDIFAL DEST:    {$mT003['T003_Valor_Total_Partilha_ICMS_Destino']}\nDIFAL ORIG:    {$mT003['T003_Valor_Total_Partilha_ICMS_Origem']}";

        $mT003['T003_Valor_Frete_Calculado'] = gCorrigeNumero($mT003['T003_Valor_Frete_Calculado']);

        echo <<<EOT
            <style type="text/css">
                .observacoesLimites{
                    width: 285px;
                    height: 202px;
                    float: left;
                    margin: 7px 0px 10px 8px;
                    border-radius:5px;
                    border-color:silver;
                }
                .observacoesLimites .comercial{
                    height: {$heightComercial}px;
                    padding:1px;
                    border-bottom:none;
                }
                .observacoesLimites .financeiro{
                    height: {$heightFinanceiro}px;
                    padding:1px;
                }
                .observacoesLimites .limite{
                    height: 15%;
                    padding:1px;
                    border-top:none;
                }
                .observacoesLimites .liberado{
                    height: 13%;
                    padding:0px;
                    border-bottom:1px solid silver;
                }
                .observacoesLimites .saldo{
                    height: 9%;
                    padding:1px;
                    border-top:none;
                }
            </style>
            <table style='font-size:13px; border-left:5px;width: 195px;float:left; height: 210px; margin: 3px 5px 0 5px; cursor: pointer;' cellspacing="0" cellpadding="0" onclick="{$onclick}">
              <tr>
                <td class='observacaoTotaisTd' style='border-bottom:1px solid #F4F4F4; font-size:11px;'>
                    Tabela
                </td>
                <td class='observacaoTotaisTd' style='border-bottom:1px solid #F4F4F4; font-size:11px;' align='right'>
                    {$valorPrecoTabelaOrcamento}
                </td>
              </tr>
             
EOT;
           
            echo <<<EOT
                <tr>
                    <td class='observacaoTotaisTd' style='border-bottom:1px solid #F4F4F4; font-size:11px;'>
                        Desconto
                    </td>
                    <td class='observacaoTotaisTd' style='border-bottom:1px solid #F4F4F4; font-size:11px;' align='right'>
                        <font style='font-size:11px'>($percentualDesconto%)</font> {$mT003['T003_Valor_Desconto']}
                    </td>
                </tr>
              <tr>
                <td class='observacaoTotaisTd' style='font-weight:bold;border-bottom:1px solid #F4F4F4'>
                    Produtos
                </td>
                <td class='observacaoTotaisTd' style='font-weight:bold;font-size:14px;border-bottom:1px solid #F4F4F4' align='right'>
                    {$mT003['T003_Valor_Total_Produtos']}
                </td>
              </tr>
              <tr>
                <td class='observacaoTotaisTd' style='border-bottom:1px solid #F4F4F4; font-size:11px;'>
                    IPI
                </td>
                <td class='observacaoTotaisTd'  style='border-bottom:1px solid #F4F4F4; font-size:11px;' align='right'>
                    {$mT003['T003_Valor_Total_IPI']}
                </td>
              </tr>
              <tr title="{$titleST}">
                <td class='observacaoTotaisTd' style='border-bottom:1px solid #F4F4F4; font-size:11px;'>
                    <u>ST+FCP ST</u>
                </td>
                <td class='observacaoTotaisTd' style='border-bottom:1px solid #F4F4F4; font-size:11px;' align='right'>
                    {$valortotalST}
                </td>
              </tr>
              <tr>
                <td class='observacaoTotaisTd' style='border-bottom:1px solid #F4F4F4; font-size:11px;'>
                    Desconto
                </td>
                <td class='observacaoTotaisTd' style='border-bottom:1px solid #F4F4F4; font-size:11px;' align='right'>
                    <font style='font-size:11px'>($percentualDescontoManual%)</font> {$mT003['T003_Valor_Desconto_Manual']}
                </td>
              </tr>
              <tr title="Quantidade:   {$mT003['quantidadeTotal']}\nCubagem:   {$mT003['cubagemTotal']}m³\nPeso liquido:   {$mT003['pesoLiquido']} kg\nPeso bruto:    {$mT003['pesoBruto']} kg\n Frete calculado:    R$ {$mT003['T003_Valor_Frete_Calculado']}">
                <td class='observacaoTotaisTd' style='border-bottom:1px solid #F4F4F4; font-size:11px;'>
                    <u>Frete</u>
                </td>
                <td class='observacaoTotaisTd' style='border-bottom:1px solid #F4F4F4; font-size:11px;' align='right'>
                    {$mT003['T003_Valor_Frete']}
                </td>
              </tr>
              <tr >
                <td class='observacaoTotaisTd' style='border-bottom:1px solid #F4F4F4; font-size:11px;'>
                    <u>Peso liquido:</u>
                </td>
                <td class='observacaoTotaisTd' style='border-bottom:1px solid #F4F4F4; font-size:11px;' align='right'>
                    {$mT003['pesoLiquido']} kg
                </td>
              </tr>  
              
              <tr >
                <td class='observacaoTotaisTd' style='border-bottom:1px solid #F4F4F4; font-size:11px;'>
                    <u>Cubagem:</u>
                </td>
                <td class='observacaoTotaisTd' style='border-bottom:1px solid #F4F4F4; font-size:11px;' align='right'>
                    {$mT003['cubagemTotal']} m³
                </td>
                 </tr>
                {$linhaMargem}
                <tr>
              <tr>
                <td class='observacaoTotaisTd' style='border-bottom:1px solid #F4F4F4; font-size:11px;'>
                    Desp.Acessorias
                </td>
                <td class='observacaoTotaisTd' style='border-bottom:1px solid #F4F4F4; font-size:11px;' align='right'>
                    {$mT003['T003_Valor_Despesas_Acessorias']}
                </td>
              </tr>
              <tr>
                <td class='observacaoTotaisTd' style='font-weight:bold;border-bottom:1px solid #F4F4F4'>
                    Total
                </td>
                <td class='observacaoTotaisTd' style='font-weight:bold;font-size:16px;border-bottom:1px solid #F4F4F4' align='right'>
                    {$mT003['T003_Valor_Total']}
                </td>
              </tr>
              <tr title="{$titleICMS}">
                <td class='observacaoTotaisTd' style='border-bottom:1px solid #F4F4F4; font-size:11px;'>
                    <u>{$ICMS}</u>
                </td>
                <td class='observacaoTotaisTd' style='border-bottom:1px solid #F4F4F4; font-size:11px;' align='right'>
                    {$aliquotaICMS}{$valorICMS}
                </td>
              </tr>
               <tr title="{$title}">
                <td class='observacaoTotaisTd' style='border-bottom:1px solid #F4F4F4; font-size:11px;'>
                    <u>Outros tributos</u>
                </td>
                <td class='observacaoTotaisTd' style='border-bottom:1px solid #F4F4F4; font-size:11px;' align='right'>
                    {$somaTributos}
                </td>
              </tr>
                           
              <tr>
                <td class='observacaoTotaisTd' style='border-bottom:1px solid #F4F4F4; font-size:11px;'>
                    Comissão
                </td>
                <td class='observacaoTotaisTd' style='border-bottom:1px solid #F4F4F4; font-size:11px;' align='right'>
                    ({$mT003['T003_Percentual_Comissao']}%) {$mT003['T003_Valor_Total_Comissao']}
                </td>
              </tr>              
            </table>
EOT;

    if((isset($g['c029Ids'][217])) && (isset($g['c029Ids'][218]))){
        echo<<<EOT
            <div class="observacoes observacoesLimites" style='float:left;'>
                <div style="height:135px;"></div>
EOT;
    }

    if(!(isset($g['c029Ids'][217])) || !(isset($g['c029Ids'][218]))){
        echo<<<EOT
            <div class="observacoes observacoesLimites" style='float:left;'>
EOT;

    }

    if(!(isset($g['c029Ids'][217]))){
        echo<<<EOT
            <div class="comercial" title='{$mT003['D024_Observacao_Comercial']}'>
                <b>Observação comercial:</b><br />
                <span style="color: var(--BGVermelho);">{$obsComercial}</span>
            </div>
            <div class="liberado">
                <span style="">Liberado Por: <span style="color: var(--BGVermelho);">{$mT003['Liberou']}</span></span>
            </div>
EOT;

    }

     if(!(isset($g['c029Ids'][218]))){
        echo<<<EOT
            <div class="financeiro"  title='{$mT003['D024_Observacao_Financeiro']}'>
                <b>Observação financeiro:</b><br />
                <span style="color: var(--BGVermelho);">{$obsFinanceiro}</span>
            </div> 
EOT;

    }

    $diasSemNota = "";
    if ($g['C031']['mostrarDiasSemNota'] == 'S' && $g['C031']['diasUltimaVendaPedirAnaliseCredito'] > 0) {
        $select = "SELECT T007_Id, datediff(curdate(), T007_Data_Emissao) as ultVendaDias
                    FROM T007 
                        LEFT JOIN D006 ON D006_Id = T007_D006_Id
                    WHERE
                        T007_D024_Id = '{$D024_Id}'
                        AND T007_Flag_Cancelada != 'S'
                        AND D006_Flag_Venda_Compra_Outros = 'V'
                        AND D006_Flag_Devolucao != 'S'
                    order by T007_Id desc limit 1";
        $result = mysql_query($select);
        $mT007 = mysql_fetch_assoc($result);

        echo "<style type='text/css'>
                .observacoesLimites .comercial{
                    height: 50px;
                    padding:1px;
                    border-bottom:none;
                }
                .observacoesLimites .financeiro{
                    height: 50px;
                    padding:1px;
                }
                .observacoesLimites .limite{
                    height: 22%;
                    padding:1px;
                    border-top:none;
                }
            </style>";

        if ($mT007['ultVendaDias'] >= $g['C031']['diasUltimaVendaPedirAnaliseCredito']) {
            $corSemNota = "var(--BGVermelho);" ;
            $msg = " - Pedido ira para Análise de crédito";    
        } else {
            $corSemNota = "var(--BGAzul);"; 
            $msg = "";
        }
        $diasSemNota = "Dias sem faturar: <span style=\"color: {$corSemNota}\">{$mT007['ultVendaDias']}{$msg}</span><br>";
        $diasSemNota = (mysql_num_rows($result) <= 0) ? "Primeira venda" : $diasSemNota;
    }
    
       echo<<<EOT
            <div class="limite">
                    {$diasSemNota}
                    Prazo Pagamento: <span style="color: var(--BGVermelho);">{$mT003['Prazo']}</span><br>
                    Limite: <span style="color: var(--BGVermelho);">{$valorLimite}</span> Saldo: <span style="color: var(--BGVermelho);">{$saldoAtual}</span><br>
                    Crédito Antecipado: <span style="color: var(--BGVermelho);">{$valorCreditoAntecipado}</span> Crédito Troca: <span style="color: var(--BGVermelho);">{$valorCreditoTroca}</span><br>
                    Valores em atraso: <span style="color: var(--BGVermelho);">{$linhaAtraso['Atraso']}</span>
            </div>
           <br>$pedido
        </div> 
       
EOT;




