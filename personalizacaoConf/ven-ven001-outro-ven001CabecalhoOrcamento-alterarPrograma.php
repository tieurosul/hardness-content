<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /ven-ven001-outro-ven001CabecalhoOrcamento/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/


        global $g;
        // Select
        $sql = <<<EOT
        SELECT 
            *,
            if(D024_Cnpj='',D024_Cpf,D024_Cnpj) as D024_Cnpj
        FROM 
            T003
        left join D024 on T003_D024_Id=D024_Id
        left join D020 on D024_D020_Id=D020_Id
        left join D018 on D024_D018_Id=D018_Id
        left join C007 on C007_Id=D024_C007_Vendedor_Interno
        left join D030 on D024_D030_Id=D030_Id
        WHERE
            (T003_Id='$r_acaoId')
EOT;
        $resultado = mysql_query($sql);
        $row = mysql_fetch_assoc($resultado);
        
        // Registro vazios = FALTA
        foreach ($row as &$row_) {
            if (trim($row_)=="") { 
                $row_ = '<span style="color: var(--BGVermelho); line-height: 0.5rem;">FALTA</span>';
            }
        }
        $possuiRestricoes = "";
        if($row['D024_Flag_Liberado']=="N" || $row['D024_Flag_Restricao'] == "S"){
            $possuiRestricoes = "<font style='color: var(--BGVermelho); line-height: 0.5rem;'><b>RESTRIÇÕES</b></font>";
        }else{
            $possuiRestricoes = "<font style='color: var(--BGVerde); line-height: 0.5rem;'><b>LIBERADO</b></font>";
        }
        $Contribuinte = "<font style='color: var(--BGVermelho); line-height: 0.5rem;'><b> - </b></font>";
        if($row['D024_Flag_Contribuinte']=="0"){
            $Contribuinte = "<font style='color: var(--BGAzul); line-height: 0.5rem;'><b>NÃO CONTRIB.</b></font>";
        } else if($row['D024_Flag_Contribuinte']=="1"){
            $Contribuinte = "<font style='color: var(--BGAzul); line-height: 0.5rem;'><b>CONSUM.FINAL</b></font>";
        } else if($row['D024_Flag_Contribuinte']=="2"){
            $Contribuinte = "<font style='color: var(--BGAzul); line-height: 0.5rem;'><b>REVENDA</b></font>";
        } else if($row['D024_Flag_Contribuinte']=="3"){
            $Contribuinte = "<font style='color: var(--BGAzul); line-height: 0.5rem;'><b>PROD.RURAL</b></font>";
        } else if($row['D024_Flag_Contribuinte']=="4"){
            $Contribuinte = "<font style='color: var(--BGAzul); line-height: 0.5rem;'><b>SIMPLES</b></font>";
        } else if($row['D024_Flag_Contribuinte']=="5"){
            $Contribuinte = "<font style='color: var(--BGAzul); line-height: 0.5rem;'><b>INDUSTRIA</b></font>";
        } else if ($row['D024_Flag_Contribuinte']==""){
            $Contribuinte="<font color='red'>FALTA</font>";            
        }
        
        switch($row['T003_Flag_Lista_Preco']){
            case '1':   
                $listaPreco="Preço1";
                break;
            case '2':
                $listaPreco="Preço2";
                break;
            case '3':
                $listaPreco = "Preço3";
                break;
            case '4':
                $listaPreco = "Preço4";
                break;
            default:
                $listaPreco="<font color='red'><b>FALTA</b></font>";
        }

        switch($row['T003_Flag_Destino_Produto']){
            case '1':   
                $destino="Industrialização";
                break;
            case '2':
                $destino="Revenda";
                break;
            case '3':
                $destino = "Consumo";
                break;
            case '4':
                $destino = "Especial";
                break;
            default:
                $destino="<font color='red'><b>FALTA</b></font>";
        }
        
        $dataEmissao = gCorrigeData($row['T003_Data_Emissao']);
        if($g['C031']['campoNomeEmpresa'] == 'fantasia'){
            $D024_Nome_Empresa = gTruncar($row['D024_Nome_Fantasia'], 24, ' ');
        } else {
            $D024_Nome_Empresa = gTruncar($row['D024_Nome_Empresa'], 24, ' ');
        }
        $D024_Nome_Empresa = $D024_Nome_Empresa.' '.$row['D024_Cnpj'];
        $D020_Nome_Cidade = gTruncar($row['D020_Nome_Cidade'], 15);
        $row['D024_Telefone_1'] = str_replace(array(' ', '.', '-'), '', $row['D024_Telefone_1']);
        $D024_Telefone = strpos($row['D024_Telefone_1'], "FALTA") ? '<span style="color: var(--BGVermelho);">FALTA</span>' : gTruncar($row['D024_DDD_Telefone_1'].' '.$row['D024_Telefone_1'], 20);

        $ultimaCompra = $row['D024_Data_Ultima_Compra'];
        
        if (!strpos($ultimaCompra,'FALTA')) {
            if ($ultimaCompra < date("Y-m-d",strtotime(date("Y-m-d")."-12 month"))) {
                $ultimaCompra = "<blink><font style='background:red;color:white;font-weight:bold;padding:3px;border-radius: .425rem;'>" . gCorrigeData($ultimaCompra) . "</font></blink>";
            } else {
                $ultimaCompra = "<font style='background:green;color:white;font-weight:bold;padding:3px;border-radius: .425rem;'>" . gCorrigeData($ultimaCompra) . "</font>";
            }
        } else {
            $filiais = mysql_query("SELECT D024_Data_Ultima_Compra 
                                      FROM D024
                                     WHERE SUBSTR(D024_Cnpj,1,10) = SUBSTR('{$row['D024_Cnpj']}',1,10)
                                       AND D024_Data_Ultima_Compra > '0000-00-00'
                                  ORDER BY D024_Data_Ultima_Compra DESC LIMIT 1");
            
            if(mysql_num_rows($filiais) > 0){
                log("esta entrando aqui");
                $mFiliais = mysql_fetch_array($filiais);
                if ($mFiliais['D024_Data_Ultima_Compra'] < date("Y-m-d",strtotime(date("Y-m-d")."-12 month"))) {
                    $ultimaCompra = "<blink><font style='background:red;color:white;font-weight:bold;padding:3px;border-radius: .425rem;'>" . gCorrigeData($mFiliais['D024_Data_Ultima_Compra']) . "</font></blink>";
                } else {
                    $ultimaCompra = "<font style='background:green;color:white;font-weight:bold;padding:3px;border-radius: .425rem;'>" . gCorrigeData($mFiliais['D024_Data_Ultima_Compra']) . "</font>";
                }
            } else {
                $ultimaCompra = "<blink><font style='background:red;color:white;font-weight:bold;padding:3px;border-radius: .425rem;'>SEM COMPRA</font></blink>";
            }
        }

        /*if($g['C007']['C007_Flag_Vendedor'] == 'V' && $row['D024_Flag_Pre_Cadastro'] == 'S'){
            $urlCadastroCliente = <<<EOT
                onclick="abrirJanela(false, '{$g['divIdRoot']}', '{$g['divId']}' ,unique(), '', 'Pré Cadastro Cliente/Fornecedor', '/cad/cad001/content/cad001ContentPreCadastrarCliente/', '&acaoId=' + encodeURIComponent('{$row['D024_Id']}') + '&tabela=D024', [930,583]);"
EOT;
        } else {
            $urlCadastroCliente = <<<EOT
                onclick="abrirJanela(false, '{$g['divIdRoot']}', '{$g['divId']}' ,unique(), '', 'Editar Cliente/Fornecedor', '/cad/cad001/content/cad001contentCadastro/', '&acaoId=' + encodeURIComponent('{$row['D024_Id']}') + '&tabela=D024', [930,583]);"
EOT;
        }*/
        $urlCadastroCliente = <<<EOT
            onclick="abrirJanela(false, '{$g['divIdRoot']}', '{$g['divId']}' , unique(), '', 'CRM - Cliente', '/crm/crm001/content/crm001ContentPrincipalCRM/', '&D024_Id=' + encodeURIComponent('{$row['D024_Id']}') + '&tabela=D024', 'auto');"
EOT;
        // Print
        echo <<<EOT
            <div class="contentCabecalhoLayout">
                <table class="tabela ui-state-default ui-corner-all">
                    <tr class="linhaDesc" style="cursor:pointer;" {$urlCadastroCliente}>
                        <td min-width='4%'>Nº</td>
                        <td min-width='4%'>Código</td>
                        <td min-width='35%'>Cliente</td>
                        <td min-width='11%'>Telefone</td>
                        <td min-width='13%'>Cidade</td>
                        <td min-width='1%'>UF</td>
                        <td min-width='5%'>Emissão</td>
                        <td min-width='7%'>Tipo</td>
                        <td min-width='7%'>Financeiro</td>
                        <td min-width='5%'>Lista</td>
                        <td min-width='9%'>Destino</td>
                        <td min-width='9%'>Ult.Compra</td>
                    </tr>
                    <tr class="linhaVal" style="cursor:pointer;" {$urlCadastroCliente}>
                        <td align='center'>{$row['T003_Id']}</td>
                        <td>{$row['D024_Id']}</td>
                        <td>{$D024_Nome_Empresa}</td>
                        <td>{$D024_Telefone}</td>
                        <td>{$D020_Nome_Cidade}</td>
                        <td>{$row['D018_UF']}</td>
                        <td>{$dataEmissao}</td>
                        <td>{$Contribuinte}</td>
                        <td>{$possuiRestricoes}</td>
                        <td>{$listaPreco}</td>
                        <td>{$destino}</td>
                        <td>{$ultimaCompra}</td>
                    </tr>
                </table>
            </div>
EOT;


