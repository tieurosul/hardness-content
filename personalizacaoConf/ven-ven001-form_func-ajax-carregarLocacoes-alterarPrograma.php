<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /ven-ven001-form_func-ajax-carregarLocacoes/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

    // Felipe Kadanos - 17/03/2026 - Melhoria EUROSUL FORNECEDO - 46516
    // Personalizado para seguir as regras de sugestão de locação que foram ajustada na impressão do pedido na expedição

$resposta = array('code' => true, 'data' => array());

        $D009_Id = mysql_real_escape_string($r_D009_Id);

        /* $D001 = mysql_query("SELECT D001A_Id 
                                FROM D001A
                            LEFT JOIN D001 ON D001_Id = D001A_D001_Id
                            LEFT JOIN D049 ON D001_Id = D049_D001_Id
                            LEFT JOIN D009 ON D049_Id = D009_D049_Id
                                WHERE D009_Id = '{$D009_Id}'
                                AND D001A_Flag_Validade ='S'");
                                
        if(mysql_num_rows($D001) > 0){
            // Se tiver controle de validade vem selecionado a locacao com a data de validade mais antiga
            $extraD004 = "LEFT JOIN D004  ON T066_D004_Id  = D004_Id 
                            LEFT JOIN T066A ON T066A_T066_Id = T066_Id 
                                WHERE T066_D009_Id = '{$D009_Id}'
                                AND T066A_Data_Validade != '0000-00-00'
                            ORDER BY IF(T066A_Data_Validade = '0000-00-00','9999-99-99', T066A_Data_Validade) ASC, T066_Quantidade_Estoque_Liquido DESC, T066_Id ASC";

            $resposta['data']['T004A_T066_Id']            = gProcessaSelect(gGeraSelect('T066','T066_Id','D004_Local,format(T066_Quantidade_Estoque_Liquido,0),if(T066A_Data_Validade = "0000-00-00","", concat("- VAL: ",DATE_FORMAT(T066A_Data_Validade,"%d/%m/%Y"))), IF(IFNULL(T066A_Numero_Lote,"") = "", "ND", T066A_Numero_Lote)', $extraD004, true, false, "LOTE: %4 - LOC: %1 (QTD:%2) %3"));
            $resposta['data']['T004A_T066_Id_refreshBtn'] = base64_encode(serialize(array('T066','T066_Id','D004_Local,T066_Id,T066A_Data_Validade', $extraD004, true, false, "%1 %3")));
        }  else {
            // Se não tiver controle de validade busca as locações do item mas não vem selecionado no formulario
            $extraD004 = "LEFT JOIN D004  ON T066_D004_Id  = D004_Id 
                            LEFT JOIN T066A ON T066A_T066_Id = T066_Id 
                                WHERE T066_D009_Id = '{$D009_Id}' 
                            ORDER BY T066_Quantidade_Estoque DESC, T066_Id ASC";
                            
            $resposta['data']['T004A_T066_Id']            = gProcessaSelect(gGeraSelect('T066','T066_Id','D004_Local,format(T066_Quantidade_Estoque_Liquido,0),if(T066A_Data_Validade = "0000-00-00","", concat("- VAL: ",DATE_FORMAT(T066A_Data_Validade,"%d/%m/%Y"))), IF(IFNULL(T066A_Numero_Lote,"") = "", "ND", T066A_Numero_Lote)', $extraD004, true, false, "LOTE: %4 - LOC: %1 (QTD:%2)"));
            $resposta['data']['T004A_T066_Id_refreshBtn'] = base64_encode(serialize(array('T066','T066_Id','D004_Local,T066_Id,T066A_Data_Validade', $extraD004, true, false, "%1 %3")));
        }	  */

        $campos = "T066_Id,
                D004_Local, 
                FORMAT(T066_Quantidade_Estoque_Liquido,0), 
                IF(T066A_Data_Validade = '0000-00-00','', CONCAT('- VAL: ',DATE_FORMAT(T066A_Data_Validade,'%d/%m/%Y'))), 
                IF(IFNULL(T066A_Numero_Lote,'') = '', 'ND', T066A_Numero_Lote)";

        $sql = "SELECT 
                    T066_Id, D004_Local, T066_Quantidade_Estoque_Liquido, T066A_Data_Validade, T066A_Numero_Lote
                FROM T066
                    LEFT JOIN D004 ON D004_Id=T066_D004_Id 
                    LEFT JOIN D009 ON D009_Id = T066_D009_Id
                    LEFT JOIN D049 ON D049_Id = D009_D049_Id
                    LEFT JOIN D001 ON D001_Id = D049_D001_Id
                    LEFT JOIN D001A ON D001_Id = D001A_D001_Id
                    LEFT JOIN T066A ON T066_Id = T066A_T066_Id
                WHERE T066_D009_Id = '{$D009_Id}'
                    AND IFNULL(D004_Flag_Somar_Estoque_Fisico, 'S') != 'N'
                    AND IFNULL(T066A_Flag_Ativo,'S') = 'S'";
                    #AND T066_Quantidade_Estoque > 0

        $query1 = $sql . " AND T066A_Data_Validade > 0 AND D001A_Flag_Validade = 'S'";
        $query2 = $sql . " AND (T066A_Numero_Lote <= 0 OR T066A_Numero_Lote = '' OR T066A_Numero_Lote IS NULL)";
        $query3 = $sql . " AND T066A_Numero_Lote > 0";

        $from = "(({$query1}) UNION ALL ({$query2}) UNION ALL ({$query3})) LOC";

        $extra = "GROUP BY
                        T066_Id
                    ORDER BY
                        CASE
                            WHEN T066A_Data_Validade = '0000-00-00' OR T066A_Data_Validade IS NULL 
                            THEN 1
                            ELSE 0
                        END,
                        T066A_Data_Validade ASC,
                        T066A_Numero_Lote ASC,
                        D004_Local ASC,
                        T066_Id ASC";

        // executando a query para pegar o ID da locação que vira selected no campo da locação
        /* $query = "SELECT {$campos} FROM {$from} {$extra}"; log("query locacao: ".$query);
        $loc = mysql_fetch_assoc(mysqli_query($query)); */
        require_once('bibliotecas/classes/CAD002.php');
        $CAD002 = new CAD002();
        $locacoes = $CAD002->getLocacoesPrioridadeFIFO($D009_Id);
        $loc = reset($locacoes);

        $resposta['data']['T004A_T066_Id'] = gProcessaSelect(gGeraSelect($from, 'T066_Id', $campos,  $extra,  true, $loc['T066_Id'], "LOTE: %5 - LOC: %2 (QTD:%3) %4"));
        $resposta['data']['T004A_T066_Id_refreshBtn'] = base64_encode(serialize(array($from, 'T066_Id', $campos, $extra, true, $loc['T066_Id'], "%2 %4")));

echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";







