<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /etc-etc016-ajax-etc016SqlCodVar/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

$resposta = array('code' => true, 'data' => array());
    $codbarra = ltrim($r_codbarra, '0');
    $sql_codvar = base64_decode($r_sql_codvar);
    $sql_codvar = str_replace('[[CODVAR]]', $codbarra, $sql_codvar);

    $res = mysql_query($sql_codvar);
    if (!$res || mysql_num_rows($res) == 0) {
        $resposta['code'] = false;
        $resposta['data'] = "ID não econtrado no sistema!";
    } else {
        // Felipe Kadanos - 22/01/2026
        // Adicionada validação quando o pedido é bipado, a pedido do Miguel
        $sql = "SELECT T005_Flag_Status, T005_Nome_Status FROM T005 WHERE T005_Id = '{$codbarra}'";
        $rowT005 = mysql_fetch_assoc(mysql_query($sql));

        if($rowT005['T005_Flag_Status'] != '10'){
            $resposta['code'] = false;
            $resposta['data'] = "Pedido {$r_T005_Id} já foi conferido, está com o status <b>{$rowT005['T005_Nome_Status']}</b>";
        } else {
            $resposta['data'] = base64_encode($sql_codvar);
        }
    }
echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";


