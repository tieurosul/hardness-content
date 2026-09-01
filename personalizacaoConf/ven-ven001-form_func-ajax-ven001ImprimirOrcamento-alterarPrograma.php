<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /ven-ven001-form_func-ajax-ven001ImprimirOrcamento/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/
$resposta = array('code' => true, 'data' => array());
    global $g;

    $T003_Id = isset($r_T003_Id) ? $r_T003_Id : false;
    $alcadaUpdate = isset($r_alcadaUpdate) ? $r_alcadaUpdate : false;
    $moeda = isset($r_moeda) ? $r_moeda : false;
    $descIngles = isset($r_descIngles) ? $r_descIngles : false;
    error_log("Desc Ingles: ".$descIngles);
    
    if (isset($r_gerarHtml)) {
        echo $VEN001->montarEmailPdf($T003_Id, true, $moeda, $descIngles);
        die();
    } else {
        $T089 = mysql_query("SELECT SUM(T089_Valor_Parcela) as TotalParcelas, T003_Valor_Total FROM T089 LEFT JOIN T003 ON T003_Id = T089_T003_Id WHERE T089_T003_Id='{$T003_Id}'");
        $resT089 = mysql_fetch_array($T089);

        $sql="SELECT D006_Flag_Gera_Contas FROM T003 LEFT JOIN D006 on D006_Id=T003_D006_Id WHERE T003_Id='{$T003_Id}'";
        $resultado=mysql_query($sql);
        $row=mysql_fetch_array($resultado);

        if($row['D006_Flag_Gera_Contas'] == "R" && $g['C031']['permitirOrcamentoEmailImpSemPagamento'] == 'N'){
            if($resT089['TotalParcelas'] == 0){
                $erro = "É necessário preencher a forma de pagamento";
            } else {
                if($resT089['T003_Valor_Total'] != $resT089['TotalParcelas']){
                    $erro = "Valor das parcelas não confere com o total do pedido.";
                }
            }
        }
        if(isset($erro)){
            $resposta['code'] = false;
            $resposta['data'] = $erro;
        } else {
            $retorno = $VEN001->montarEmailPdf($T003_Id, false, $moeda, $descIngles);
            $resposta['code'] = true;
            $resposta['data'] = isset($retorno['AnexosWeb'][0]) ? $retorno['AnexosWeb'][0] : '';
        }
    }
echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
