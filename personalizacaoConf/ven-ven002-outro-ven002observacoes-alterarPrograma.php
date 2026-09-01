<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /ven-ven002-outro-ven002observacoes/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

global $g;
        $T005_Id = isset($r_acaoId) ? $r_acaoId : false;
        $divAjuste = isset($r_divAjuste) ? $r_divAjuste : false;
        mysql_query("call T005_Gravar_Totalizacao_4($T005_Id);");
        $T005 = "SELECT *, T005_Prazos(T005_Id) as Prazo, T005_Status_Pedido(T005_Flag_Status,1) as Status, SUM(T006_Valor_Lucro) as Lucro,Liberou.C007_Primeiro_Nome AS Liberou,LiberouOrcamento.C007_Primeiro_Nome AS LiberouOrcamento
                      FROM T005
                 LEFT JOIN D024 ON D024_Id = T005_D024_Id
                 LEFT JOIN T006 ON T006_T005_Id = T005_Id
                 LEFT JOIN T003 ON T003_Id = T005_T003_Id
                 LEFT JOIN T005A ON T005_Id = T005A_T005_Id
                 LEFT JOIN C007 AS Liberou ON Liberou.C007_Id=T005_Liberou_Alcada_C007_Id
                 LEFT JOIN C007 AS LiberouOrcamento ON LiberouOrcamento.C007_Id=T003_Liberou_Alcada_C007_Id
                     WHERE T005_Id='{$T005_Id}'";
        $T005 = mysql_query($T005);
        $mT005= mysql_fetch_array($T005);

        $obsComercial    = substr($mT005['D024_Observacao_Comercial'], 0, 140);
        $obsFinanceiro   = substr($mT005['D024_Observacao_Financeiro'], 0, 180);
        $valorLimite     = gCorrigeNumero($mT005['D024_Valor_Limite']);
        $lucro = gCorrigeNumero(($mT005['Lucro']/$mT005['T005_Valor_Total_Produtos'])*100);
        $mT005['Lucro'] = gCorrigeNumero($mT005['Lucro']);
        $ipv = gCorrigeNumero($mT005['T005_Valor_Total_Produtos']/$mT005['T005_Valor_Total_Custo'],4);
        $margem = gCorrigeNumero((($mT005['T005_Valor_Total_Produtos']-$mT005['T005_Valor_Total_Custo'])/$mT005['T005_Valor_Total_Produtos'])*100);
        $mT005['T005_Valor_Total_Produtos'] = gCorrigeNumero($mT005['T005_Valor_Total_Produtos']);
        $mT005['T005_Valor_Total_Custo'] = gCorrigeNumero($mT005['T005_Valor_Total_Custo']);
        $mT005['T005_Valor_Total_ICMS'] = gCorrigeNumero($mT005['T005_Valor_Total_ICMS']);
        $mT005['T005_Valor_Total_IPI'] = gCorrigeNumero($mT005['T005_Valor_Total_IPI']);
        $mT005['T005_Valor_ST'] = gCorrigeNumero($mT005['T005_Valor_ST']);
        $mT005['T005_Valor_Total'] = gCorrigeNumero($mT005['T005_Valor_Total']);
        $mT005['T005_IPV'] = gCorrigeNumero($mT005['T005_IPV'],4);
        $mT005['T005_Percentual_Margem'] = gCorrigeNumero($mT005['T005_Percentual_Margem'],2);
        $mT005['T005_Valor_Total_PIS'] = gCorrigeNumero($mT005['T005_Valor_Total_PIS']);
        $mT005['T005_Valor_Total_COFINS'] = gCorrigeNumero($mT005['T005_Valor_Total_COFINS']);
        $mT005['T005_Valor_Total_IRPJ'] = gCorrigeNumero($mT005['T005_Valor_Total_IRPJ']);
        $mT005['T005_Valor_Total_CSLL'] = gCorrigeNumero($mT005['T005_Valor_Total_CSLL']);

        $mT005['T005_Percentual_Comissao'] = gCorrigeNumero($mT005['T005_Percentual_Comissao']);
        $mT005['T005_Valor_Total_Comissao'] = gCorrigeNumero($mT005['T005_Valor_Total_Comissao']);

        //Calcula saldo do cliente
        $sqlSaldo = "SELECT ifnull(sum(if(T002_Data_Vencimento<curdate(),T002_Valor_Total,0)),0) as Vencido, 
                            ifnull(sum(if(T002_Data_Vencimento>=curdate(),T002_Valor_Total,0)),0) as Vencer
                        FROM T002
                        WHERE T002_D024_Id='{$mT005['D024_Id']}'
                        and T002_Data_Recebimento='0000-00-00' 
                        and (T002_T002_Id_Agrupado<=0 or T002_T002_Id_Agrupado is null)
                        ";
        log("SQL Vencido e vencer: ".$sqlSaldo);
        $resultSaldo = mysql_query($sqlSaldo);
        $linhaSaldo= mysql_fetch_array($resultSaldo);

        log("Vencido: ".$linhaSaldo['Vencido']);
        log("Vencer: " .$linhaSaldo['Vencer']);

        
        $sqlPedidos="select ifnull(sum(T005_Valor_Total),0) as Pedidos
                                from T005
                           left join T007 on T007_T005_Id=T005_Id
                               where T005_D024_Id='{$mT005['D024_Id']}'
                                 and T005_Flag_Almoxarifado='S'
                                 and T007_Id is null";
        log("SQL PEdidos: ".$sqlPedidos);
        $retornoPedidos = mysql_query($sqlPedidos);
        $linhaPedidos = mysql_fetch_array($retornoPedidos);
        log("Pedidos: ".$linhaPedidos['Pedidos']);
        $mT005['D024_Valor_Limite'] = (!empty($mT005['D024_Valor_Limite']) ? $mT005['D024_Valor_Limite'] : '0.00');
        log("Limite: ".$mT005['D024_Valor_Limite']);
        $saldoAtual=gCorrigeNumero($mT005['D024_Valor_Limite']-($linhaSaldo['Vencido']+$linhaSaldo['Vencer']+$linhaPedidos['Pedidos']));
        
        $percentualDesconto = gCorrigeNumero(($mT005['T005_Valor_Desconto']/$mT005['T005_Valor_Total_Produtos_Sem_Desconto'])*100,2);

        //$valorOrcamento = $VEN001->calculaTotalGeral($T005_Id);
        $exibirLimites = $g['C031']['exibirLimitesOrcamentoPedido'];
        //$mT005['T005_Valor_Total_Produtos_Sem_Desconto'] = gCorrigeNumero($mT005['T005_Valor_Total_Produtos_Sem_Desconto']);
        $mT005['T005_Valor_Desconto'] = gCorrigeNumero($mT005['T005_Valor_Desconto']);
        // $mT005['T005_Valor_ICMS_Substituicao_Retencao'] = gCorrigeNumero($mT005['T005_Valor_ICMS_Substituicao_Retencao']);
        $valortotalST = gCorrigeNumero($mT005['T005_Valor_ICMS_Substituicao_Retencao'] + $mT005['T005A_Valor_FCP_ST']);
        $valorCreditoAntecipado = gCorrigeNumero($mT005['D024_Valor_Credito_Antecipado']);
        $valorCreditoTroca = gCorrigeNumero($mT005['D024_Valor_Credito_Troca']);
        
        require_once('bibliotecas/funcoesCallback/campoPrecoTabelaGridPedido.php');
        $array['todosCampos']['T005_Id'] = $T005_Id;
        $array['todosCampos']['T005_Flag_Status'] = $mT005['T005_Flag_Status'];
        $array['campoAtual'] = $mT005['T005_Valor_Total_Produtos_Sem_Desconto'];
        $valorPrecoTabelaPedido = campoPrecoTabelaGridPedido($array, true);
        
        $onclick = ($g['nivelAlcada'] == 4) ? "abrirJanela(false, '{$g['divIdRoot']}', '{$g['divId']}' , unique(), '', 'Montagem IPV', '/ven/ven002/content/contentMontagemIPV/', '&acaoId={$T005_Id}', [370,580]);" : "";

        $diasSemNota = "";
        if ($g['C031']['mostrarDiasSemNota'] == 'S' && $g['C031']['diasUltimaVendaPedirAnaliseCredito'] > 0) {
            $select = "SELECT T007_Id, datediff(curdate(), T007_Data_Emissao) as ultVendaDias
                        FROM T007 
                            LEFT JOIN D006 ON D006_Id = T007_D006_Id
                        WHERE
                            T007_D024_Id = '{$mT005['D024_Id']}'
                            AND T007_Flag_Cancelada != 'S'
                            AND D006_Flag_Venda_Compra_Outros = 'V'
                            AND D006_Flag_Devolucao != 'S'
                        order by T007_Id desc limit 1";
            $result = mysql_query($select);
            $mT007 = mysql_fetch_assoc($result);

            $style = "<style type='text/css'>
                        .observacoesLimites .comercial{
                            height: 50px;
                        }
                        .observacoesLimites .financeiro{
                            height: 50px;
                        }
                        .observacoesLimites .limite{
                            height: 25%;
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
        
        echo <<<EOT
            <style type="text/css">
                .observacoesLimites{
                    width: 285px;
                    height: 186px;
                    float: left;
                    margin: 7px 0px 10px 8px;
                    border-radius:5px;
                    border-color:silver;
                }
                .observacoesLimites .comercial{
                    height: 52px;
                    padding:1px;
                    border-bottom:none;

                }
                .observacoesLimites .financeiro{
                    height: 68px;
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
            <table style='font-size:13px; border-left:5px;width: 195px;float:left; height: 210px; margin: 7px 5px 0 5px; cursor: pointer;' cellspacing="0" cellpadding="0" onclick="{$onclick}">
                <tr>
                    <td style='border-bottom:1px solid #F4F4F4'>
                        Tabela
                    </td>
                    <td style='font-weight:normal;border-bottom:1px solid #F4F4F4' align='right'>
                        {$valorPrecoTabelaPedido}
                    </td>
                </tr>
                <tr>
                    <td style='border-bottom:1px solid #F4F4F4'>
                        Desconto
                    </td>
                    <td style='font-weight:normal;border-bottom:1px solid #F4F4F4' align='right'>
                        ($percentualDesconto%) R$ {$mT005['T005_Valor_Desconto']}
                    </td>
                </tr>
                <tr>
                    <td style='font-weight:bold;border-bottom:1px solid #F4F4F4'>
                        Produtos
                    </td>
                    <td style='font-weight:bold;font-size:14px;border-bottom:1px solid #F4F4F4' align='right'>
                        R$ {$mT005['T005_Valor_Total_Produtos']}
                    </td>
                </tr>
                <tr>
                    <td style='border-bottom:1px solid #F4F4F4'>
                        IPI
                    </td>
                    <td style='font-weight:bold;border-bottom:1px solid #F4F4F4;font-size:14px;' align='right'>
                        R$ {$mT005['T005_Valor_Total_IPI']}
                    </td>
                </tr>
                <tr>
                    <td style='border-bottom:1px solid #F4F4F4'>
                        ST + FCP ST
                    </td>
                    <td style='font-weight:bold;border-bottom:1px solid #F4F4F4;font-size:14px;' align='right'>
                        R$ {$valortotalST}
                    </td>
                </tr>
                <!--<tr>
                    <td>
                        IPV:
                    </td>
                    <td style='font-weight:bold;border-bottom:1px solid #F4F4F4' align='right'>
                        {$mT005['T005_IPV']}
                    </td>
                </tr>-->
                <tr>
                    <td style='font-weight:bold;border-bottom:1px solid #F4F4F4'>
                        Total
                    </td>
                    <td style='font-weight:bold;font-size:18px;border-bottom:1px solid #F4F4F4' align='right'>
                        R$ {$mT005['T005_Valor_Total']}
                    </td>
                </tr>
                <tr>
                    <td style='border-bottom:1px solid #F4F4F4'>
                        ICM
                    </td>
                    <td style='border-bottom:1px solid #F4F4F4' align='right'>
                        R$ {$mT005['T005_Valor_Total_ICMS']}
                    </td>
                </tr>
                <tr>
                    <td style='border-bottom:1px solid #F4F4F4'>
                        PIS
                    </td>
                    <td style='border-bottom:1px solid #F4F4F4' align='right'>
                        R$ {$mT005['T005_Valor_Total_PIS']}
                    </td>
                </tr>
                <tr>
                    <td style='border-bottom:1px solid #F4F4F4'>
                        COFINS
                    </td>
                    <td style='border-bottom:1px solid #F4F4F4' align='right'>
                        R$ {$mT005['T005_Valor_Total_COFINS']}
                    </td>
                </tr>
                <tr>
                    <td style='border-bottom:1px solid #F4F4F4'>
                        IRPJ
                    </td>
                    <td style='border-bottom:1px solid #F4F4F4' align='right'>
                        R$ {$mT005['T005_Valor_Total_IRPJ']}
                    </td>
                </tr>
                <tr>
                    <td style='border-bottom:1px solid #F4F4F4'>
                        CSLL
                    </td>
                    <td style='border-bottom:1px solid #F4F4F4' align='right'>
                        R$ {$mT005['T005_Valor_Total_CSLL']}
                    </td>
                </tr>
				<!--
                <tr>
                    <td style='font-weight:normal;border-bottom:1px solid #F4F4F4'>
                        Comissão
                    </td>
                    <td style='border-bottom:1px solid #F4F4F4' align='right'>
                        ({$mT005['T005_Percentual_Comissao']}%) R$ {$mT005['T005_Valor_Total_Comissao']}
                    </td>
                </tr>
				-->
            </table>
            {$style}
            <div class="observacoes observacoesLimites" style='border:1px solid silver; '>
                <div class="comercial" title='{$mT005['D024_Observacao_Comercial']}'>
                    <b>Observação comercial:</b><br />
                    <span style="color: red;">{$obsComercial}</span>
                </div>                
                <div class="liberado">
                    <span>Orçamento liberado por: <span style="color: red;">{$mT005['LiberouOrcamento']}</span></span><br />
                    <span>Pedido liberado por: <span style="color: red;">{$mT005['Liberou']}</span></span>  
                </div>
                <div class="financeiro" title='{$mT005['D024_Observacao_Financeiro']}'>
                    <b>Observação financeiro:</b><br />
                    <span style="color: red;">{$obsFinanceiro}</span>
                </div>
                <div class="limite">
                    {$diasSemNota}
                    Prazo Pagamento: <span style="color: red;">{$mT005['Prazo']}</span><br>
                    Limite: <span style="color: red;">{$valorLimite}</span>
                    Saldo disponível: <span style="color: red;">{$saldoAtual}</span><br />
                    Crédito Antecipado: <span style="color: red;">{$valorCreditoAntecipado}</span> Crédito Troca: <span style="color: red;">{$valorCreditoTroca}</span>
                </div>
                <div style='font-weight:bold;border-radius:5px;border-bottom:1px solid #F4F4F4;color:white;background:blue; height: 16px; font-size: 13px; margin-top: 11px; text-align: center; padding: 1px;'>{$mT005['Status']}</div>
            </div>
EOT;
