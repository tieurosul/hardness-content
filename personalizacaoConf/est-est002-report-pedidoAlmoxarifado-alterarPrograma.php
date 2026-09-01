<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /est/est002/report/pedidoAlmoxarifado/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/



        global $g;
        
        //??
        //$cArquivo_Cabecalho_Relatorio = $confUsuario['pathRaiz']."dados_usuarios/".$confUsuario['dbDatabase']."/cabecalho_relatorio.php";
        $cArquivo_Cabecalho_Relatorio = "hardness/cabecalho_impressoes.php";
    
        $Linhas_Normais=30;
        $Linhas_Ultima_Pagina=25;
        $Tamanho_Maximo_Tabela=580;
        $Tamanho_Minimo_Tabela=400;
        $Width_Relatorio='98%';

        if(isset($_GET['T005_Id_64']))
        {
            $T005_Id_Array = explode(';', base64_decode($_GET['T005_Id_64']));
        }
        else
        {
            $T005_Id_Array = explode(';', $_GET['T005_Id']);
        }
        

        /* HTML START */

        echo "<html>";
        echo "<head>";
        echo "<title>Impressão Pedidos</title>";
        echo "<meta http-equiv='Content-Type' content='text/html' charset='UTF-8'>";
        echo "<style>

        .fonte
        {
            font-family:arial,verdana;
            font-size:11px;
        }

        @media print {
            .pagebreak { page-break-before: always;} /* page-break-after works, as well */
        }


        </style>";

        echo "</head>";
        echo "<body bgcolor=white leftmargin='0' topmargin='0'>";

        /* PARA CADA PEDIDO(T005_Id) */
        foreach($T005_Id_Array as $T005_Id) 
        {
            $mT005['T005_Observacao_1'] = '';
            /* PEGA TODAS AS INFOs DO PEDIDO */
            $cSql_T005=mysql_query("select * from T005
            left join D024 on D024_Id=T005_D024_Id
            left join D020 on D020_Id=D024_D020_Id
            left join D018 on D018_Id=D020_D018_Id
            left join D021 on D021_Id=T005_D021_Id
            left join D022 on D022_Id=T005_D022_Id
            left join D036 ON D036_Id=T005_D036_Id
            left join C007 on C007_Id=T005_C007_Id_Vendedor_Interno
                where T005_Id='{$T005_Id}'");

            $mT005 = mysql_fetch_assoc($cSql_T005);

            // PEGA INFO
            $cSql_C004=mysqli_query("select * from C004 where C004_Id='{$Empresa_Atual}'");
            $mC004=mysql_fetch_array($cSql_C004);
            $mC004['C004_Observacao_Promocao']=str_replace("\n","<br>",$mC004['C004_Observacao_Promocao']);

            $mT005['T005_Data_Emissao']=gCorrigeData($mT005['T005_Data_Emissao']);
            $mT005['T005_Observacao']=str_replace("\n","<br>",trim($mT005['T005_Observacao']));

            $cSql_Prazos=mysqli_query("select T005_Prazos('$T005_Id') as Prazos");
            $mPrazos=mysql_fetch_array($cSql_Prazos);

            // PEGA INFO
            if ($mConfig['C020_Expedicao_Direcionar_Compra']=='S')
            {
                $cClausula="select count(*)
                            from T006
                        left join T005 on T005_Id=T006_T005_Id
                        left join D009 on D009_Id=T006_D009_Id
                        left join T066 on T066_D009_Id=D009_Id
                        left join D004 on D004_Id=T066_D004_Id
                        left join D049 on D049_Id=D009_D049_Id
                        left join C004 on C004_Id=T006_C004_Id
                        left join D024 on D024_Id=D009_D024_Id
                        left join D005 on D005_Id=T006_D005_Id
                        left join D082 on D082_Id=D049_D082_Id
                            where T006_T005_Id='$T005_Id'
                            and (T006_T006_Id is null or T006_T006_Id<=0)
                            and (T006_Quantidade>0)
                            and (T006_Flag_Status!=3)
                            and (T006_Flag_Tipo_Produto='AA' or T006_Flag_Tipo_Produto='' or T006_Flag_Tipo_Produto is null)
                            and (T006_Quantidade>T006_Quantidade_Separacao or T006_Quantidade_Separacao is null)
                            and T006_Flag_Divergencia!='S'
                            and T006_Quantidade>T006_Quantidade_Estoque
                        group by T006_Id
                        order by D004_Local,T006_Id";
                $Resultado=mysqli_query($cClausula);
            }
            // PEGA INFO
            $cClausula="select T006_Item,
                            T006_Quantidade,
                            if(T006_Codigo_Substituto!='',T006_Codigo_Substituto,T006_Codigo_Produto) as T006_Codigo_Produto_Substituto,
                            T006_Codigo_Produto,
                            T006_Codigo_Substituto,
                            T006_Descricao_Produto,
                            D082_Marca,
                            D049_Modelo,
                            D009_Quantidade_Estoque,
                            T006_Quantidade_Separacao Separado,
                            T006_Quantidade_Estoque,
                            D009_Quantidade_Estoque_Real,
                            T006_Peso_Unitario,
                            T006_Id,
                            D003_Imagem,
                            group_concat(distinct T066_Id SEPARATOR '|') as T066_Id_Mais,
                            DATE_FORMAT(D049_Data_Fabricacao,'%d/%m/%Y') AS D049_Data_Fabricacao,
                            DATE_FORMAT(D049_Data_Validade,'%d/%m/%Y') AS D049_Data_Validade,
                            D049_Lote,
                            D037_Unidade,
                            D049_Id,
                            T006_Quantidade_Separacao,
                            T006_T066_Id,
                            T006_D009_Id,
                            D001_Id,
                            D001A_Observacao_Logistica,
                            (SELECT T014_Codigo_Produto from T014 LEFT JOIN T013 on T013_Id=T014_T013_Id LEFT JOIN D006 ON D006_Id = T014_D006_Id WHERE T014_D009_Id=D009_Id AND T013_Flag_Estoque_Finalizado = 'S' AND (D006_Flag_Venda_Compra_Outros = 'C' OR D006_Flag_Venda_Compra_Outros = 'T') ORDER BY T013_Data_Emissao DESC LIMIT 1) as Cod_Forn
                        from T006
                    left join T005 on T005_Id=T006_T005_Id
                    left join D009 on D009_Id=T006_D009_Id
                    left join T066 on T066_Id=T006_T066_Id
                    left join D004 on D004_Id=T066_D004_Id
                    left join D049 on D049_Id=D009_D049_Id
                    left join D001 on D001_Id=D049_D001_Id
                    left join D001A on D001_Id=D001A_D001_Id
                    left join D003 on D003_Id=D001_D003_Id
                    left join C004 on C004_Id=T006_C004_Id
                    left join D024 on D024_Id=D009_D024_Id
                    left join D005 on D005_Id=T006_D005_Id
                    left join D082 on D082_Id=D049_D082_Id
                    left join D037 on D037_Id=D001_D037_Id
                        where T006_T005_Id='$T005_Id'
                        and (T006_T006_Id is null or T006_T006_Id<=0)
                        and (T006_Quantidade>0)
                        and (T006_Flag_Status!=3)
                        and IF(IFNULL(T006_C004_Id_Estoque,0) > 0, T006_C004_Id_Estoque, T005_C004_Id) = T005_C004_Id
                    group by T006_Id
                    order by D004_Local,T066_Id ASC,T006_Id";
            $Resultado=mysqli_query($cClausula);
            echo mysql_error();

            // PEGA INFO
            $sqlTotais = mysqli_query("SELECT COUNT(DISTINCT T006_Id) as totalItens,
                                            SUM(T006_Quantidade) as quantidadeTotal,
                                            SUM(D001_Cubagem_Unitaria*T006_Quantidade) as cubagemTotal,
                                            T005_Peso_Liquido as pesoliquidoTotal,
                                            T005_Peso_Bruto as pesobrutoTotal
                                            FROM T006
                                        LEFT JOIN T005 ON T005_Id = T006_T005_Id     
                                        LEFT JOIN D009 ON D009_Id = T006_D009_Id
                                        LEFT JOIN D049 ON D049_Id = D009_D049_Id
                                        LEFT JOIN D001 ON D001_Id = D049_D001_Id
                                            WHERE T006_T005_Id='{$T005_Id}'");
            $rowTotais = mysql_fetch_assoc($sqlTotais);
            $rowTotais['pesobrutoTotal'] = number_format($rowTotais['pesobrutoTotal'],2,',','.');
            $rowTotais['pesoliquidoTotal'] = number_format($rowTotais['pesoliquidoTotal'],2,',','.');
            $rowTotais['quantidadeTotal'] = number_format($rowTotais['quantidadeTotal'],2,',','.');
            $rowTotais['cubagemTotal'] = number_format($rowTotais['cubagemTotal'],2,',','.');

            ////////////////////////////////////////////////////////////
            // VERIFICA QUANTAS PAGINAS SERÃO IMPRESSAS
            ////////////////////////////////////////////////////////////
            // Encontra o total das linhas a ser impressa
            $Total_Linhas=mysql_num_rows($Resultado);
            if (!empty($mT005['T005_Mao_Obra_1'])) {
                $Total_Linhas+=4;
            }

            if ($Total_Linhas==0) {
            //-- SE NAO RETORNAR NADA PARA IMPRIMIR NO CORPO DO RELATORIO, TOTAL_LINHAS É DEFINIDO PARA 1,
            //-- PARA QUE SEJA IMPRESSO O CABECALHO.
            $Total_Linhas=1;
            }

            if ($Total_Linhas>=1) {

                if (count($mLimit)==0) {
                    // Encontra o numero de paginas cheias
                    $Total_Paginas = intval($Total_Linhas / $Linhas_Normais);
                    // Encontra o numero de itens restantes
                    if ($Total_Paginas>=1) {
                    $Resto = ($Total_Linhas - ($Total_Paginas * $Linhas_Normais));
                    } else {
                    $Resto = $Total_Linhas;
                    }

                    // Verifica caso o resto seja maior que o número máximo de itens que cabe na última folha
                    if ($Resto > $Linhas_Ultima_Pagina) {
                    // Sendo maior soma mais duas folhas
                    $Total_Paginas += 2 ;
                    } else {
                    // Sendo menor soma mais uma folha
                    $Total_Paginas += 1 ;
                    }
                } else {
                    $Total_Paginas=count($mLimit);

                    $Linhas_Normais=$mLimit[0];
                }
            }

            //////////////////////////////////////////////////////////////

            // Inicia com o tamanho maximo estabelecido
            $Tamanho_Tabela_Itens=$Tamanho_Maximo_Tabela;

            // Inicializa Variavel utilizada no limit da consulta dos itens
            $Inicio=0;
            $Pagina_Atual=0;
            $Pagina_Atual++;

            echo "<br><br><br>";
            echo "<table align=center border='0' valign=top cellpading=0 cellspacing=0 width='735' bgcolor=white bgcolor=white style='border:0px solid black;'>";
                echo "<tr bgcolor=white>";
                echo "<td valign=top>";
                echo "<table align=center border='0' cellspacing=0 cellpading=0 width='100%' height=10 valign=top style='border:0px solid'>";
                echo "<tr>";
                    echo "<td bgcolor=white width=80%>";

                    if(file_exists($cArquivo_Cabecalho_Relatorio)){
                        ob_start();
                        include $cArquivo_Cabecalho_Relatorio;
                        echo ob_get_clean();
                    }else{
                        echo "Arquivo cabeçalho não encontrado: $cArquivo_Cabecalho_Relatorio<br>";
                    }

                    echo "</td>";
                    echo "<td bgcolor=white align=center width=20%>";
                        echo "<font class='fonte' style='font-size:18px;font-weight:bold'>";
                    echo "PEDIDO<BR>Nr. $T005_Id";
                        echo "</font>";
                    echo "</font>";
                    echo "<br><font class=fonte>".date("d/m/Y")."</font>";
                    echo "</td>";
                echo "</tr>";
                echo "<tr bgcolor=white>";
                    echo "<td colspan=2>";
                    echo "<table cellpadding=7 width=100% border=0>";
                        echo "<tr bgcolor=white>";
                        echo "<td colspan=3>";
                        echo "<table width=100% border=0>";
                        echo "<tr><td style='width:50%'>";
                            if ($mT005['D024_Id']>0) {
                                echo "<font class='fonte' style='font-size:12px'>Cliente : <b>{$mT005['D024_Id']}-{$mT005['D024_Nome_Empresa']}</b></font><br>";
                                echo "<font class='fonte' style='font-size:12px'>Fantasia: <b>{$mT005['D024_Nome_Fantasia']}</b></font><br>";
                                echo "<font class='fonte' style='font-size:12px'>Telefone: <b>{$mT005['D024_DDD_Telefone_1']} {$mT005['D024_Telefone_1']}</b></font><br>";
                                echo "<font class='fonte' style='font-size:12px'>CNPJ/CPF: <b>{$mT005['D024_Cnpj']}{$mT005['D024_Cpf']}</b></font><br>";
                                echo "<font class='fonte' style='font-size:12px'>Endereco: <b>{$mT005['D024_Endereco']}, {$mT005['D024_Numero']} {$mT005['D024_Complemento']}</font></b><br>";
                                echo "<font class='fonte' style='font-size:12px'>Cep/Bairro: <b>{$mT005['D024_Cep']} - {$mT005['D024_Bairro']}</font></b><br>";
                                echo "<font class='fonte' style='font-size:12px'>Cidade/UF: <b>{$mT005['D020_Nome_Cidade']} / {$mT005['D018_UF']}</font></b><br>";
                                if (!empty($mT005['T005_Nome_Comprador'])) {
                                    echo "<font class='fonte'>Comprador:</font> ";
                                    echo "<font class='fonte' style='font-size:12px'>{$mT005['T005_Nome_Comprador']}</font> <font class=fonte>{$mT005['T005_E_mail_Comprador']}</font><br>";
                                }
                            } else {
                                echo "<font class='fonte' style='font-size:12px'><b>{$mT005['T005_Nome_Cliente']}</b></font><br>";
                                echo "<font class='fonte' style='font-size:12px'>TELEFONE: {$mT005['T005_Telefone']} - FAX: {$mT005['T005_Fax']}</font><br>";
                                if (!empty($mT005['T005_Nome_Comprador'])) {
                                    echo "<font class='fonte'>Comprador:</font> ";
                                    echo "<font class='fonte' style='font-size:12px'>{$mT005['T005_Nome_Comprador']}</font> <font class=fonte>{$mT005['T005_E_mail_Comprador']}</font>";
                                }
                            }
                            //if (!empty($mT005['T005_Observacao'])) {
                                //echo "<br><font class='fonte' style='font-size:12px'><b>$mT005['T005_Observacao']</b></font>";
                            //}
                        echo "</td>";
                        echo "<td align=left valign=top style='width:50%'>";
                                    echo "<font class='fonte' style='font-size:12px'>Vendedor: <b>{$mT005['C007_Nome']}</b></font><br>";
                                    echo "<font class='fonte' style=\"color: var(--BGVermelho);font-size:18px;font-weight:bold\">{$mT005['D022_Nome_Empresa']}</font>";
                            if (!empty($mT005['D036_Tipo_Transporte'])) {
                            echo "<br /><font class=fonte style=\"font-size:12px;\">Tipo Frete: <b>".$mT005['D036_Tipo_Transporte']."</b></font>";
                            }
                            
                            switch($mT005['T005_Flag_Frete']){
                            case '0':
                            $T005_Frete = "EMITENTE";
                            break;
                            case '1':
                            $T005_Frete = "DESTINATÁRIO";
                            break;
                            case '2':
                            $T005_Frete = "TERCEIROS";
                            break;
                            case '9':
                            $T005_Frete = "SEM FRETE";
                            break;
                            default:
                            $T005_Frete = "NãO INFORMADO";
                            break;
                            }
                            echo "<br /><font class=fonte style=\"font-size:12px\">Frete: <b>{$T005_Frete}</b></font>";
                            if (!empty($mT005['T005_Prazo_Entrega'])) {
                            echo "<br /><font class=fonte style=\"font-size:12px\">Entrega: <b>".$mT005['T005_Prazo_Entrega']."</b></font>";
                            }
                            if (!empty($mT005['T005_Ordem_Compra'])) {
                            echo "<br /><font class=fonte style=\"font-size:12px\">OC Cliente: <b>".$mT005['T005_Ordem_Compra']."</b></font>";
                            }
                            if (!empty($mT005['T005_Data_Entrega'])) {
                            $mT005['T005_Data_Entrega']=gCorrigeData($mT005['T005_Data_Entrega']);
                            echo "<br /><font class=fonte style=\"font-size:12px;\">Entrega: <b>".$mT005['T005_Data_Entrega']."</b></font>";
                            }
                            if (!empty($mT005['T005_Canal_Vendas_Ecommerce'])) {
                                echo "<br /><font class=fonte style=\"font-size:12px;\">Canal de vendas: <b>".$mT005['T005_Canal_Vendas_Ecommerce']."</b></font>";
                            }
                            
                        echo "</td></tr>";
                        echo "<tr><td colspan=2>";
                            if (!empty($mT005['T005_Observacao_1'])) {
                                $obs = preg_replace('/\s+/', ' ', $mT005['T005_Observacao_1']);
                                $obs = strip_tags($obs);
                                echo "<br><font class='fonte' style='font-size:14px;background:yellow;'><b>{$obs}</b></font>";
                            }
                        echo "</td></tr>";
                        echo "<tr><td colspan=2>";
                            if (!empty($mT005['D024_Observacao_Comercial'])) {
                                $obs = preg_replace('/\s+/', ' ', $mT005['D024_Observacao_Comercial']);
                                $obs = strip_tags($obs);
                                echo "<br><font class='fonte' style='font-size:14px;'><b>{$obs}</b></font>";
                            }
                        echo "</td></tr>";
                        echo "<tr><td colspan=2>";
                            echo "<br /><font class=fonte style=\"font-size:12px\"><b>Itens: {$rowTotais['totalItens']} - Quantidade total: {$rowTotais['quantidadeTotal']} - Cubagem: {$rowTotais['cubagemTotal']} m3 - Peso Líquido: {$rowTotais['pesoliquidoTotal']} - Peso Bruto: {$rowTotais['pesobrutoTotal']}</b></font>";
                        echo "</td></tr>";
                        echo "</table>";
                        echo "</td>";
                    echo "</tr>";
                    echo "</table>";
                echo "</td>";
                echo "</tr>";
            echo "</table>";
            echo "</td>";
            echo "</tr>";

            //////////////////////////////////////////////////////////////////////////////////////////////
            // CORPO DO RELATORIO
            //////////////////////////////////////////////////////////////////////////////////////////////
            echo "<tr valign=top height=100% bgcolor=white>";
            echo "<td valign=top>";

                echo "<table align=center cellspacing=1 cellpadding=3 border=0 width=100% bgcolor=black valign=top>";

                echo "<tr bgcolor=#f4f4f4>";
                /*echo "<td align='center' valign='center' width='30'";
                    echo "<font class='fonte'><b>Id</b></font>";
                    echo "</td>"; */

                    if($g['C031']['exibirFotoImpressaoVenda'] == 'S'){
                        echo "<td align='center' valign='center' width='30'";
                        echo "<font class='fonte'><b>Foto</b></font>";
                        echo "</td>";
                    }

                    echo "<td align='center' valign='center' width='30'";
                    echo "<font class='fonte'><b>Código</b></font>";
                    echo "</td>";

                    echo "<td align='center' valign='center' width='30'";
                    echo "<font class='fonte'><b>Cód.Cli</b></font>";
                    echo "</td>";

                    echo "<td align='center' valign='center' width='30'";
                    echo "<font class='fonte'><b>Cód.Forn</b></font>";
                    echo "</td>";

                    echo "<td align='left' valign='center'>";
                    echo "<font class='fonte'><b>Descrição Produto</b></font>";
                    echo "</td>";

                    echo "<td align='left' valign='center'>";
                    echo "<font class='fonte'><b>Marca</b></font>";
                    echo "</td>";

                    echo "<td align='center' valign='center' width='20'>";
                    echo "<font class='fonte'><b>Un</b></font>";
                    echo "</td>";

                    echo "<td align='center' valign='center' width='40'>";
                    echo "<font class='fonte'><b>Quant.</b></font>";
                    echo "</td>";

                    echo "<td align='center' valign='center' width='40'>";
                    echo "<font class='fonte'><b>Quant. Separado</b></font>";
                    echo "</td>";

                    echo "<td align='center' valign='center' width='40'>";
                    echo "<font class='fonte'><b>Estoque Atual</b></font>";
                    echo "</td>";

                    if($mT005['T005_Flag_Status'] == '10'){
                        echo "<td align='center' valign='center' width='40'>";
                        echo "<font class='fonte'><b>Separado</b></font>";
                        echo "</td>";
                    }
                    
                    echo "<td align='center' valign='center' width='140px'>";
                    echo "<font class='fonte'><b>Local</b></font>";
                    echo "</td>";

                    echo "<td align='center' valign='center' width='100'>";
                    echo "<font class='fonte'><b>Obs. Logística</b></font>";
                    echo "</td>";

                echo "</tr>";


                $cClausula_Limit = $cClausula;
                $Resultado_Itens = mysqli_query($cClausula_Limit);

                $Sequencial=0;
                $nTotal_Linhas_Impressas=0;

                while ($mResultado=mysql_fetch_array($Resultado_Itens)) {

                $mResultado['T006_Descricao_Produto']=stripslashes($mResultado['T006_Descricao_Produto']);

                echo "<tr bgcolor=white height=25>";

                    /*echo "<td align='center' valign='center'>";
                    echo "<font class='fonte'>{$mResultado['T006_Id']}</font>";
                    echo "</td>"; */

                    if($g['C031']['exibirFotoImpressaoVenda'] == 'S'){
                    $foto = cad002FotoProduto($mResultado['D001_Id'],60);
                    echo "<td align='center' valign='center'>";
                        echo $foto;
                    echo "</td>";
                    }


                    echo "<td align='center' valign='center' width='30'>";
                    echo "<font class='fonte'>{$mResultado['T006_Codigo_Produto']}<br>{$mResultado['D049_Modelo']}</font>";
                    echo "</td>";

                    echo "<td align='center' valign='center' width='30'>";
                    echo "<font class='fonte'>{$mResultado['T006_Codigo_Substituto']}</font>";
                    echo "</td>";

                    echo "<td align='center' valign='center' width='30'>";
                    echo "<font class='fonte'>{$mResultado['Cod_Forn']}</font>";
                    echo "</td>";

                    echo "<td align='left' valign='center'>";
                    echo "<font class='fonte'><b>{$mResultado['T006_Descricao_Produto']}</b></font>";
                    $sqlT204 = mysqli_query("SELECT * FROM T204 WHERE T204_T006_Id = '{$mResultado['T006_Id']}'");
                    if(mysql_num_rows($sqlT204) > 0){
                        while($rowT204 = mysql_fetch_array($sqlT204)){
                        echo "<br><font class='fonte'>{$rowT204['T204_Descricao']} - ".number_format($rowT204['T204_Valor_Unitario'],2,',','.')." - {$rowT204['T204_Observacao']}</font>";
                        }	
                    }
                    /*
                    if (!empty($mResultado['D003_Imagem'])) {
                        $mResultado['D003_Imagem'] = explode(".", $mResultado['D003_Imagem']);
                        $dir = "/hardness3/dados_usuarios/{$Banco_Dados}/produtos/linhas/{$mResultado['D003_Imagem'][0]}-100.{$mResultado['D003_Imagem'][1]}";
                        echo "<br /><img src=\"{$dir}\" />";
                    }
                    */
                    
                    echo "</td>";

                    echo "<td align='left' valign='center'>";
                    echo "<font class='fonte'>{$mResultado['D082_Marca']}</font>";
                    echo "</td>";

                    echo "<td align='left' valign='center'>";
                    echo "<font class='fonte'>{$mResultado['D037_Unidade']}</font>";
                    echo "</td>";

                    echo "<td align='center' valign='center' width='40'>";
                    echo "<font class='fonte'><b><big>".number_format($mResultado['T006_Quantidade'],2,',','.')."</big></b></font>";
                    echo "</td>";

                    echo "<td align='center' valign='center' width='40'>";
                    if ($mResultado['T006_Quantidade_Separacao'] > 0) {
                        $separado = true;
                        echo "<font class='fonte' style='font-size: 15px; font-weight: bold;'>".number_format($mResultado['T006_Quantidade_Separacao'],2,',','.')."</font>";
                    } else {
                        echo "<font class='fonte'>".number_format($mResultado['T006_Quantidade_Separacao'],2,',','.')."</font>";
                    }
                    echo "</td>";

                    echo "<td align='center' valign='center' width='40'>";
                    echo "<font class='fonte'>".number_format($mResultado['D009_Quantidade_Estoque_Real'],2,',','.')."</font>";
                    echo "</td>";

                    if($mT005['T005_Flag_Status'] == '10'){
                        echo "<td align='center' valign='center' width='40'>";
                        echo "<font class='fonte'><b><big>".number_format($mResultado['Separado'],2,',','.')."</big></b></font>";
                        echo "</td>";
                    }

                    echo "<td align='left' valign='center'>";
                    echo "<font class='fonte'>";
                        $T066Ids = $mResultado['T066_Id_Mais'];
                        // Felipe Kadanos - 24/02/2025 - Melhoria EUROSUL FORNECEDO - 46452
                        // Personalizado a pedido do Miguel
                        /* $T066=mysqli_query("SELECT T066_Id,
                                                   T066_Quantidade_Estoque,
                                                   D004_Local,
                                                   T066A_Data_Validade,
                                                   D001A_Flag_Validade,
                                                   (SELECT T066A_Numero_Lote FROM T066 LEFT JOIN T066A ON T066_Id = T066A_T066_Id WHERE T066_D009_Id='{$mResultado['T006_D009_Id']}' AND T066A_Numero_Lote > 0 AND T066A_Data_Validade > 0 ORDER BY T066A_Data_Validade ASC LIMIT 1) AS T066A_Numero_Lote
                                              FROM T066
                                         LEFT JOIN D004 ON D004_Id=T066_D004_Id 
                                         LEFT JOIN T066A ON T066_Id = T066A_T066_Id
                                         LEFT JOIN D009 ON D009_Id = T066_D009_Id
                                         LEFT JOIN D049 ON D049_Id = D009_D049_Id
                                         LEFT JOIN D001 ON D001_Id = D049_D001_Id
                                         LEFT JOIN D001A ON D001_Id = D001A_D001_Id
                                             WHERE T066_D009_Id='{$mResultado['T006_D009_Id']}' 
                                          ORDER BY D004_Local");
                        if(mysql_num_rows($T066) > 1){

                            echo "<table cellspacing=0 border=0 cellpadding=0 class=fonte>";    

                            while($mT066=mysql_fetch_array($T066)){

                                $dataValidade = ''; 
                                if($mT066['D001A_Flag_Validade'] == 'S'){
                                   if($mT066['T066A_Data_Validade'] != '0000-00-00' && $mT066['T066A_Data_Validade'] != ''){
                                        $mT066['T066A_Data_Validade'] = date("d/m/y", strtotime($mT066['T066A_Data_Validade']));
                                        $dataValidade = '<span style="background-color: yellow;">(V: '.$mT066['T066A_Data_Validade'].')</span>';
                                    } else{
                                        $dataValidade = '(V: Não inf.)';
                                    }
                                }

                                $mT066['T066_Quantidade_Estoque'] = number_format($mT066['T066_Quantidade_Estoque'],2,',','.');
                                $style = ($mResultado['T006_T066_Id'] == $mT066['T066_Id']) ? 'font-weight: bold; font-style: italic; text-decoration: underline;' : '';
                               if($mT066['D001A_Flag_Validade'] == 'S'){
                                    echo "<tr style='{$style} font-size:12px; background-color: yellow;'><td>{$mT066['T066A_Numero_Lote']}</td><td>   </td><td>{$mT066['D004_Local']}<td><td>   </td><td>{$mT066['T066_Quantidade_Estoque']}</td><td>   {$dataValidade}</td>";
                               } else {
                                    echo "<tr style='{$style} font-size:12px; background-color: yellow;'><td>{$mT066['T066A_Numero_Lote']}</td><td>   </td><td>{$mT066['D004_Local']}<td><td>   </td><td>{$mT066['T066_Quantidade_Estoque']}</td><td>   {$dataValidade}</td>";
                               }
                            }
                            echo "</table>";
                        } else {
     
                            $mT066=mysql_fetch_array($T066);

                            $dataValidade = '';
                            if($mT066['D001A_Flag_Validade'] == 'S'){
                                log("qual é a validade ".$mT066['T066A_Data_Validade']." e o local".$mT066['D004_Local']);
                               if($mT066['T066A_Data_Validade'] != '0000-00-00' || $mT066['T066A_Data_Validade'] != ''){
                                    $dataValidade = '(Val:'.gCorrigeData($mT066['T066A_Data_Validade']).')';
                                } else{
                                    $dataValidade = '(Val:Não inform.)';
                                }
                            }
                            $style = "font-weight: bold; font-style: italic; text-decoration: underline; background-color: yellow;";
                            //echo  $mT066['D004_Local'].' '.$dataValidade;
                            echo '<span style="' . $style . '">' . $mT066['T066A_Numero_Lote'] . ' ' . $mT066['D004_Local'] . ' ' . $dataValidade . '</span>';
                        } */

                        /* $sql = "SELECT T066_Id, T066A_Numero_Lote, D004_Local, T066_Quantidade_Estoque, T066A_Data_Validade, D001A_Flag_Validade
                                FROM T066
                                    LEFT JOIN D004 ON D004_Id=T066_D004_Id 
                                    LEFT JOIN D009 ON D009_Id = T066_D009_Id
                                    LEFT JOIN D049 ON D049_Id = D009_D049_Id
                                    LEFT JOIN D001 ON D001_Id = D049_D001_Id
                                    LEFT JOIN D001A ON D001_Id = D001A_D001_Id
                                    LEFT JOIN T066A ON T066_Id = T066A_T066_Id
                                WHERE T066_D009_Id = '{$mResultado['T006_D009_Id']}'
                                    AND T066_Quantidade_Estoque > 0";

                        $query = $sql . " AND T066A_Data_Validade > 0 AND D001A_Flag_Validade = 'S' ORDER BY T066A_Data_Validade ASC, T066_Id ASC";

                        // Se tem validade, Pega loc com menor validade
                        log("sql loc 1: ".$query);
                        $T066 = mysqli_query($query);

                        if (mysql_num_rows($T066) <= 0) {
                            // Se não tem validade, Pega primeira loc sem lote
                            $query = $sql . " AND (T066A_Numero_Lote <= 0 OR T066A_Numero_Lote = '' OR T066A_Numero_Lote IS NULL) ORDER BY T066A_Numero_Lote ASC, T066_Id ASC";
                            log("sql loc 2: ".$query);
                            $T066 = mysqli_query($query);

                            // Se tem lote, Pega lote mais antigo
                            $query = $sql . " AND T066A_Numero_Lote > 0 ORDER BY T066_Id ASC";
                            log("sql loc 3: ".$query);
                            $temLote = mysqli_query($query);
                            
                            $T066 = (mysql_num_rows($temLote) > 0) ? $temLote : $T066;
                        }

                        echo "<table cellspacing=0 border=0 cellpadding=0 class=fonte>";
                        while ($mT066 = mysql_fetch_assoc($T066)) {

                            $style = ($mResultado['T006_T066_Id'] == $mT066['T066_Id']) 
                                ? "font-weight: bold; font-style: italic; text-decoration: underline;" 
                                : "";

                            if ($mT066['D001A_Flag_Validade'] == 'S') {
                                if ($mT066['T066A_Data_Validade'] > 0) {
                                    $dt = date("d/m/y", strtotime($mT066['T066A_Data_Validade']));
                                    $dataValidade = "<span style='background-color: yellow;'>(V: " . $dt . ")</span>";
                                } else {
                                    $dataValidade = "(V: Não inf.)";
                                }
                            }
                            
                            echo "<tr style='{$style} font-size:12px; background-color: yellow;'>
                                    <td>{$mT066['T066A_Numero_Lote']}</td>
                                    <td>   </td>
                                    <td style='white-space:nowrap;'>{$mT066['D004_Local']}</td>
                                    <td>   </td>
                                    <td>{$mT066['T066_Quantidade_Estoque']}</td>
                                    <td>   {$dataValidade}</td>
                                </tr>";
                        }

                        echo "</table>"; */

                        /* $select = "SELECT T066_Id, T066A_Numero_Lote, D004_Local, T066_Quantidade_Estoque, T066A_Data_Validade, D001A_Flag_Validade";
                        $sql = "{$select}
                                FROM T066
                                    LEFT JOIN D004 ON D004_Id = T066_D004_Id
                                    LEFT JOIN D009 ON D009_Id = T066_D009_Id
                                    LEFT JOIN D049 ON D049_Id = D009_D049_Id
                                    LEFT JOIN D001 ON D001_Id = D049_D001_Id
                                    LEFT JOIN D001A ON D001_Id = D001A_D001_Id
                                    LEFT JOIN T066A ON T066_Id = T066A_T066_Id
                                WHERE T066_D009_Id = '{$mResultado['T006_D009_Id']}'
                                    AND T066_Quantidade_Estoque > 0
                                    AND D004_Flag_Somar_Estoque_Fisico != 'N'
                                    AND IFNULL(T066A_Flag_Ativo,'S') = 'S'";

                        $query1 = $sql . " AND T066A_Data_Validade > 0 AND D001A_Flag_Validade = 'S'";
                        $query2 = $sql . " AND (T066A_Numero_Lote <= 0 OR T066A_Numero_Lote = '' OR T066A_Numero_Lote IS NULL)";
                        $query3 = $sql . " AND T066A_Numero_Lote > 0";

                        $query = "$select
                                FROM (
                                        ({$query1})
                                        UNION ALL
                                        ({$query2})
                                        UNION ALL
                                        ({$query3})
                                ) LOC
                                GROUP BY T066_Id
                                ORDER BY
                                    CASE
                                        WHEN T066A_Data_Validade = '0000-00-00' OR T066A_Data_Validade IS NULL THEN 1
                                        ELSE 0
                                    END,
                                    T066A_Data_Validade ASC,
                                    T066A_Numero_Lote ASC,
                                    D004_Local ASC,
                                    T066_Id ASC";

                        // Junta as 3 regras em uma única query
                        log("sql loc: " . $query);

                        // passe a conexão mysqli corretamente
                        $T066 = mysqli_query($query); */

                        require_once('bibliotecas/classes/CAD002.php');

                        $CAD002 = new CAD002();
                        $locacoes = $CAD002->getLocacoesPrioridadeFIFO($mResultado['T006_D009_Id']);
                        log(json_encode($locacoes));

                        $qtdPedido = (float)$mResultado['T006_Quantidade'];
                        $qtdRestante = $qtdPedido;
                        $first = true;
                        echo "<table cellspacing=0 border=0 cellpadding=0 class=fonte>";

                        // quantidade do pedido = $mResultado['T006_Quantidade']
                        // while ($mT066 = mysql_fetch_assoc($T066)) {
                        foreach ($locacoes as $mT066) {
                            // se já atendeu todo o pedido, para
                            if ($qtdRestante <= 0) {
                                break;
                            }

                            $qtdEstoque = (float)$mT066['T066_Quantidade_Estoque'];

                            if ($qtdEstoque <= 0) {
                                continue;
                            }

                            // define quanto será usado desta locação
                            $qtdSeparar = min($qtdEstoque, $qtdRestante);
                            $qtdRestante -= $qtdSeparar;

                            $style = '';
                            if ($first) {
                                $style = "font-weight: bold; font-style: italic; text-decoration: underline;"; $first = false;
                            }

                            if ($mT066['D001A_Flag_Validade'] == 'S') {
                                if ($mT066['T066A_Data_Validade'] > 0) {
                                    $dt = date("d/m/y", strtotime($mT066['T066A_Data_Validade']));
                                    $dataValidade = "<span style='background-color: yellow;'>(V: {$dt})</span>";
                                } else {
                                    $dataValidade = "(V: Não inf.)";
                                }
                            }

                            echo "<tr style=\"{$style} font-size:12px; background-color: yellow;\">
                                    <td>{$mT066['T066A_Numero_Lote']}</td>
                                    <td>   </td>
                                    <td style='white-space:nowrap;'>{$mT066['D004_Local']}</td>
                                    <td>   </td>
                                    <td>{$mT066['T066_Quantidade_Estoque']}</td>
                                    <td>   {$dataValidade}</td>
                                </tr>";
                        }

                        echo "</table>";
                        
                    echo "</font>";
                    echo "</td>";

                    echo "<td align='left' valign='center'>";
                    echo "<font class='fonte'>{$mResultado['D001A_Observacao_Logistica']}</font>";
                    echo "</td>";
                echo "</tr>";
                $nTotal_Linhas_Impressas++;
                }
            echo "</table></td></tr>";

            if ($Moeda=='D') {
                $mT005['T005_Valor_Total_Produtos']=$mT005['T005_Valor_Total_Produtos']/$mT005['T005_Valor_Dolar'];
                $mT005['T005_Valor_Frete']=$mT005['T005_Valor_Frete']/$mT005['T005_Valor_Dolar'];
                $mT005['T005_Valor_Mao_Obra']=$mT005['T005_Valor_Mao_Obra']/$mT005['T005_Valor_Dolar'];
                $mT005['T005_Valor_Despesas_Acessorias']=$mT005['T005_Valor_Despesas_Acessorias']/$mT005['T005_Valor_Dolar'];
                $mT005['T005_Valor_Desconto']=$mT005['T005_Valor_Desconto']/$mT005['T005_Valor_Dolar'];
                $mT005['T005_Valor_Total']=$mT005['T005_Valor_Total']/$mT005['T005_Valor_Dolar'];
                $mT005['T005_Valor_Total_IPI']=$mT005['T005_Valor_Total_IPI']/$mT005['T005_Valor_Dolar'];
            }

            if ($mT005['T005_Valor_Mao_Obra']>0) {

                echo "<tr bgcolor=white><td>";
                echo "<table align=center border='0' valign=top cellpading=0 cellspacing=0 width='$Width_Relatorio' bgcolor='white'>";

                if (!empty($mT005['T005_Mao_Obra_1'])) {
                echo "<tr bgcolor=white>";
                    echo "<td valign=top colspan=10>";
                        echo "<font class='fonte'>{$mT005['T005_Mao_Obra_1']}<br></font>";
                        echo "<font class='fonte'>{$mT005['T005_Mao_Obra_2']}<br></font>";
                        echo "<font class='fonte'>{$mT005['T005_Mao_Obra_3']}<br></font>";
                        echo "<font class='fonte'>{$mT005['T005_Mao_Obra_4']}</font>";
                    echo "</td>";
                echo "</tr>";

                }

                echo "</table>";

                echo "</td></tr>";

            }

            echo "<tr bgcolor=white><td>";


            ///////////////////////////////////////////////////////////////////////////////////
            // TOTALIZACAO
            ///////////////////////////////////////////////////////////////////////////////////

            $mC004['C004_Observacao_Rodape_Orcamento']=str_replace("\n","<br>",$mC004['C004_Observacao_Rodape_Orcamento']);

            echo "<font class=fonte>Sistema Hardness - www.hardness.com.br</font>";

            if ($separado && !empty($mT005['T005_Estoque_Box'])) {
                echo "<br/><br/><div style='text-align: center; font-size: 16px; font-weight: bold;'>Itens separados no box: {$mT005['T005_Estoque_Box']}</div>";
            }

            // codbarra

            /*include('../class/php-barcode.php');
            @mkdir('../dados_usuarios/codbarras');
            $im     = imagecreatetruecolor(733, 50);  
            $black  = ImageColorAllocate($im,0x00,0x00,0x00);  
            $white  = ImageColorAllocate($im,0xff,0xff,0xff);  
            imagefilledrectangle($im, 0, 0, 733, 50, $white);  
            $data = Barcode::gd($im, $black, 370, 60, 0, "code128", $T005_Id, 2, 110);  
            imagepng($im, "../dados_usuarios/codbarras/{$T005_Id}.barcode.png");
            */


            if(!empty($mT005['T005_C007_Id_Separou'])){
            $sqlC007Separador = mysqli_query("SELECT C007_Nome, T005_Data_Separacao_Pecas FROM T005 LEFT JOIN C007 ON C007_Id = T005_C007_Id_Separou WHERE T005_Id = '{$T005_Id}'");
            $rowC007Separador = mysql_fetch_array($sqlC007Separador);
            $dataHora = date('d/m/Y H:i:s', strtotime($rowC007Separador['T005_Data_Separacao_Pecas']));
            $separador = $rowC007Separador['C007_Nome'];
            } else {
            $sql = "SELECT * FROM T178 LEFT JOIN C007 ON C007_Id=T178_C007_Id WHERE T178_T005_Id='{$T005_Id}' AND T178_Descricao = 'Aguardando Separação' ORDER BY T178_Data DESC LIMIT 1";
            $resT178 = mysqli_query($sql);
            $rowT178 = mysql_fetch_assoc($resT178);
            $dataHora = date('d/m/Y H:i:s', strtotime($rowT178['T178_Data']));
            $separador = $rowT178['C007_Nome'];
            }


            echo <<<EOT
                <div class="fonte" style="fonts-size: 12px">
                <hr style="border-top: dashed 2px; margin: 55px 0 0px;" />
                <div style="margin: 15 0 0 0; padding: 0; font-weight: bold; font-size: 12px; text-align: center;">CANHOTO DE SEPARAÇÃO PARA CONFERÊNCIA</div><br/>
                <!--<img src="/hardness/class/barcodegen/html/image.php?filetype=PNG&dpi=72&scale=2&rotation=0&font_family=Arial.ttf&font_size=8&text={$T005_Id}&thickness=17&start=NULL&code=BCGcode128" style="margin: 10px 0 0 40%;" />-->
                <div style="float: right; margin: 20px 30px;">
EOT;
            $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
            echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($T005_Id, $generator::TYPE_CODE_39)) . '" style="height: 50px; width: 200px;">';

            if (!empty($mT005['T005_Observacao_1'])) {
                $obs = preg_replace('/\s+/', ' ', $mT005['T005_Observacao_1']);
                $obs = strip_tags($obs);
                $obsFooter = "<div class='fonte' style='width: fit-content; margin-top: 20px; padding: 2px 4px; font-size: 12px; font-weight: bold; background:yellow;'>{$obs}</div>";
            }

            echo <<<EOT
                </div>
                <table cellspacing=6>
                <tr>
                    <td class="fonte" style="font-size: 12px">Pedido:    </td>
                    <td class="fonte" style="font-size: 12px; font-weight: bold;">{$T005_Id}</td>
                </tr>
                <tr>
                    <td class="fonte" style="font-size: 12px">Separador:    </td>
                    <td class="fonte" style="font-size: 12px; font-weight: bold;">{$separador}</td>
                </tr>
                <tr>
                    <td class="fonte" style="font-size: 12px">Data/Hora Separação:    </td>
                    <td class="fonte" style="font-size: 12px; font-weight: bold;">{$dataHora}</td>
                </tr>
                <tr>
                    <td class="fonte" style="font-size: 12px">Transportadora:    </td>
                    <td class="fonte" style="font-size: 12px; font-weight: bold;">{$mT005['D022_Nome_Empresa']}</td>
                </tr>
                <tr>
                    <td class="fonte" style="font-size: 12px">Cliente:    </td>
                    <td class="fonte" style="font-size: 12px; font-weight: bold;">{$mT005['D024_Id']} - {$mT005['D024_Nome_Empresa']}</td>
                </tr>
                </table>
                {$obsFooter}
                </div>
                </table>
                </table>
                </table>                
EOT;
            if(count($T005_Id_Array) > 1 && end($T005_Id_Array) !== $T005_Id) {
                echo <<<EOT
                    <div class="pagebreak"> </div>
                EOT;
            }

            if($g['C031']['modeloExpedicao'] == 2){
                // Só da UPDATE no modelo 2 porque só lista no grid de expedicao esta campo JSON para o modelo 2
                $T005A = mysql_query("SELECT T005A_Id FROM T005A WHERE T005A_T005_Id = '{$T005_Id}'");
                if(mysql_num_rows($T005A) <= 0 ){
                    mysql_query("insert into T005A(T005A_T005_Id) VALUES ('{$T005_Id}')");
                }
                mysql_query("UPDATE T005A
                                SET T005A_JSON = JSON_SET(IFNULL(T005A_JSON, '{}'), -- Se o campo JSON for nulo ou ausente, cria um JSON vazio
                                          '$.Quantidade_Impressao',
                                          CAST(IFNULL(JSON_UNQUOTE(JSON_EXTRACT(T005A_JSON, '$.Quantidade_Impressao')), 0) + 1 AS SIGNED)
                                          )
                              WHERE T005A_T005_Id = '{$T005_Id}'");
            }
        }
        
        echo "</body></html>";














