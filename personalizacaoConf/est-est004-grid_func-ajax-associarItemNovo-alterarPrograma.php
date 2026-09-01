<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /est-est004-grid_func-ajax-associarItemNovo/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

$resposta = array('code' => true, 'data' => array());

    // Felipe Kadanos - 25/02/2026 - Melhoria EUROSUL FORNECEDO - 46468
    // Ao vincular OC no item, seta locação para PENDENTE
    global $g;
    require_once('bibliotecas/classes/GFormWrap.php');
    $form = new GFormWrap(array('D009_Id' => $r_D009_Id, 'T014_Id' => $r_T014_Id));
    // Associar o Produto com o novo código do fornecedor
    $retorno = $EST004->associarProduto($form);
    if ($retorno !== true) {
        $resposta['code'] = false;
        $resposta['data'] = $retorno;
        } else {
            // Já faz a associação com a OC
            $sql = "SELECT T014_Quantidade FROM T014 WHERE T014_Id = '{$r_T014_Id}'";
            $mT014 = mysql_query($sql);
            $mT014 = mysql_fetch_assoc($mT014);
            $sql = "SELECT T225_Id,T225_Quantidade,T225_Quantidade_Pendente_3(T225_Id) as Pendente, T225_D006_Id FROM T225 WHERE T225_Id = '{$r_T225_Id}'";
            $mT225 = mysql_query($sql);
            $mT225 = mysql_fetch_assoc($mT225);
            if (($mT014['T014_Quantidade']<=$mT225['Pendente']) || ($g['C031']['vincularProdutoNFRecebidaOCQtdDiferentes']=="S")){
                if($g['C031']['associarCfopOcParaNf'] == 'S'){
                    mysql_query("UPDATE T014 SET T014_T225_Id='{$mT225['T225_Id']}', T014_Flag_Divergencia='S', T014_D006_Id = '{$mT225['T225_D006_Id']}' WHERE T014_Id='{$r_T014_Id}'");
                } else {
                    mysql_query("UPDATE T014 SET T014_T225_Id='{$mT225['T225_Id']}', T014_Flag_Divergencia='S' WHERE T014_Id='{$r_T014_Id}'");
                }

                $T066 = mysql_query("SELECT T066_Id FROM T066 WHERE T066_D004_Id = '1882' AND T066_D009_Id = '{$r_D009_Id}'");
                log("1- SELECT T066_Id FROM T066 WHERE T066_D004_Id = '1882' AND T066_D009_Id = '{$r_D009_Id}'");
                $loc = mysql_fetch_array($T066);
                if($loc['T066_Id'] > 0){
                    mysql_query("UPDATE T014 SET T014_T066_Id = '{$loc['T066_Id']}' WHERE T014_Id='{$r_T014_Id}'");
                    log("2- UPDATE T014 SET T014_T066_Id = '{$loc['T066_Id']}' WHERE T014_Id='{$r_T014_Id}'");
                    if ($erro = mysql_error()) {
                        $resposta['code'] = false;
                        $resposta['data'] = "Erro ao atualizar locação (D004=1882): {$erro}";
                        echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
                        die();
                    }
                } else {
                    // Se não encontrou com D004_Id = 1882, tenta buscar localização com D004_Local = 'PENDENTE'
                    $T066_Pendente = mysql_query("SELECT T066_Id FROM T066 LEFT JOIN D004 ON D004_Id = T066_D004_Id WHERE T066_D009_Id = '{$r_D009_Id}' AND D004_Local = 'PENDENTE'");
                    log("3- SELECT T066_Id FROM T066 LEFT JOIN D004 ON D004_Id = T066_D004_Id WHERE T066_D009_Id = '{$r_D009_Id}' AND D004_Local = 'PENDENTE'");
                    $loc_pendente = mysql_fetch_array($T066_Pendente);
                    if($loc_pendente['T066_Id'] > 0){
                        log("4- UPDATE T014 SET T014_T066_Id = '{$loc_pendente['T066_Id']}' WHERE T014_Id='{$r_T014_Id}'");
                        mysql_query("UPDATE T014 SET T014_T066_Id = '{$loc_pendente['T066_Id']}' WHERE T014_Id='{$r_T014_Id}'");
                        if ($erro = mysql_error()) {
                            $resposta['code'] = false;
                            $resposta['data'] = "Erro ao atualizar locação (PENDENTE): {$erro}";
                            echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
                            die();
                        }
                    } else {
                        // Se não encontrou, cria uma nova locação com D004_Local = 'PENDENTE'
                        $ins = "INSERT INTO T066 (T066_D004_Id, T066_D009_Id) VALUES ('1882', '{$r_D009_Id}')";
                        log("5- ".$ins);
                        mysqli_query($ins);
                        if ($erro = mysql_error()) {
                            $resposta['code'] = false;
                            $resposta['data'] = "Erro ao inserir locação (PENDENTE) do item {$r_T014_Id}: <br> {$erro}";
                            echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
                            die();
                        }
                        mysql_query("UPDATE T014 SET T014_T066_Id = '{$g['mysqlLastId']}' WHERE T014_Id='{$r_T014_Id}'");
                        log("6- UPDATE T014 SET T014_T066_Id = '{$g['mysqlLastId']}' WHERE T014_Id='{$r_T014_Id}'");
                        if ($erro = mysql_error()) {
                            $resposta['code'] = false;
                            $resposta['data'] = "Erro ao atualizar locação (PENDENTE) do item {$r_T014_Id}: <br> {$erro}";
                            echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
                            die();
                        }
                    }
                }
                
                // Seta a quantidade recebida da OC com o valor do item da NF
                $sql = "SELECT T014_Quantidade FROM T014 WHERE T014_Id = '{$r_T014_Id}'";
                $mT014 = mysql_query($sql);
                $mT014 = mysql_fetch_assoc($mT014);
                mysql_query("UPDATE T225 SET T225_Quantidade_Recebida='{$mT014['T014_Quantidade']}' WHERE T225_Id='{$r_T225_Id}'");
            //
            $resposta['data'] = 'Ok!';
        }else{
            $resposta['code'] = false;
            $resposta['data'] = 'A quantidade da Nota fiscal é maior que o saldo da ordem de compra. NF: '.$mT014['T014_Quantidade'].' OC: '.$mT225['Pendente'];
        }
    }

echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";



