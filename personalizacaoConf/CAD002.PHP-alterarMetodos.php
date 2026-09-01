<?php
namespace hardness;
/**
* Alterar (sobre-escrever) Métodos de uma Classe
*/

class CAD002 extends CAD002_ {
	// defina os métodos para sobreescrever

    /**
     * Retorna as locações do produto na prioridade FIFO.
     *
     * A ordem do array é exatamente a ordem definida no ORDER BY da consulta.
     * As chaves usam o identificador da locação com um prefixo para que o array
     * pai também seja associativo; os dados de cada locação são associativos.
     *
     * @param int $D009_Id
     * @return array
     */
    public function getLocacoesPrioridadeFIFO($D009_Id) {

        $sql = <<<SQL
            SELECT
                T066_Id,
                T066A_Numero_Lote,
                D004_Id,
                D004_Local,
                T066_Quantidade_Estoque,
                (
                    T066_Quantidade_Estoque
                    - D009_Quantidade_Pedido_Por_Locacao(T066_D009_Id, T066_Id)
                ) AS T066_Quantidade_Disponivel,
                T066_Quantidade_Estoque_Liquido,
                T066A_Data_Validade,
                D001A_Flag_Validade
            FROM T066
                LEFT JOIN D004 ON D004_Id = T066_D004_Id
                LEFT JOIN D009 ON D009_Id = T066_D009_Id
                LEFT JOIN D049 ON D049_Id = D009_D049_Id
                LEFT JOIN D001 ON D001_Id = D049_D001_Id
                LEFT JOIN D001A ON D001_Id = D001A_D001_Id
                LEFT JOIN T066A ON T066_Id = T066A_T066_Id
            WHERE T066_D009_Id = '{$D009_Id}'
                AND IFNULL(D004_Flag_Somar_Estoque_Fisico, 'S') != 'N'
                AND IFNULL(T066A_Flag_Ativo, 'S') = 'S'
                AND D004_Local IS NOT NULL
            GROUP BY T066_Id
            ORDER BY
                CASE
                    WHEN T066A_Data_Validade = '0000-00-00' OR T066A_Data_Validade IS NULL THEN 1
                    ELSE 0
                END,
                T066A_Data_Validade ASC,
                T066A_Numero_Lote ASC,
                D004_Local ASC,
                T066_Id ASC;
        SQL;

        $res = mysqli_query($sql);
        $locacoes = [];

        while ($loc = mysql_fetch_assoc($res)) {
            $locacoes[] = $loc;
        }

        return $locacoes;

        /* $select = "SELECT
                        T066_Id,
                        T066A_Numero_Lote,
                        D004_Id,
                        D004_Local,
                        T066_Quantidade_Estoque,
                        T066_Quantidade_Estoque_Liquido,
                        T066A_Data_Validade,
                        D001A_Flag_Validade";
        $sql = "{$select}
                FROM T066
                    LEFT JOIN D004 ON D004_Id = T066_D004_Id
                    LEFT JOIN D009 ON D009_Id = T066_D009_Id
                    LEFT JOIN D049 ON D049_Id = D009_D049_Id
                    LEFT JOIN D001 ON D001_Id = D049_D001_Id
                    LEFT JOIN D001A ON D001_Id = D001A_D001_Id
                    LEFT JOIN T066A ON T066_Id = T066A_T066_Id
                WHERE T066_D009_Id = '{$D009_Id}'
                    AND IFNULL(D004_Flag_Somar_Estoque_Fisico, 'S') != 'N'
                    AND IFNULL(T066A_Flag_Ativo, 'S') = 'S'";

        $query1 = $sql . " AND T066A_Data_Validade > 0 AND D001A_Flag_Validade = 'S'";
        $query2 = $sql . " AND (T066A_Numero_Lote <= 0 OR T066A_Numero_Lote = '' OR T066A_Numero_Lote IS NULL)";
        $query3 = $sql . " AND T066A_Numero_Lote > 0";

        $sql = "{$select}
                FROM (({$query1}) UNION ALL ({$query2}) UNION ALL ({$query3})) LOC
                GROUP BY T066_Id
                ORDER BY
                    CASE
                        WHEN T066A_Data_Validade = '0000-00-00' OR T066A_Data_Validade IS NULL THEN 1
                        ELSE 0
                    END,
                    T066A_Data_Validade ASC,
                    T066A_Numero_Lote ASC,
                    D004_Local ASC,
                    T066_Id ASC"; */
    }

    // Felipe Kadanos - 19/03/2026 - Melhoria EUROSUL FORNECEDO - 46486 
    // Personalizado para preencher mais de uma locação
    public function preencherLocacoes($D009_Id, $T005_Id, $T006_Id, $qtdPed, $locEscolhida = 0) {
        $qtdPedConf = $qtdPed;
        $sqlPdt = <<<SQL
            SELECT T006_Item, D001_Codigo_Produto, LEFT(D001_Descricao_Produto, 50) AS DescPdt FROM T006
                LEFT JOIN D009 ON D009_Id = T006_D009_Id
                LEFT JOIN D049 ON D049_Id = D009_D049_Id
                LEFT JOIN D001 ON D001_Id = D049_D001_Id
            WHERE T006_Id = '{$T006_Id}'
                AND D009_Id = '{$D009_Id}'
        SQL;
        $pdt = mysql_fetch_assoc(mysql_query($sqlPdt));
        $identificacaoProduto = "It: '{$pdt['T006_Item']}' | Produto: {$pdt['D001_Codigo_Produto']} - {$pdt['DescPdt']}";

        $sql = mysql_query("SELECT T238_T006_Id FROM T238 WHERE T238_T006_Id = '{$T006_Id}'");
        if (mysql_num_rows($sql) > 0) {
            // return "{$identificacaoProduto} \n\n Já existem locações separadas para este produto.";
            return true;
        }

        $sql = mysql_query("SELECT T066_Id FROM T066 LEFT JOIN T066A ON T066_Id = T066A_T066_Id WHERE T066_D009_Id = '{$D009_Id}' AND IFNULL(T066A_Flag_Ativo, 'S') = 'S' LIMIT 1");
        if (mysql_num_rows($sql) <= 0) {
            return "{$identificacaoProduto} \n\n O produto não possui locação cadastrada (T066).";
        }

        $sql = mysql_query("SELECT T066_Id FROM T066 LEFT JOIN D004 ON D004_Id = T066_D004_Id WHERE T066_D009_Id = '{$D009_Id}' AND T066_Id = '{$locEscolhida}'");
        if ($locacaoSemLocal = mysql_fetch_assoc($sql)) {
            return "{$identificacaoProduto} \n\n A locação T066_Id: {$locacaoSemLocal['T066_Id']} não possui local definido (D004_Local).";
        }
        
        $locacoesPrioridade = $this->getLocacoesPrioridadeFIFO($D009_Id);

        $somaQtdDisp = $qtdPedConf + array_sum(array_column($locacoesPrioridade, 'T066_Quantidade_Disponivel'));
        if ($somaQtdDisp < $qtdPedConf && $locEscolhida <= 0) {
            $sql = <<<SQL
                SELECT SUM(T066_Quantidade_Estoque_Liquido) as est_liq FROM D009 LEFT JOIN T066 ON T066_D009_Id = D009_Id LEFT JOIN T066A ON T066_Id = T066A_T066_Id WHERE D009_Id = '{$D009_Id}' AND IFNULL(T066A_Flag_Ativo, 'S') = 'S'
            SQL;
            $liq = mysql_fetch_assoc(mysql_query($sql));
            
            return "{$identificacaoProduto} \n\n Este item não tem quantidade de estoque liquido suficiente para atenter esse pedido.\n\nQuantidade de estoque liquido das locações: {$liq['est_liq']}\nQuantidade do pedido: {$qtdPedConf}";
        }
        
        $locacao = [];
        $first = true;
        foreach ($locacoesPrioridade as $loc) {
            // Quando há uma locação escolhida, somente a primeira prioridade FIFO pode ser usada.
            // Se ela for diferente da escolhida, encerra sem separar nenhuma locação.
            if ($locEscolhida > 0 && $loc['T066_Id'] != $locEscolhida && $first) {
                break;
            }
            
            if ($loc['D004_Id'] <= 0 || trim($loc['D004_Local']) === '') {
                return "{$identificacaoProduto} \n\n A locação T066_Id: {$loc['T066_Id']} não possui local definido (D004_Local).";
            }

            $qtdDisponivel = $loc['T066_Quantidade_Disponivel'] + $qtdPedConf;
            if ($qtdDisponivel <= 0) {
                // A locação pode ter estoque físico, mas já estar reservada em outro pedido.
                // Neste caso segue para a próxima prioridade FIFO sem criar separação zerada.
                $first = false;
                continue;
            }

            if ($qtdPed > $qtdDisponivel) {
                $qtdPed -= $qtdDisponivel;
                $locacao[] = [
                    'T066_Id' => $loc['T066_Id'],
                    'qtdSairLoc' => $qtdDisponivel,
                    'D004_Id' => $loc['D004_Id'],
                ];
            } else {
                $locacao[] = [
                    'T066_Id' => $loc['T066_Id'],
                    'qtdSairLoc' => $qtdPed,
                    'D004_Id' => $loc['D004_Id'],
                ];
                break;
            }
            $first = false;
        }
        
        $somaQtd = array_sum(array_column($locacao, 'qtdSairLoc'));
        log("D009_Id: {$D009_Id} {$identificacaoProduto} somaQtd: {$somaQtd}; qtdPedConf: {$qtdPedConf}; locEscolhida: {$locEscolhida}");
        if ($somaQtd < $qtdPedConf && $locEscolhida <= 0) {
            return "{$identificacaoProduto} \n\n Não foi separada quantidade suficiente.\nQuantidade separada: '{$somaQtd}'\nQuantidade do pedido: '{$qtdPedConf}'";
        }

        log("{$identificacaoProduto} locacao: " . json_encode($locacao));
        
        foreach ($locacao as $loc) {
            $ins = "INSERT INTO T238 (
                        T238_T005_Id,
                        T238_T006_Id,
                        T238_T066_Id,
                        T238_D004_Id,
                        T238_Quantidade_Separacao
                    ) VALUES (
                        '{$T005_Id}',
                        '{$T006_Id}',
                        '{$loc['T066_Id']}',
                        '{$loc['D004_Id']}',
                        '{$loc['qtdSairLoc']}'
            )";
            if (!mysqli_query($ins)) {
                return "{$identificacaoProduto} \n\n Houve um erro ao separar as locações para o pedido.\n" . mysql_error();
            }
        }

        $sql = "SELECT SUM(T238_Quantidade_Separacao) AS separado, D001_Codigo_Produto AS cod FROM T238 LEFT JOIN T006 ON T006_Id = T238_T006_Id LEFT JOIN D009 ON D009_Id = T006_D009_Id LEFT JOIN D049 ON D049_Id = D009_D049_Id LEFT JOIN D001 ON D001_Id = D049_D001_Id WHERE T238_T006_Id = '{$T006_Id}'";
        $sep = mysql_fetch_assoc(mysql_query($sql));
        if ($sep['separado'] != $qtdPedConf && $locEscolhida <= 0) {
            return "{$identificacaoProduto} \n\n Houve um erro ao separar as locações para o pedido.\nQuantidade separada: '{$sep['separado']}'\nQuantidade do pedido: '{$qtdPedConf}'";
        }

        return true;
    }

    public function obterNovoCodigoProduto($D001_Id = false, $segundaSeq = false, $preCadastro = false) {
        global $g;
        $resultado = $this->obterNovoCodigoProdutoSmart($D001_Id, $segundaSeq, $preCadastro);

        if(!empty($resultado)){
            return $resultado;
        }
        // Mascara (passar para o painel de configuração)
        if ($preCadastro) {
            $mascara = $g['C031']['mascaraCodigoProdutoPreCadastro'];
        } else {
            $mascara = $g['C031']['mascaraCodigoProduto'];
        }

        // $segundaSeq é utilizado quando tem 2 sequencias no código
        preg_match_all('#SEQ#', $mascara, $matches);
        if ($segundaSeq && count($matches[0]) < 2) {
            return false;
        }

        // Campos para dar o replace
        $sql = <<<EOT
            SELECT
                D001_C008_Id,
                D001_D060_Id,
                D001_D003_Id,
                D001_D002_Id
            FROM D001
            WHERE
                D001_Id = '{$D001_Id}'
EOT;
        $res = mysql_query($sql);

        $resD049 = mysql_query("SELECT D001_C008_Id,
                                       D001_D060_Id,
                                       D001_D003_Id,
                                       D001_D002_Id,
                                       D049_Flag_Tipo
                                   FROM D001
                                   LEFT JOIN D049 ON D049_D001_Id=D001_Id
                                   WHERE D049_D001_Id = '{$D001_Id}'");
        //Verifica se o produto já possui D049
        if(mysql_num_rows($resD049) > 0){
            $row = mysql_fetch_assoc($resD049);
        } else {
            $row = mysql_fetch_assoc($res);
        }

        // Monta o novo código
        $codigo = '';
        // Quebra todas as partes da máscara
        preg_match_all('#(\[[^\]]+\]+|[^\[]+)#', $mascara, $matches);
        foreach ($matches[0] as $key => $value) {
            if(preg_match('#^\[IF\(.+\)\]#', $value)){
                // Encontra as partes da sequência [IF(CONDICAO;VALOR1;VALOR2)]
                preg_match('#^\[IF\((.+);(.+);(.+)\)\]#', $value, $seqMatches);

                foreach ($row as $campo => $valor) {
                    log($campo . ' ' . $valor);
                    $check = strpos($seqMatches[1], $campo);
                    if($check !== false){
                        $seqMatches[1] = str_replace($campo, $valor, $seqMatches[1]);
                    }
                }
                foreach ($row as $campo => $valor) {
                    $check = strpos($seqMatches[2], $campo);
                    if($check !== false){
                        $seqMatches[2] = str_replace($campo, $valor, $seqMatches[2]);
                    }
                }
                foreach ($row as $campo => $valor) {
                    $check = strpos($seqMatches[3], $campo);
                    if($check !== false){
                        $seqMatches[3] = str_replace($campo, $valor, $seqMatches[3]);
                    }
                }
                $seqMatches[2] = str_replace("'", "", $seqMatches[2]);
                $seqMatches[3] = str_replace("'", "", $seqMatches[3]);
                //Executa o IF da mascara e popula a variavel retornoIF com um booleano para usar na sequencia
                $retornoIF = eval("return ({$seqMatches[1]}) ? '0' : '1';");
                if($retornoIF == '0') {
                    $codigo .= $seqMatches[2];
                    $strComplementar = $seqMatches[3];
                } else {
                    $codigo .= $seqMatches[3];
                    $strComplementar = $seqMatches[2];
                }
            } else if ($value{0} == '[' && !preg_match('#^\[SEQ#', $value)) {
                // Encontra as partes do campo [NOME|NÚMERO]
                preg_match('#^\[([^\|]+)(\|([0-9]))?\]#', $value, $campoMatches);
                $campo = $campoMatches[1];
                $campo = $row[$campo];
                if (empty($campo)) {
                    return false;
                }
                if (isset($campoMatches[3])) {
                    $campo = str_pad($campo, $campoMatches[3], '0', STR_PAD_LEFT);
                }
                $codigo .= $campo;
            } else if (preg_match('#^\[SEQ#', $value)) {
                // Encontra as partes da sequência [SEQ|NÚMERO]
                preg_match('#^\[SEQ\|([0-9]+)\]#', $value, $seqMatches);
                $nrSeq = false;
                if (isset($seqMatches[1])) {
                    $nrSeq = $seqMatches[1];
                }

                // Número procura o número sequencial atual no banco
                if ($segundaSeq) {
                    $sql = "SELECT D001_Codigo_Produto FROM D001 WHERE D001_Id = '{$D001_Id}'";
                    log("Código Produto 1: ".$sql);
                } else {
                    if(!empty($codigo)){
                        $sql = "SELECT D001_Codigo_Produto FROM D001 WHERE D001_Codigo_Produto LIKE '{$codigo}%' ORDER BY D001_Codigo_Produto DESC LIMIT 1";
                        log("Código Produto 2: ".$sql);
                    } if(isset($strComplementar)) {
                        $strlenComplementar = strlen($strComplementar);
                        $sql = "SELECT D001_Codigo_Produto FROM D001 WHERE IFNULL(D001_Codigo_Produto,'') != '' AND SUBSTRING(D001_Codigo_Produto, 1, {$strlenComplementar}) != '{$strComplementar}' ORDER BY D001_Codigo_Produto DESC LIMIT 1";
                        log("Código Produto 3: ".$sql);
                    } else {
                        $sql = "SELECT D001_Codigo_Produto FROM D001 WHERE IFNULL(D001_Codigo_Produto,'') != '' ORDER BY CAST(D001_Codigo_Produto AS UNSIGNED) DESC LIMIT 1";
                        log("Código Produto 3: ".$sql);
                    }
                }

                $resSeq = mysql_query($sql);
                if (mysql_num_rows($resSeq) > 0) {
                    $rowSeq = mysql_fetch_assoc($resSeq);
                    $proximoNumero = $rowSeq['D001_Codigo_Produto'];
                    log("proximoNumero: ".$proximoNumero);
                    $proximoNumero = preg_replace('#^' . preg_quote($codigo) . '#', '', $proximoNumero);
                    log("proximoNumero2: ".$proximoNumero);

                    log("nrSeq: ".$nrSeq);
                    if ($nrSeq) {
                    log("codigo: ".$codigo."; strComplementar: ".$strComplementar);
                        if(empty($codigo) && isset($strComplementar)){
                            $valorMascara = str_replace("'", "", $strComplementar);
                            $valorMascara = strlen($valorMascara);
                            $nrSeq = $nrSeq + $valorMascara;
                        }
                        $proximoNumero = substr($proximoNumero, 0, $nrSeq);
                        log("proximoNumero3: ".$proximoNumero);
                    }
                    $proximoNumero = ltrim($proximoNumero, '0');
                    log("proximoNumero4: ".$proximoNumero);

                    if (empty($proximoNumero)) {
                        return false;
                    }
                    if ($segundaSeq) {
                        $segundaSeq = false;
                    } else {
                        $proximoNumero++;
                    }
                    log("proximoNumero5: ".$proximoNumero);

                } else {
                    if ($segundaSeq) {
                        return false;
                    }
                    if(empty($codigo) && isset($strComplementar)){
                        $valorMascara = str_replace("'", "", $strComplementar);
                        $valorMascara = strlen($valorMascara);
                        $nrSeq = $nrSeq + $valorMascara;
                    }
                    $proximoNumero = 1;
                }
                
                log("nrSeq: " . $nrSeq ."; proximoNumero: " . $proximoNumero);
                $codigo .= $nrSeq ? str_pad($proximoNumero, $nrSeq, '0', STR_PAD_LEFT) : $proximoNumero;
            } else {
                // Qualquer outro texto
                $codigo .= $value;
            }
        }
        log("codigo: " . $codigo);
        return $codigo;
    }

    // Felipe Kadanos - 10/10/2025
    // Personalizado pois adicionamos um campo para salvar a data de alteração do produto
    public function incluirMarca($form)
    {
        global $g;
        $D001_Id = $g['mysqlLastId'];
        $sqlD001 = mysql_query("SELECT * FROM D001 WHERE D001_Id = '{$D001_Id}'");
        $resD001 = mysql_fetch_array($sqlD001);

            $D049_D082_Id           = $form->campoValorEnviado('D049_D082_Id');
            $D049_D024_Id           = $form->campoValorEnviado('D049_D024_Id');
            $D049_Flag_Tipo         = $form->campoValorEnviado('D049_Flag_Tipo');
            $D049_Origem_Mercadoria = $form->campoValorEnviado('D049_Origem_Mercadoria');
            $D049_Flag_Nao_Comprar  = $form->campoValorEnviado('D049_Flag_Nao_Comprar');
            mysql_query("INSERT INTO D049 (D049_D082_Id, D049_D001_Id, D049_D024_Id, D049_Flag_Tipo, D049_Origem_Mercadoria,D049_Flag_Nao_Comprar) VALUES ('{$D049_D082_Id}', '{$D001_Id}', '{$D049_D024_Id}', '{$D049_Flag_Tipo}', '{$D049_Origem_Mercadoria}', '{$D049_Flag_Nao_Comprar}')");
            $D049_Id = $g['mysqlLastId'];
            mysql_query("INSERT INTO D049A (D049A_D049_Id, D049A_Data_Alteracao) VALUES ('{$D049_Id}', NOW())");

            mysql_query("INSERT INTO D041 (D041_D049_Id) VALUES ('$D049_Id')");

            $D009_Valor_Preco_Tabela = gCorrigeNumeroInverte($form->campoValorEnviado('D009_Valor_Preco_Tabela'));
            $D009_IPV_2   = gCorrigeNumeroInverte($form->campoValorEnviado('D009_IPV_2'));
            $D009_IPV_3   = gCorrigeNumeroInverte($form->campoValorEnviado('D009_IPV_3'));
            $D009_IPV_4   = gCorrigeNumeroInverte($form->campoValorEnviado('D009_IPV_4'));
            $D009A_IPV_5   = gCorrigeNumeroInverte($form->campoValorEnviado('D009A_IPV_5'));
            $D009_Preco_1 = gCorrigeNumeroInverte($form->campoValorEnviado('D009_Preco_1'));
            $D009_Preco_2 = gCorrigeNumeroInverte($form->campoValorEnviado('D009_Preco_2'));
            $D009_Preco_3 = gCorrigeNumeroInverte($form->campoValorEnviado('D009_Preco_3'));
            $D009_Preco_4 = gCorrigeNumeroInverte($form->campoValorEnviado('D009_Preco_4'));
            $D009A_Preco_5 = gCorrigeNumeroInverte($form->campoValorEnviado('D009A_Preco_5'));
            $D009_IPT_1   = gCorrigeNumeroInverte($form->campoValorEnviado('D009_IPT_1'));
            $D009_IPT_2   = gCorrigeNumeroInverte($form->campoValorEnviado('D009_IPT_2'));
            $D009_IPT_3   = gCorrigeNumeroInverte($form->campoValorEnviado('D009_IPT_3'));
            $D009_IPT_4   = gCorrigeNumeroInverte($form->campoValorEnviado('D009_IPT_4'));
            $D009A_IPT_5   = gCorrigeNumeroInverte($form->campoValorEnviado('D009A_IPT_5'));
            $D009A_Pedido_Minimo   = gCorrigeNumeroInverte($form->campoValorEnviado('D009A_Pedido_Minimo'));
            $C004 = mysql_query("SELECT * FROM C004 ORDER BY C004_Id");
            while ($mC004 = mysql_fetch_array($C004)) {
                
                $D009_ICF_1 = gCorrigeNumeroInverte($form->campoValorEnviado('D009_ICF_1'));
                $D009_IPV_1 = gCorrigeNumeroInverte($form->campoValorEnviado('D009_IPV_1'));
                //IPV 1 E ICF SE ESTIVEREM PREENCHIDOS SOBRESCREVEM O VALOR PADRÃO CADASTRADO NO C004
                $D009_ICF_1 = ($D009_ICF_1 > 0) ? $D009_ICF_1 : $mC004['C004_ICF_Padrao'];
                $D009_IPV_1 = ($D009_IPV_1 > 0) ? $D009_IPV_1 : $mC004['C004_IPV_Padrao'];

                $sql = "INSERT INTO D009 (
                                    D009_D049_Id,
                                    D009_D001_Id,
                                    D009_C004_Id,
                                    D009_Valor_Preco_Tabela,
                                    D009_ICF_1,
                                    D009_IPV_1,
                                    D009_IPV_2,
                                    D009_IPV_3,
                                    D009_IPV_4,
                                    D009_Preco_1,
                                    D009_Preco_2,
                                    D009_Preco_3,
                                    D009_Preco_4,
                                    D009_IPT_1,
                                    D009_IPT_2,
                                    D009_IPT_3,
                                    D009_IPT_4
                                ) VALUES (
                                    '{$D049_Id}',
                                    '{$D001_Id}',
                                    '{$mC004['C004_Id']}',
                                    '{$D009_Valor_Preco_Tabela}',
                                    '{$D009_ICF_1}',
                                    '{$D009_IPV_1}',
                                    '{$D009_IPV_2}',
                                    '{$D009_IPV_3}',
                                    '{$D009_IPV_4}',
                                    '{$D009_Preco_1}',
                                    '{$D009_Preco_2}',
                                    '{$D009_Preco_3}',
                                    '{$D009_Preco_4}',
                                    '{$D009_IPT_1}',
                                    '{$D009_IPT_2}',
                                    '{$D009_IPT_3}',
                                    '{$D009_IPT_4}'
                                )";
                mysql_query($sql);
                
                $D009_Id = $g['mysqlLastId'];

                $sqlD009A = "INSERT INTO D009A (
                            D009A_D009_Id,
                            D009A_IPV_5,
                            D009A_Preco_5,
                            D009A_IPT_5,
                            D009A_Pedido_Minimo
                        ) VALUES (
                            '{$D009_Id}',
                            '{$D009A_IPV_5}',
                            '{$D009A_Preco_5}',
                            '{$D009A_IPT_5}',
                            '{$D009A_Pedido_Minimo}'
                        )";
                mysql_query($sqlD009A);
            }

            $this->Cad002PersonalizacaoTriggerInserirAposD009($D001_Id);

        mysql_query($sql);
    }
    
    function atualizarMarca($form, $D001_Id)
    {
        global $g;
        $D049_Id                           = $form->campoValorEnviado('D049_Id');
        $D049_D082_Id                      = $form->campoValorEnviado('D049_D082_Id');
        $D049_D024_Id                      = $form->campoValorEnviado('D049_D024_Id');
        $D049_Flag_Tipo                    = $form->campoValorEnviado('D049_Flag_Tipo');
        $D049_Modelo                       = $form->campoValorEnviado('D049_Modelo');
        $D049_Codigo_Produto_Fornecedor    = $form->campoValorEnviado('D049_Codigo_Produto_Fornecedor');
        $D049_Origem_Mercadoria            = $form->campoValorEnviado('D049_Origem_Mercadoria');
        $D049_Valor_Preco_KG               = gCorrigeNumeroInverte($form->campoValorEnviado('D049_Valor_Preco_KG'));        
        $D049_Percentual_Acrescimo_Tabela  = gCorrigeNumeroInverte($form->campoValorEnviado('D049_Percentual_Acrescimo_Tabela'));
        $D049_Flag_Nao_Comprar             = $form->campoValorEnviado('D049_Flag_Nao_Comprar');
        
        $D049_Observacao_Compra            = $form->campoValorEnviado('D049_Observacao_Compra');
        $D049_Flag_Nacional_Importado      = $form->campoValorEnviado('D049_Flag_Nacional_Importado');
        $D049_Data_Cadastro                = gCorrigeDataInverte($form->campoValorEnviado('D049_Data_Cadastro'));
        $D049_Flag_Ativo                   = $form->campoValorEnviado('D049_Flag_Ativo');
        $D049_Flag_Substituicao_Tributaria = $form->campoValorEnviado('D049_Flag_Substituicao_Tributaria');
        $D049_IPV                          = gCorrigeNumeroInverte($form->campoValorEnviado('D049_IPV'));
        $D049_IPV1                         = gCorrigeNumeroInverte($form->campoValorEnviado('D049_IPV1'));
        $D049_IPV2                         = gCorrigeNumeroInverte($form->campoValorEnviado('D049_IPV2'));
        $D049_Quantidade_Embalagem_Compra  = gCorrigeNumeroInverte($form->campoValorEnviado('D049_Quantidade_Embalagem_Compra'));
        $D041_Valor_Base                   = gCorrigeNumeroInverte($form->campoValorEnviado('D041_Valor_Base'));
        $D041_Valor_Preco_Consumidor       = gCorrigeNumeroInverte($form->campoValorEnviado('D041_Valor_Preco_Consumidor'));
        $D041_Valor_Preco_Revenda          = gCorrigeNumeroInverte($form->campoValorEnviado('D041_Valor_Preco_Revenda'));
        $D041_Valor_Custo                  = gCorrigeNumeroInverte($form->campoValorEnviado('D041_Valor_Custo'));
        
        // Novos campos de preços que eram da D049, alterados para D009
        $D009_IPV_1                        = gCorrigeNumeroInverte($form->campoValorEnviado('D009_IPV_1'));
        $D009_IPV_2                        = gCorrigeNumeroInverte($form->campoValorEnviado('D009_IPV_2'));
        $D009_IPV_3                        = gCorrigeNumeroInverte($form->campoValorEnviado('D009_IPV_3'));
        $D009_IPV_4                        = gCorrigeNumeroInverte($form->campoValorEnviado('D009_IPV_4'));
        $D009A_IPV_5                       = gCorrigeNumeroInverte($form->campoValorEnviado('D009A_IPV_5'));
        $D009_Preco_Pauta_ST               = gCorrigeNumeroInverte($form->campoValorEnviado('D009_Preco_Pauta_ST'));
        $D009_IPT_1                        = gCorrigeNumeroInverte($form->campoValorEnviado('D009_IPT_1'));
        $D009_IPT_2                        = gCorrigeNumeroInverte($form->campoValorEnviado('D009_IPT_2'));
        $D009_IPT_3                        = gCorrigeNumeroInverte($form->campoValorEnviado('D009_IPT_3'));
        $D009_IPT_4                        = gCorrigeNumeroInverte($form->campoValorEnviado('D009_IPT_4'));
        $D009A_IPT_5                       = gCorrigeNumeroInverte($form->campoValorEnviado('D009A_IPT_5'));
        $D009_ICF_1                        = gCorrigeNumeroInverte($form->campoValorEnviado('D009_ICF_1'));
        $D009_ICF_2                        = gCorrigeNumeroInverte($form->campoValorEnviado('D009_ICF_2'));
        $D009_ICF_3                        = gCorrigeNumeroInverte($form->campoValorEnviado('D009_ICF_3'));
        $D009_Valor_Preco_Tabela           = gCorrigeNumeroInverte($form->campoValorEnviado('D009_Valor_Preco_Tabela'));
        $D009_Aliquota_ICMS_Tabela         = gCorrigeNumeroInverte($form->campoValorEnviado('D009_Aliquota_ICMS_Tabela'));
        $D009_Percentual_Desconto_Tabela   = gCorrigeNumeroInverte($form->campoValorEnviado('D049_Percentual_Desconto_Tabela'));
        $D009_Preco_1                      = gCorrigeNumeroInverte($form->campoValorEnviado('D009_Preco_1'));
        $D009_Preco_2                      = gCorrigeNumeroInverte($form->campoValorEnviado('D009_Preco_2'));
        $D009_Preco_3                      = gCorrigeNumeroInverte($form->campoValorEnviado('D009_Preco_3'));
        $D009_Preco_4                      = gCorrigeNumeroInverte($form->campoValorEnviado('D009_Preco_4'));
        $D009A_Preco_5                     = gCorrigeNumeroInverte($form->campoValorEnviado('D009A_Preco_5'));
        $D009_Preco_Loja                   = gCorrigeNumeroInverte($form->campoValorEnviado('D009_Preco_Loja'));
        $D009_Flag_Promocao                = $form->campoValorEnviado('D009_Flag_Promocao');
        $D009_Flag_Usar_Custo              = $form->campoValorEnviado('D009_Flag_Usar_Custo');
        $D009_Flag_Preco_Tabelado          = $form->campoValorEnviado('D009_Flag_Preco_Tabelado');
        $D009_Origem_Mercadoria            = $form->campoValorEnviado('D009_Origem_Mercadoria');
        $D009A_Pedido_Minimo               = gCorrigeNumeroInverte($form->campoValorEnviado('D009A_Pedido_Minimo'));
        
        if ($D009_Flag_Promocao == 'on') {
            $D009_Flag_Promocao = "S";
        } else {
            $D009_Flag_Promocao = "N";
        }
        if ($D009_Flag_Preco_Tabelado == 'on') {
            $D009_Flag_Preco_Tabelado = "S";
        } else {
            $D009_Flag_Preco_Tabelado = "N";
        }
        
        $sql = <<<EOT
                    UPDATE D049 SET D049_D082_Id = '{$D049_D082_Id}',
                                D049_D024_Id = '{$D049_D024_Id}',
                                D049_Flag_Tipo = '{$D049_Flag_Tipo}',
                                D049_Modelo = '{$D049_Modelo}',
                                D049_Codigo_Produto_Fornecedor = '{$D049_Codigo_Produto_Fornecedor}',
                                D049_Valor_Preco_Tabela = '{$D049_Valor_Preco_Tabela}',
                                D049_Percentual_Desconto_Tabela = '{$D049_Percentual_Desconto_Tabela}',
                                D049_Percentual_Acrescimo_Tabela = '{$D049_Percentual_Acrescimo_Tabela}',
                                D049_Aliquota_ICMS_Tabela = '{$D049_Aliquota_ICMS_Tabela}',
                                D049_IPV = '{$D049_IPV}',
                                D049_IPV1 = '{$D049_IPV1}',
                                D049_IPV2 = '{$D049_IPV2}',
                                D049_ICF = '{$D049_ICF}',
                                D049_ICF1 = '{$D049_ICF1}',
                                D049_ICF2 = '{$D049_ICF2}',
                                D049_Observacao_Compra = '{$D049_Observacao_Compra}',
                                D049_Flag_Nacional_Importado = '{$D049_Flag_Nacional_Importado}',
                                D049_Data_Cadastro = '{$D049_Data_Cadastro}',
                                D049_Flag_Ativo = '{$D049_Flag_Ativo}',
                                D049_Flag_Substituicao_Tributaria = '{$D049_Flag_Substituicao_Tributaria}',
                                D049_Preco_1='{$D049_Preco_1}',
                                D049_Preco_2='{$D049_Preco_2}',
                                D049_Preco_3='{$D049_Preco_3}',
                                D049_Flag_Promocao='{$D049_Flag_Promocao}',
                                D049_Origem_Mercadoria='{$D049_Origem_Mercadoria}',
                                D049_Valor_Preco_KG='{$D049_Valor_Preco_KG}',
                                D049_Quantidade_Embalagem_Compra='{$D049_Quantidade_Embalagem_Compra}',
                                D049_Flag_Nao_Comprar='{$D049_Flag_Nao_Comprar}'
                          WHERE D049_D001_Id = '{$D001_Id}'
EOT;
        mysql_query($sql);
        log($sql);

        $D049 = "SELECT D049_Id, D049A_Id FROM D049 LEFT JOIN D049A ON D049A_D049_Id=D049_Id WHERE D049_D001_Id = '{$D001_Id}'";
        $mD049 = mysql_fetch_assoc(mysql_query($D049));

        if($mD049['D049A_Id']){
            $updt = "UPDATE D049A SET D049A_Data_Alteracao = NOW() WHERE D049A_Id = '{$mD049['D049A_Id']}'";
            mysql_query($updt);
        } else {
            mysql_query("INSERT INTO D049A (D049A_D049_Id, D049A_Data_Alteracao) VALUES ('{$mD049['D049_Id']}', NOW())");
        }
        
        $sql = <<<EOT
            UPDATE D041
            LEFT JOIN D049 on D049_Id=D041_D049_Id
            LEFT JOIN D009 on D009_D049_Id=D049_Id
            LEFT JOIN D082 on D082_Id=D049_D082_Id
                  SET D041_Valor_Base            ='{$D041_Valor_Base}',
                      D041_Valor_Preco_Consumidor='{$D041_Valor_Preco_Consumidor}',
                      D041_Valor_Preco_Revenda   ='{$D041_Valor_Preco_Revenda}',
                      D041_Valor_Custo           ='{$D041_Valor_Custo}'
                WHERE D049_D001_Id = '{$D001_Id}'
EOT;
        mysql_query($sql);
        
        $sql = <<<EOT
            SELECT D009_Id 
            FROM D009 
            LEFT JOIN D049 ON D049_Id=D009_D049_Id 
            WHERE D049_D001_Id = '{$D001_Id}' AND D009_C004_Id = '{$g['empresaAtual']}';
EOT;
        $res = mysql_query($sql);
        while ($row = mysql_fetch_assoc($res)) {
            $sqlD009 = <<<EOT
                    UPDATE D009 
                    SET 
                    D009_ICF_1='{$D009_ICF_1}',
                    D009_ICF_2='{$D009_ICF_2}',
                    D009_ICF_3='{$D009_ICF_3}',
                    D009_IPV_1='{$D009_IPV_1}',
                    D009_IPV_2='{$D009_IPV_2}',
                    D009_IPV_3='{$D009_IPV_3}',
                    D009_IPV_4='{$D009_IPV_4}',
                    D009_Preco_Pauta_ST='{$D009_Preco_Pauta_ST}',
                    D009_IPT_1='{$D009_IPT_1}',
                    D009_IPT_2='{$D009_IPT_2}',
                    D009_IPT_3='{$D009_IPT_3}',
                    D009_IPT_4='{$D009_IPT_4}',
                    D009_Preco_1='{$D009_Preco_1}',
                    D009_Preco_2='{$D009_Preco_2}',
                    D009_Preco_3='{$D009_Preco_3}',
                    D009_Preco_4='{$D009_Preco_4}',
                    D009_Preco_Loja='{$D009_Preco_Loja}',
                    D009_Valor_Preco_Tabela='{$D009_Valor_Preco_Tabela}',
                    D009_Aliquota_ICMS_Tabela='{$D009_Aliquota_ICMS_Tabela}',
                    D009_Percentual_Desconto_Tabela='{$D009_Percentual_Desconto_Tabela}',
                    D009_Flag_Preco_Tabelado = '{$D009_Flag_Preco_Tabelado}',
                    D009_Flag_Promocao = '{$D009_Flag_Promocao}',
                    D009_Flag_Usar_Custo = '{$D009_Flag_Usar_Custo}',
                    D009_Origem_Mercadoria = '{$D009_Origem_Mercadoria}'
                    WHERE D009_Id = '{$row['D009_Id']}'
EOT;
            log($sqlD009);
            mysql_query($sqlD009);
            
            $sqlD009A = <<<EOT
                    UPDATE D009A SET 
                    D009A_IPV_5='{$D009A_IPV_5}',
                    D009A_IPT_5='{$D009A_IPT_5}',
                    D009A_Preco_5='{$D009A_Preco_5}',
                    D009A_Pedido_Minimo='{$D009A_Pedido_Minimo}'
                    WHERE D009A_D009_Id = '{$row['D009_Id']}'
EOT;
            mysql_query($sqlD009A);
        }
        
        $D009_Valor_Custo_Unitario       = gCorrigeNumeroInverte($form->campoValorEnviado('D009_Valor_Custo_Unitario'));
        $D009_Valor_Custo_Medio_Unitario = gCorrigeNumeroInverte($form->campoValorEnviado('D009_Valor_Custo_Medio_Unitario'));
        $D009_Valor_Custo_Compra         = gCorrigeNumeroInverte($form->campoValorEnviado('D009_Valor_Custo_Compra'));
        $D009_Valor_Custo_Ultima_Compra  = gCorrigeNumeroInverte($form->campoValorEnviado('D009_Valor_Custo_Ultima_Compra'));
        
        return true;
        
    }

    // Personalizado para que o Codigo de barras seja o mesmo do produto
    public function duplicar_form2Comum($divId, $divIdRoot, $tabela, $D001_Id_Antigo)
    {
        global $g;
        
        mysql_query("SET AUTOCOMMIT=0");
        mysql_query("START TRANSACTION");
        
        // Selecionando o pedido
        $D001  = mysql_query("SELECT * FROM D001 WHERE D001_Id='{$D001_Id_Antigo}'");
        $mD001 = mysql_fetch_assoc($D001);

        if (mysql_num_rows($D001) == 0) {
            $retorno = <<<EOT
            var \$buttons = {
              "Fechar": function() { $( this).dialog("destroy").remove(); }
            };
            dialogConfirm("Erro", "Produto não encontrado", \$buttons);
EOT;
            return $retorno;
        }
        
        $campos  = "";
        $valores = "";
        foreach ($mD001 as $key => $value) {
            if ($key == 'D001_Id') {
                continue;
            }else if($key == "D001_Codigo_Produto"){
                $campos .= $key . ", ";
                if($g['C031']['habilitaMascaraCodigo'] == 'S'){   
                    $novoCodigo= $this->obterNovoCodigoProduto($D001_Id_Antigo);
                }else{
                    $novoCodigo= $this->obterNovoCodigoProduto();
                }
                $valores .= "'" . mysql_real_escape_string($novoCodigo) . "',";
                log("D001_Codigo_Produto: ".$novoCodigo);
            }else if($key == "D001_Codigo_Barras"){
                $campos .= $key . ", ";
                $valores .= "'" . mysql_real_escape_string($novoCodigo) . "',";
                log("D001_Codigo_Barras: ".$novoCodigo);
            }else if($key == 'D001_JSON') {            
                continue;                   
            } else {
                $campos .= $key . ", ";
                $valores .= "'" . mysql_real_escape_string($value) . "',";
            }
        }

        $campos  = trim($campos, ', ');
        $valores = trim($valores, ',');
        $insD001 = "INSERT INTO D001 ({$campos}) VALUES ({$valores})";
        $resD001 = mysql_query($insD001);                

        if (!$resD001) {
            mysql_query("ROLLBACK");
            $erro =  mysql_error();
            return "alert('Erro 1: Insert D001');";
        }
        $D001_Id_Novo = $g['mysqlLastId'];

        #region Grava D001A
        $D001A = mysql_query("SELECT * FROM D001A WHERE D001A_D001_Id='{$D001_Id_Antigo}'");
        if(mysql_num_rows($D001A) > 0){
            $mD001A = mysql_fetch_assoc($D001A);
            $campos  = "";
            $valores = "";
            foreach ($mD001A as $key => $value) {
                if ($key == 'D001A_Id') {
                    continue;
                } else if($key == "D001A_D001_Id"){
                    $campos .= $key . ", ";
                    $valores .= "'" . mysql_real_escape_string($D001_Id_Novo) . "',";
                }else {
                    $campos .= $key . ", ";
                    $valores .= "'" . mysql_real_escape_string($value) . "',";
                }
            }
            $campos  = trim($campos, ', ');
            $valores = trim($valores, ',');
            $insD001a = "INSERT INTO D001A ({$campos}) VALUES ({$valores})";
            $resD001a = mysql_query($insD001a);
            if (!$resD001a) {
                mysql_query("ROLLBACK");
                $erro =  mysql_error();
                return "alert('Erro 2: Insert D001A');";
            }
        }else{
            $insD001a = mysql_query("INSERT INTO D001A (D001A_D001_Id) VALUES ('{$D001_Id_Novo}')");
            if (!$insD001a) {
                mysql_query("ROLLBACK");
                $erro =  mysql_error();
                return "alert('Erro 3: Insert D001A');";
            }
        }
        #endregion

        // Copia fotos do produto
        $T172 = mysql_query("SELECT * FROM T172 WHERE T172_D001_Id = '{$D001_Id_Antigo}'");
        if(mysql_num_rows($T172) > 0){
            
            $dirAntigo = "{$g['pathDados']}produtos/".$D001_Id_Antigo."/fotos/";
            $dirNovo   = "{$g['pathDados']}produtos/".$D001_Id_Novo."/fotos/";
            is_dir($dirNovo) or mkdir($dirNovo, 0777, true);            
            
            while($mT172 = mysql_fetch_assoc($T172)){
                $campos  = "";
                $valores = "";

                foreach ($mT172 as $key => $value) {
                    if ($key == 'T172_Id') {
                        continue;
                    } else if($key == "T172_D001_Id"){
                        $campos .= $key . ", ";
                        $valores .= "'" . mysql_real_escape_string($D001_Id_Novo) . "',";     
                    }else {
                        $campos .= $key . ", ";
                        $valores .= "'" . mysql_real_escape_string($value) . "',";
                    }        
                }

                $campos         = trim($campos, ', ');
                $valores        = trim($valores, ',');
                $insT172 = "INSERT INTO T172 ({$campos}) VALUES ({$valores})";
                mysql_query($insT172);
                $T172_Id_Novo = $g['mysqlLastId'];

                $T172_Id_Antigo = $mT172['T172_Id'];
                $pathinfo       = pathinfo($mT172['T172_Nome_Arquivo']);

                //Copia arquivo tamanho original
                $fileAntigo   = $dirAntigo . $T172_Id_Antigo . '.'. $pathinfo['extension'];
                $fileNovo     = $dirNovo . $T172_Id_Novo . '.'. $pathinfo['extension'];
                if(file_exists($fileAntigo)){
                    copy($fileAntigo, $fileNovo);
                }

                // Copia tumb
                $fileAntigo = $dirAntigo . $T172_Id_Antigo . '-100.'. $pathinfo['extension'];
                $fileNovo   = $dirNovo . $T172_Id_Novo . '-100.'. $pathinfo['extension'];
                if(file_exists($fileAntigo)){
                    copy($fileAntigo, $fileNovo);
                }
            }
        }

        // Copia documentos do produto
        $T140 = mysql_query("SELECT * FROM T140 WHERE T140_D001_Id = '{$D001_Id_Antigo}'");
        if(mysql_num_rows($T140) > 0){
            
            $dir = "{$g['pathDados']}certificados/";        
            
            while($mT140 = mysql_fetch_assoc($T140)){
                $campos  = "";
                $valores = "";

                foreach ($mT140 as $key => $value) {
                    if ($key == 'T140_Id') {
                        continue;
                    } else if($key == "T140_D001_Id"){
                        $campos .= $key . ", ";
                        $valores .= "'" . mysql_real_escape_string($D001_Id_Novo) . "',";     
                    }else {
                        $campos .= $key . ", ";
                        $valores .= "'" . mysql_real_escape_string($value) . "',";
                    }        
                }

                $campos         = trim($campos, ', ');
                $valores        = trim($valores, ',');
                $insT140 = "INSERT INTO T140 ({$campos}) VALUES ({$valores})";
                mysql_query($insT140);
                $T140_Id_Novo = $g['mysqlLastId'];

                $T140_Id_Antigo = $mT140['T140_Id'];
                $pathinfo       = pathinfo($mT140['T140_Nome_Arquivo']);

                //Copia arquivo tamanho original
                $fileAntigo   = $dir . $T140_Id_Antigo . '.'. $pathinfo['extension'];
                $fileNovo     = $dir . $T140_Id_Novo . '.'. $pathinfo['extension'];
                if(file_exists($fileAntigo)){
                    copy($fileAntigo, $fileNovo);
                }
            }
        }
        
        // Selecionando todos os itens do D049
        $D049 = mysql_query("SELECT * FROM D049 WHERE D049_D001_Id='{$D001_Id_Antigo}'");
        
        //While que insere o D049 novo
        while ($mD049 = mysql_fetch_assoc($D049)) {
            $campos  = "";
            $valores = "";
            foreach ($mD049 as $key => $value) {
                if ($key == 'D049_Id') {
                    $D049_Id_Antigo=$value;
                    continue;
                } else if($key == "D049_D001_Id"){
                    $campos .= $key . ", ";
                    $valores .= "'" . mysql_real_escape_string($D001_Id_Novo) . "',";
                }else {
                    $campos .= $key . ", ";
                    $valores .= "'" . mysql_real_escape_string($value) . "',";
                }
            }
            $campos  = trim($campos, ', ');
            $valores = trim($valores, ',');
            $insD049 = "INSERT INTO D049 ({$campos}) VALUES ({$valores})";
            $resD049 = mysql_query($insD049);
            if (!$resD049) {
                mysql_query("ROLLBACK");
                $erro =  mysql_error();
                return "alert('Erro 4: Insert D049');";
            }

            $D049_Id_Novo = $g['mysqlLastId'];

            #region Grava D049A
            $D049A = mysql_query("SELECT * FROM D049A WHERE D049A_D049_Id='{$D049_Id_Antigo}'");
            if(mysql_num_rows($D049A) > 0){
                $mD049A = mysql_fetch_assoc($D049A);
                $campos  = "";
                $valores = "";
                foreach ($mD049A as $key => $value) {
                    if ($key == 'D049A_Id') {
                        continue;
                    } else if($key == "D049A_D049_Id"){
                        $campos .= $key . ", ";
                        $valores .= "'" . mysql_real_escape_string($D049_Id_Novo) . "',";
                    }else {
                        $campos .= $key . ", ";
                        $valores .= "'" . mysql_real_escape_string($value) . "',";
                    }
                }
                $campos  = trim($campos, ', ');
                $valores = trim($valores, ',');
                $insD049A = "INSERT INTO D049A ({$campos}) VALUES ({$valores})";
                $resD049A = mysql_query($insD049A);
                if (!$resD049A) {
                    mysql_query("ROLLBACK");
                    $erro =  mysql_error();
                    return "alert('Erro 5: Insert D049A);";
                }
            }else{
                mysql_query("INSERT INTO D049A (D049A_D049_Id) VALUES ('{$D049_Id_Novo}')");
                $error =  mysql_error();
                if(!empty($error)){
                    return "alert('Erro 6: Insert D049A');";
                }
            }

            // Selecionando todos os itens do D009 de dentro do D049
            $D009 = mysql_query("SELECT * FROM D009 WHERE D009_D049_Id='{$D049_Id_Antigo}'");
            while ($mD009 = mysql_fetch_assoc($D009)) {
                $campos  = "";
                $valores = "";
                foreach ($mD009 as $key => $value) {
                    if ($key == 'D009_Id') {
                        $D009_Id_Antigo=$value;
                        continue;
                    } else if($key == "D009_D001_Id"){
                        $campos .= $key . ", ";
                        $valores .= "'" . mysql_real_escape_string($D001_Id_Novo) . "',";
                    } else if($key == "D009_D049_Id"){
                        $campos .= $key . ", ";
                        $valores .= "'" . mysql_real_escape_string($D049_Id_Novo) . "',";
                    }else {
                        $campos .= $key . ", ";
                        $valores .= "'" . mysql_real_escape_string($value) . "',";
                    }
                }
                $campos  = trim($campos, ', ');
                $valores = trim($valores, ',');
                $insD009 = "INSERT INTO D009 ({$campos}) VALUES ({$valores})";
                $resD009 = mysql_query($insD009);
                if (!$resD009) {
                    mysql_query("ROLLBACK");
                    $erro =  mysql_error();
                     return "alert('Erro 7: Insert D009');";
                }

                $D009_Id_Novo = $g['mysqlLastId'];

                #region Grava D009A
                $D009A = mysql_query("SELECT * FROM D009A WHERE D009A_D009_Id='{$D009_Id_Antigo}'");
                if(mysql_num_rows($D009A) > 0){
                    $mD009A = mysql_fetch_assoc($D009A);
                    $campos  = "";
                    $valores = "";
                    foreach ($mD009A as $key => $value) {
                        if ($key == 'D009A_Id') {
                            continue;
                        } else if($key == "D009A_D009_Id"){
                            $campos .= $key . ", ";
                            $valores .= "'" . mysql_real_escape_string($D009_Id_Novo) . "',";
                        } else if($key == 'D009A_JSON') {            
                            continue;                   
                        } else {
                            $campos .= $key . ", ";
                            $valores .= "'" . mysql_real_escape_string($value) . "',";
                        }
                    }
                    $campos  = trim($campos, ', ');
                    $valores = trim($valores, ',');
                    $insD009A = "INSERT INTO D009A ({$campos}) VALUES ({$valores})";
                    $resD009A = mysql_query($insD009A);
                    if (!$resD009A) {
                        mysql_query("ROLLBACK");
                        $erro =  mysql_error();
                        return "alert('Erro 8: Insert D009A');";
                    }
                } else {
                    mysql_query("INSERT INTO D009A (D009A_D009_Id) VALUES ('{$D009_Id_Novo}')");
                    $error =  mysql_error();
                    if(!empty($error)){
                        return "alert('Erro 9: Insert D009A');";
                    }
                }
            }

        }

        $D001_Id = $D001_Id_Novo;
        $this->D001_reprocessa_historico($D001_Id, 0, false, false);

        mysql_query("COMMIT");
        $novoId = uniqid();
        return "$('#{$divId}').closest('.ui-dialog-content').dialog('close'); abrirJanela(false, '{$divIdRoot}', '{$divId}' ,'{$novoId}', '', 'Cadastro Produto', '/cad/cad002/content/form2/', '&acaoId=' + encodeURIComponent('{$D001_Id}') + '&tabela=D001', [990,605]);";
    }
    

    // Felipe Kadanos - 29/09/2025
    // Personalizado a pedido do Miguel para que as locações com "Somar Estoque" = 'N' não seja consideradas
    public function processaEstoqueLiquidoPorLocacao($D009_Id) {
        $T006 = mysql_query("SELECT T006_Id,
                                    T006_Quantidade,
                                    T006_Quantidade_Estoque,
                                    T006_Flag_Reservar_Estoque,
                                    T005_Flag_Notificacao_Estoque,
                                    T006_T005_Id,
                                    T006_D009_Id,
                                    T004A_T066_Id,
                                    T005_Id_Pedido_Externo,
                                    D022_Nome_Empresa
                               FROM T006 
                          LEFT JOIN T005 ON T005_Id=T006_T005_Id
                          LEFT JOIN D006 ON D006_Id=T006_D006_Id
                          LEFT JOIN T004 ON T004_Id=T006_T004_Id
                          LEFT JOIN T004A ON T004_Id=T004A_T004_Id
                          LEFT JOIN D022 ON T005_D022_Id = D022_Id
                          LEFT JOIN T005A ON T005_Id = T005A_T005_Id
                              WHERE T006_D009_Id='{$D009_Id}'
                                AND ifnull(T006_T075_Id,0) <= 0
                                AND ((T006_Flag_Status!='3'
                                AND T005_Flag_Status!='4'
                                AND T005_Flag_Status!='5'
                                AND T005_Flag_Status!='7'
                                AND T005_Flag_Status!='11'
                                AND T005_Flag_Status!='8'
                                AND T005_Flag_Status!='26')
                                 OR T005_Flag_Status='')
                                AND (T005_T005_Id_Agrupado<=0 OR T005_T005_Id_Agrupado IS NULL)
                                AND IFNULL(T005A_Flag_Tipo,1) != 3
                           ORDER BY T006_Prioridade DESC, T006_Id ASC");

        $estoquePedido = $Saldo;
        while ($mT006 = mysql_fetch_array($T006)) {
            $resT066 = mysql_query("SELECT T066_Id, 
                                           T066_Quantidade_Estoque, 
                                           D004_Local,
                                           D004_Indicador_Propriedade, 
                                           (T066_Quantidade_Estoque-D009_Quantidade_Pedido_Por_Locacao(T066_D009_Id, T066_Id)) as EstoqueLiquido
                                      FROM D009
                                 LEFT JOIN T066 ON T066_D009_Id=D009_Id
                                 LEFT JOIN D004 ON D004_Id=T066_D004_Id
                                     WHERE D009_Id = '{$mT006['T006_D009_Id']}'
                                       AND D004_Flag_Somar_Estoque_Fisico = 'S'
                                  ORDER BY T066_Quantidade_Estoque DESC");
            $T066_Id        = 0;
            $T066_Id_Local  = 0;
            $locacaoLoja    = 0;
            $locacaoExcecao = false;
            while ($rowT066 = mysql_fetch_assoc($resT066)) {
                if ($rowT066['D004_Local'] == 'LOJA') {
                    // Caso tenha apenas uma locação no produto que se chama loja, grava ela
                    $locacaoLoja = $rowT066['T066_Id'];
                }

                if ($rowT066['D004_Local'] != 'LOJA') {
                    $T066_Id_Local = $rowT066['T066_Id'];
                }

                if (!$T066_Id && $rowT066['D004_Local'] != 'LOJA') {
                    $T066_Id = $rowT066['T066_Id'];
                }

                if ($rowT066['EstoqueLiquido'] == $mT006['T006_Quantidade'] && $rowT066['D004_Local'] != 'LOJA' && !$locacaoExcecao) {
                    $T066_Id = $rowT066['T066_Id'];
                }
                
                //Se for selecionado a locação no item do orçamento, utiliza ela no pedido
                if($mT006['T004A_T066_Id'] == $rowT066['T066_Id'] && $rowT066['D004_Local'] != 'LOJA' && !$locacaoExcecao){
                    $T066_Id = $mT006['T004A_T066_Id'];
                }

                $locacaoExcecao = $this->excecaoLocacao($rowT066, $mT006);
                if ($locacaoExcecao AND !isset($excecaoDefinida))
                {
                    $excecaoDefinida = true;                    
                    $T066_Id = $rowT066['T066_Id'];
                }                       
            }

            if (!$T066_Id) {
                $T066_Id_Local = ($T066_Id_Local <= 0) ? $locacaoLoja : $T066_Id_Local;
                $T066_Id       = $T066_Id_Local;
            }
            
            // T006_T066_Id
            mysqli_query("UPDATE T006 
                             SET T006_T066_Id = '{$T066_Id}'
                           WHERE T006_Id = '{$mT006['T006_Id']}'");

        }

        // T066_Quantidade_Estoque_Liquido
        mysqli_query("UPDATE T066 
                         SET T066_Quantidade_Estoque_Liquido = (T066_Quantidade_Estoque-D009_Quantidade_Pedido_Por_Locacao(T066_D009_Id, T066_Id))
                       WHERE T066_D009_Id = '{$D009_Id}'");

    }
 
	public function formulaCalculoSugestaoCompra($mD009){
        global $g;

		$T001              = mysql_query("SELECT SUM(IF(T001_Quantidade>('{$mD009['qtdVendaMedia']}'*4),('{$mD009['qtdVendaMedia']}'*4),T001_Quantidade)) as quantidadeMedia FROM T001 WHERE T001_D009_Id='{$mD009['D009_Id']}' AND IF(T001_Flag_MMF='S',1,0)=1 AND T001_Data_Lancamento >= '{$mD009['ultimaData']}'");
		$qtdMediaVendida   = mysql_fetch_array($T001);
		$mD009['mediaMMF'] = $qtdMediaVendida['quantidadeMedia'] / $mD009['D009_Meses_MMF'];   

		$Quantidade_Comprar_Calculada = ($mD009['mediaMMF']*$mD009['D009_Meses_Compra']) - ($mD009['D009_Quantidade_Estoque']-$mD009['Quantidade_Minima_Estoque'])  - $mD009['D009_Quantidade_OC'];
     	
        return $Quantidade_Comprar_Calculada;
    }

    public function formulaCalculoEstoqueMinimo($mD009){
        global $g;

		$T001              = mysql_query("SELECT SUM(IF(T001_Quantidade>('{$mD009['qtdVendaMedia']}'*4),('{$mD009['qtdVendaMedia']}'*4),T001_Quantidade)) as quantidadeMedia FROM T001 WHERE T001_D009_Id='{$mD009['D009_Id']}' AND IF(T001_Flag_MMF='S',1,0)=1 AND T001_Data_Lancamento >= '{$mD009['ultimaData']}'");
		$qtdMediaVendida   = mysql_fetch_array($T001);
		$mD009['mediaMMF'] = $qtdMediaVendida['quantidadeMedia'] / $mD009['D009_Meses_MMF'];   

        $Quantidade_Minima_Estoque =  $mD009['mediaMMF']*$mD009['D009_Meses_Reposicao'];

        return $Quantidade_Minima_Estoque;
    }

        /**
     * atualizarCustoProduto
     *
     * @param String $D009_Id
     * @param array $dadosCusto
     * @return true
     */  
     // Personalizacao nos custos do item montado, se não tiver cadastro na montagem, vai usar o custo da ultima compra
    public function atualizarCustoProduto($D009_Id, $dadosCusto = false)
    {
        global $g;
        $sqlD009 = mysql_query("SELECT  D009_Data_Atualizacao_Preco_Tabela,
                                        D009_Valor_Preco_Tabela,
                                        D009_Valor_Custo_Medio_Unitario,
                                        D009_Valor_Custo_Ultima_Compra,
                                        D009_Quantidade_Estoque,
                                        D009_Quantidade_Estoque_Liquido,
                                        D009_Data_Ultima_Entrada,
                                        D049_Flag_Tipo,
                                        D049_Percentual_Acrescimo_Custo,
                                        D001_Id,
                                        D009_ICF_1,
                                        D009_C004_Id,
                                        D001_Codigo_Produto,
                                        D001_Descricao_Produto,
                                        D009_D069_Id,
                                        D049_Flag_Tipo,
                                        D049_Id,
                                        D009_Id,
                                        D009A_Valor_Custo_Liquido_Ultima_Compra 
                                   FROM D009 
                              LEFT JOIN D049 ON D049_Id=D009_D049_Id 
                              LEFT JOIN D001 ON D001_Id=D049_D001_Id 
                              LEFT JOIN D009A ON D009_Id=D009A_D009_Id
                                  WHERE D009_Id = '{$D009_Id}'");
        $mD009   = mysql_fetch_array($sqlD009);

        $Data_Alteracao_Custo_Manual = $mD009['D009_Data_Atualizacao_Preco_Tabela'];
        $Custo_Manual                = $mD009['D009_Valor_Preco_Tabela'];
        if($dadosCusto !== false){
            //Dados do método D001_reprocessa_historico
            $Custo_Medio           = $dadosCusto['Custo_Medio'];
            $Custo_Compra          = $dadosCusto['Custo_Compra'];
            $Saldo_Regras          = $dadosCusto['Saldo_Regras'];
            $Data_Ultima_Entrada   = $dadosCusto['Data_Ultima_Entrada'];
            $Custo_Liquido_Compra  = $dadosCusto['Custo_Liquido_Compra'];
        } else {
            //Dados do cadastro do produto
            $Custo_Medio           = $mD009['D009_Valor_Custo_Medio_Unitario'];
            $Custo_Compra          = $mD009['D009_Valor_Custo_Ultima_Compra']; 
            $Saldo_Regras          = $mD009['D009_Quantidade_Estoque'];
            $Data_Ultima_Entrada   = $mD009['D009_Data_Ultima_Entrada'];
            $Custo_Liquido_Compra  = $mD009['D009A_Valor_Custo_Liquido_Ultima_Compra'];
        }

        if($g['C031']['estoqueUtilizadoFormarCusto'] == 'L'){
            // Se a configuração do custo é pelo estoque, verifica se usa o líquido
            // Por padrão valida pelo físico
            $Saldo_Regras = $mD009['D009_Quantidade_Estoque_Liquido'];
        }

        // Verifica se a configuração de inclusão automatica de tabela fornecedor, caso não exista um registro e seja automatico, sera cadastro 
        $C031 = mysql_query("select C031_Valor from C031 where C031_Campo='inserirTabelaFornecedorPadrao' and C031_C004_Id='{$mD009['D009_C004_Id']}'");
        $mC031 = mysql_fetch_array($C031);
        $sqlD069 = mysql_query("SELECT D069_Id FROM D069 JOIN D070 ON D070_Id=D069_D070_Id WHERE D069_D009_Id='{$D009_Id}' AND D069_D070_Id='{$mC031['C031_Valor']}'");
        if (mysql_num_rows($sqlD069)<=0){
            $D070 = mysql_query("select D070_Id, D070_D024_Id from D070 where D070_Id='{$mC031['C031_Valor']}'");
            if (mysql_num_rows($D070)>0) {
                $mD070 = mysql_fetch_array($D070);
                $D069 = mysql_query("select D069_Id from D069 where D069_D009_Id='{$D009_Id}' and D069_D070_Id='{$mD070['D070_Id']}'");
                if (mysql_num_rows($D069)<=0){
                    mysql_query("insert into D069 (D069_D024_Id, D069_D070_Id, D069_Codigo_Produto, D069_Descricao_Produto, D069_D009_Id) values ('{$mD070['D070_D024_Id']}','{$mD070['D070_Id']}','{$mD009['D001_Codigo_Produto']}','{$mD009['D001_Descricao_Produto']}','$D009_Id')");
                    $opcoes = array('campoNCM' => 'false', 'campoIPI' => 'false', 'campoNossoCodigo' => 'false', 'campoOrigemProduto' => 'false', 'campoCFOP' => 'false', 'campoICMS' => 'false', 'campoICMSST' => 'false');
                    $this->atualizarAliquotasProdutos("WHERE D069_Id = '{$g['mysqlLastId']}'", $opcoes);
                }
            }
        }

        $sqlD069 = mysql_query("SELECT D069_Valor_Custo_Unitario, D069_D070_Id, D069_Id, D069_Data_Atualizacao_Custo FROM D069 JOIN D070 ON D070_Id=D069_D070_Id WHERE D069_Id='{$mD009['D009_D069_Id']}'");
        $mD069   = mysql_fetch_array($sqlD069);
        $Custo_Tabela          = $mD069['D069_Valor_Custo_Unitario'];
        $Data_Alteracao_Tabela = $mD069['D069_Data_Atualizacao_Custo'];

        /***********************************
        * PRODUTOS MONTADOS E MODIFICADOS
        ***********************************/
        if ($mD009['D049_Flag_Tipo'] == 'MT' || $mD009['D049_Flag_Tipo'] == 'MD') {
            //Dados dos produtos que são matéria primas para os produtos montados/modificados
            $D042  = mysql_query("SELECT SUM(D009_Valor_Custo_Compra * D042_Quantidade) as Custo, 
                                         SUM(D009_Valor_Custo_Ultima_Compra * D042_Quantidade) as Custo_Compra,
                                         SUM(D009_Valor_Custo_Medio_Unitario * D042_Quantidade) as Custo_Medio,
                                         SUM(D009_Valor_Custo_Tabela * D042_Quantidade) as Custo_Tabela,
                                         MIN(D009_Data_Ultima_Entrada) as Data_Compra,
                                         MIN(D009_Data_Atualizacao_Custo_Fornecedor) as Data_Fornecedor,
                                         MIN(FLOOR(D009_Quantidade_Estoque_Real/D042_Quantidade)) as Disponivel_Montagem,
                                         COUNT(*) totalItens
                                    FROM D042 
                               LEFT JOIN D049 ON D049_Id=D042_D049_Id_Itens
                               LEFT JOIN D001 ON D001_Id=D049_D001_Id
                               LEFT JOIN D009 ON D009_D049_Id=D042_D049_Id_Itens AND D009_C004_Id = '{$g['empresaAtual']}'
                                   WHERE D042_D049_Id='{$mD009['D049_Id']}'");

            $mD042 = mysql_fetch_array($D042);
            if($mD042['totalItens'] > 0){

                $Custo                 = $mD042['Custo'];
                $Custo_Compra          = $mD042['Custo_Compra'];
                $Custo_Medio           = $mD042['Custo_Medio'];
                $Custo_Tabela          = $mD042['Custo_Tabela'];
                $Data_Ultima_Entrada   = $mD042['Data_Compra'];
                $Data_Alteracao_Tabela = $mD042['Data_Fornecedor'];
                $Saldo_Regras          = $mD042['Disponivel_Montagem'];

            } else {
                //Custo principal utilizado é médio
                if($g['C031']['custoPrincipal'] == 'M'){
                    $Custo = $Custo_Medio;
                    $Custo_Utilizado = $g['C031']['custoPrincipal'];
                }
                //Custo principal utilizado é última compra
                if($g['C031']['custoPrincipal'] == 'U'){
                    $Custo = $Custo_Compra;
                    $Custo_Utilizado = $g['C031']['custoPrincipal'];
                }
                //Custo principal utilizado é o tabela
                if($g['C031']['custoPrincipal'] == 'T'){
                    $Custo = $Custo_Tabela;
                    $Custo_Utilizado = $g['C031']['custoPrincipal'];
                }
            }

            if($mD009['D049_Percentual_Acrescimo_Custo'] > 0){
                $Custo_Compra = $Custo_Compra + ($Custo_Compra * ($mD009['D049_Percentual_Acrescimo_Custo']/100));
                $Custo_Medio = $Custo_Medio + ($Custo_Medio * ($mD009['D049_Percentual_Acrescimo_Custo']/100));
                $Custo_Tabela = $Custo_Tabela + ($Custo_Tabela * ($mD009['D049_Percentual_Acrescimo_Custo']/100));
                $Custo = $Custo + ($Custo * ($mD009['D049_Percentual_Acrescimo_Custo']/100));
            }

            //Define a data da atualização e o custo utilizado com base na disponibilidade de montagem
            if($Saldo_Regras > 0){
                $Data_Alteracao_Custo = $mD042['Data_Compra'];
                $Custo_Utilizado = 'U';
            } else {
                $Data_Alteracao_Custo = $mD042['Data_Fornecedor'];
                $Custo_Utilizado = 'T';
            }

            if ($mD009['D009_Valor_Preco_Tabela']>0){
                $Custo = $mD009['D009_Valor_Preco_Tabela'];
                $Data_Alteracao_Custo = $Data_Alteracao_Custo_Manual;
                $Custo_Utilizado = 'MA';
            }

        } else if($mD009['D049_Flag_Tipo'] == 'PD'){
            /**********************
            * PRODUTOS PRODUZIDOS *
            **********************/
            if ($mD009['D009_Valor_Preco_Tabela']>0){
                $Custo = $mD009['D009_Valor_Preco_Tabela'];
                $Data_Alteracao_Custo = $Data_Alteracao_Custo_Manual;
                $Custo_Utilizado = 'MA';

            }else{
                //Custo total da Composição
                $sqlComposicao = "SELECT SUM(D009_Valor_Custo_Unitario * D074_Quantidade) as Custo,
                                         SUBSTRING(MIN(D009_Data_Alteracao_Custo),1,10) as Data_Custo
                                    FROM D074
                               LEFT JOIN D001 ON D001_Id = D074_D001_Id_Composicao 
                               LEFT JOIN D049 ON D001_Id = D049_D001_Id 
                               LEFT JOIN D009 ON D049_Id = D009_D049_Id 
                               LEFT JOIN D037 ON D037_Id = D001_D037_Id
                                   WHERE D074_D001_Id = '{$mD009['D001_Id']}'
                                     AND D009_C004_Id='{$g['empresaAtual']}'";
                $sqlComposicao = mysql_query($sqlComposicao);
                $rowComposicao = mysql_fetch_array($sqlComposicao);
                $composicao = $rowComposicao['Custo'];
                $dataCusto = substr($rowComposicao['Data_Custo'],0,10);

                //Custo total e tempo dos processos
                $processo = "SELECT SUM(D075_Lead_Time) as Tempo, SUM(D075_Lead_Time * (D079_Custo_Minuto + D078_Custo_Minuto)) as Custo
                               FROM D075
                          LEFT JOIN D078 on D078_Id = D075_D078_Id
                          LEFT JOIN D079 on D079_Id = D075_D079_Id
                              WHERE D075_D001_Id = '{$mD009['D001_Id']}'";
                $rProcesso = mysql_query($processo);
                $processo = mysql_fetch_array($rProcesso);
                $custoProcesso = $processo['Custo'];
                $tempoProducao = $processo['Tempo'];

                //Custo total do material de consumo utilizado nos processos
                $consumo = "SELECT SUM(D076_Quantidade * D009_Valor_Custo_Unitario) as Custo
                              FROM D076
                         LEFT JOIN D009 ON D009_Id = D076_D009_Id
                         LEFT JOIN D001 ON D001_Id = D009_D001_Id
                         LEFT JOIN D037 ON D037_Id = D001_D037_Id
                         LEFT JOIN D075 ON D075_Id = D076_D075_Id
                             WHERE D075_D001_Id = '{$mD009['D001_Id']}'";
                $rConsumo = mysql_query($consumo);
                $consumo = mysql_fetch_array($rConsumo);
                $consumo = $consumo['Custo'];
                //Custo final
                $Custo = $Custo_Tabela = $composicao + $custoProcesso + $consumo;
                $Data_Alteracao_Tabela = $Data_Alteracao_Custo = $dataCusto;
                $Custo_Utilizado = 'T';
            }
        } else {
            //$Custo_Medio                  Custo médio
            //$Custo_Compra                 Custo última compra
            //$Custo_Tabela                 Custo tabela
            //$Saldo_Regras                 Estoque com base no histórico do produto ou dos produtos que são matéria prima para os produtos montados/modificados
            //$Data_Alteracao_Tabela        Data da ultima alteração do custo tabela
            //$Data_Ultima_Entrada          Data da ultima compra/custo médio
            $diasCustoCompra = round(abs(strtotime(date("Y-m-d"))-strtotime($Data_Ultima_Entrada))/86400);
            $diasCustoTabela = round(abs(strtotime(date("Y-m-d"))-strtotime($Data_Alteracao_Tabela))/86400);
            log("diasCustoCompra: {$diasCustoCompra} diasCustoTabela: {$diasCustoTabela}");
            //Verificamos o tipo de custo utilizado pela empresa (De acordo com o estoque ou o custo mais recente)
            if($g['C031']['tipoCusto'] == 'E'){
                //Caso o estoque seja positivo, utilizamos a primeira regra de custo
                if($Saldo_Regras > 0){
                    if($g['C031']['prazoCustoPrincipal'] > 0){
                        //Custo principal utilizado é médio
                        if($g['C031']['custoPrincipal'] == 'M' && $diasCustoCompra <= $g['C031']['prazoCustoPrincipal']){
                            $Custo = $Custo_Medio;
                            $Custo_Utilizado = $g['C031']['custoPrincipal'];
                            $Data_Alteracao_Custo = $Data_Ultima_Entrada;
                        }
                        //Custo principal utilizado é última compra
                        if($g['C031']['custoPrincipal'] == 'U' && $diasCustoCompra <= $g['C031']['prazoCustoPrincipal']){
                            $Custo = $Custo_Compra;
                            $Custo_Utilizado = $g['C031']['custoPrincipal'];
                            $Data_Alteracao_Custo = $Data_Ultima_Entrada;
                        }
                        //Custo principal utilizado é o tabela
                        if($g['C031']['custoPrincipal'] == 'T' && $diasCustoTabela <= $g['C031']['prazoCustoPrincipal']){
                            $Custo = $Custo_Tabela;
                            $Custo_Utilizado = $g['C031']['custoPrincipal'];
                            $Data_Alteracao_Custo = $Data_Alteracao_Tabela;
                        }
                    } else {
                        //Custo principal utilizado é médio
                        if($g['C031']['custoPrincipal'] == 'M'){
                            $Custo = $Custo_Medio;
                            $Custo_Utilizado = $g['C031']['custoPrincipal'];
                            $Data_Alteracao_Custo = $Data_Ultima_Entrada;
                        }
                        //Custo principal utilizado é última compra
                        if($g['C031']['custoPrincipal'] == 'U'){
                            $Custo = $Custo_Compra;
                            $Custo_Utilizado = $g['C031']['custoPrincipal'];
                            $Data_Alteracao_Custo = $Data_Ultima_Entrada;
                        }
                        //Custo principal utilizado é o tabela
                        if($g['C031']['custoPrincipal'] == 'T'){
                            $Custo = $Custo_Tabela;
                            $Custo_Utilizado = $g['C031']['custoPrincipal'];
                            $Data_Alteracao_Custo = $Data_Alteracao_Tabela;
                        }
                    }
                }

                //Caso o estoque esteja igual/menor a zero ou nao aplicou nenhum custo mesmo com estoque positivo, utilizamos a segunda regra de custo
                if($Saldo_Regras <= 0 || $Custo_Utilizado == '' || $Custo <= 0){
                    if($g['C031']['prazoCustoSecundario'] > 0){
                        //Custo principal utilizado é médio
                        if($g['C031']['custoSecundario'] == 'M' && $diasCustoCompra <= $g['C031']['prazoCustoSecundario']){
                            $Custo = $Custo_Medio;
                            $Custo_Utilizado = $g['C031']['custoSecundario'];
                            $Data_Alteracao_Custo = $Data_Ultima_Entrada;
                        }
                        //Custo principal utilizado é última compra
                        if($g['C031']['custoSecundario'] == 'U' && $diasCustoCompra <= $g['C031']['prazoCustoSecundario']){
                            $Custo = $Custo_Compra;
                            $Custo_Utilizado = $g['C031']['custoSecundario'];
                            $Data_Alteracao_Custo = $Data_Ultima_Entrada;
                        }
                        //Custo principal utilizado é o tabela
                        if($g['C031']['custoSecundario'] == 'T' && $diasCustoTabela <= $g['C031']['prazoCustoSecundario']){
                            $Custo = $Custo_Tabela;
                            $Custo_Utilizado = $g['C031']['custoSecundario'];
                            $Data_Alteracao_Custo = $Data_Alteracao_Tabela;
                        }
                    } else {
                        //Custo principal utilizado é médio
                        if($g['C031']['custoSecundario'] == 'M'){
                            $Custo = $Custo_Medio;
                            $Custo_Utilizado = $g['C031']['custoSecundario'];
                            $Data_Alteracao_Custo = $Data_Ultima_Entrada;
                        }
                        //Custo principal utilizado é última compra
                        if($g['C031']['custoSecundario'] == 'U'){
                            $Custo = $Custo_Compra;
                            $Custo_Utilizado = $g['C031']['custoSecundario'];
                            $Data_Alteracao_Custo = $Data_Ultima_Entrada;
                        }
                        //Custo principal utilizado é o tabela
                        if($g['C031']['custoSecundario'] == 'T'){
                            $Custo = $Custo_Tabela;
                            $Custo_Utilizado = $g['C031']['custoSecundario'];
                            $Data_Alteracao_Custo = $Data_Alteracao_Tabela;
                        }
                    }
                }

                //Caso nenhuma regra de custo foi aplicada, será utilizado a regra de custo zerado
                if($Custo_Utilizado == '' || $Custo <= 0){
                    switch ($g['C031']['custoZerado']) {
                        case 'M':
                            $Custo = $Custo_Medio;
                            $Data_Alteracao_Custo = $Data_Ultima_Entrada;
                        break;
                        case 'U':
                            $Custo = $Custo_Compra;
                            $Data_Alteracao_Custo = $Data_Ultima_Entrada;
                        break;
                        case 'T':
                            $Custo = $Custo_Tabela;
                            $Data_Alteracao_Custo = $Data_Alteracao_Tabela;
                        break;
                    }
                    $Custo_Utilizado = $g['C031']['custoZerado'];
                }
                
                //if ($mD009['D009_Valor_Preco_Tabela']>0 && !empty($Custo_Utilizado) && $Custo>0){
                
                if ($mD009['D009_Valor_Preco_Tabela']>0){
                    $Custo = $mD009['D009_Valor_Preco_Tabela'];
                    $Data_Alteracao_Custo = $Data_Alteracao_Custo_Manual;
                    $Custo_Utilizado = 'MA';
                }
                
            } else {
                //Caso seja utilizado o custo mais recente
                $CustoVerificado = ($g['C031']['custoMaisRecenteUtilizado'] == 'M') ?  $Custo_Medio : $Custo_Compra;
                if(strtotime($Data_Ultima_Entrada) > strtotime($Data_Alteracao_Tabela) && strtotime($Data_Ultima_Entrada) > strtotime($Data_Alteracao_Custo_Manual) && $CustoVerificado > 0) {
                    $Custo = ($g['C031']['custoMaisRecenteUtilizado'] == 'M') ?  $Custo_Medio : $Custo_Compra;
                    $Data_Alteracao_Custo = $Data_Ultima_Entrada;
                    $Custo_Utilizado = $g['C031']['custoMaisRecenteUtilizado'];
                } else if(strtotime($Data_Alteracao_Custo_Manual) > strtotime($Data_Alteracao_Tabela) && strtotime($Data_Alteracao_Custo_Manual) > strtotime($Data_Ultima_Entrada) && $Custo_Manual > 0) {
                    $Custo = $Custo_Manual;
                    $Data_Alteracao_Custo = $Data_Alteracao_Custo_Manual;
                    $Custo_Utilizado = 'MA';
                } else {
                    $Custo = $Custo_Tabela;
                    $Data_Alteracao_Custo = $Data_Alteracao_Tabela;
                    $Custo_Utilizado = 'T';
                }
            }
        }
        $Custo_Utilizado      = ($Custo_Utilizado == '') ? '' : $Custo_Utilizado;
        $Custo_Compra         = isset($Custo_Compra) ? $Custo_Compra : 0;
        $mD009['D009_ICF_1'] = empty($mD009['D009_ICF_1']) ? '0' : $mD009['D009_ICF_1'];

        $sql = <<<EOT
            UPDATE D009
                SET D009_Valor_Custo_Medio_Unitario='$Custo_Medio',
                    D009_Valor_Custo_Compra='$Custo',
                    D009_Valor_Custo_Ultima_Compra='$Custo_Compra',
                    D009_Valor_Custo_Tabela='$Custo_Tabela',
                    D009_Valor_Melhor_Custo='{$mD069['D069_Valor_Custo_Unitario']}',
                    D009_Data_Atualizacao_Custo_Fornecedor='$Data_Alteracao_Tabela',
                    D009_Valor_Custo_Unitario=D009_Valor_Custo_Compra*if({$mD009['D009_ICF_1']}>0,{$mD009['D009_ICF_1']},1),
                    D009_Data_Ultima_Entrada='$Data_Ultima_Entrada',
                    D009_Data_Alteracao_Custo='$Data_Alteracao_Custo',
                    D009_Flag_Custo_Utilizado='{$Custo_Utilizado}'
            WHERE D009_Id='{$mD009['D009_Id']}'
EOT;

        log("qual a query:");
        log($sql);
        mysql_query($sql);

        $Custo_Liquido_Compra = isset($Custo_Liquido_Compra) ? $Custo_Liquido_Compra : 0;

        $sql = <<<EOT
            UPDATE D009A
               SET D009A_Valor_Custo_Liquido_Ultima_Compra = '{$Custo_Liquido_Compra}',
                   D009A_Valor_Custo_Unitario_Liquido      = D009A_Valor_Custo_Liquido_Ultima_Compra*if({$mD009['D009_ICF_1']}>0,{$mD009['D009_ICF_1']},1)
          WHERE D009A_D009_Id = '{$mD009['D009_Id']}'
EOT;
         mysql_query($sql);

         return true;
    }

     
     /**
     * retornaICMS
     *
     * @param String $C004_Id
     * @param String $D024_Id
     * @param String $D009_Id
     * @param String $D006_Id
     * @param String $D018_Id
     * @param String $Flag_Revenda_Consumidor
     * @param String $Comissao
     * @param String $Documento
     * @param String $Tipo
     * @return String
     */    
    public function retornaICMS($C004_Id, $D024_Id, $D009_Id, $D006_Id, $D018_Id, $Flag_Revenda_Consumidor, $Comissao, $Documento, $Tipo)
    {
        global $g;
        $Aliquota_ICMS                    = 0;
        $Percentual_Reducao_ICMS          = 0;
        $Aliquota_IPV_Cliente             = 0;
        $Aliquota_IPV_Utilizado           = 0;
        $D024_Inscricao_Estadual          = '';
        $D024_Flag_Pessoa_Fisica_Juridica = '';
        $D024_Valor_IPV                   = 0;
        $D024_Flag_Isento_ICMS            = '';
        $D024_Flag_Isento_ICMS_Venda      = 'N';
        $D024_Percentual_Reducao_ICMS     = 0;
        $D024_Aliquota_ICMS               = 0;
        $D020_Flag_ICMS                   = '';
        $D006_Flag_Isento_ICMS            = '';
        $D049_Id                          = 0;
        
        if(empty($C004_Id)){
            $C004_Id = $g['empresaAtual'];
        }

        $C004  = mysql_query("SELECT C004_Uf, C004_Flag_Isento_ICMS, C004_D018_Id FROM C004 WHERE C004_Id=$C004_Id");
        $mC004 = mysql_fetch_array($C004);
        
        if ($D024_Id > 0) {
            $D024  = mysql_query("SELECT D024_Inscricao_Estadual,
                  D024_Flag_Pessoa_Fisica_Juridica,
                  D024_Valor_IPV,
                  D024_Flag_Revenda_Consumidor,
                  D024_Flag_Isento_ICMS,
                  D024_Flag_Isento_ICMS_Venda,
                  D024_Percentual_Reducao_ICMS,
                  D024_Aliquota_ICMS,
                  D053_Aliquota_ICMS_1,
                  D053_Percentual_Reducao_ICMS_1,
                  D053_Aliquota_ICMS_2,
                  D053_Percentual_Reducao_ICMS_2,
                  D053_Aliquota_ICMS_Revenda,
                  D053_Percentual_Reducao_ICMS_Revenda,
                  D053_Aliquota_ICMS_Consumidor,
                  D053_Percentual_Reducao_ICMS_Consumidor,
                  D020_Flag_ICMS,
                  D053_Aliquota_ICMS_Importado,
                  D053_Aliquota_ICMS_Importado_2,
                  D018_UF,
                  D024_D018_Id
                  FROM D024
                  LEFT JOIN D020 ON D020_Id=D024_D020_Id
                  LEFT JOIN D018 ON D018_Id=D024_D018_Id
                  LEFT JOIN D053 ON D053_D018_Id=D018_Id AND D053_C004_Id='{$C004_Id}'
            WHERE D024_Id='{$D024_Id}' LIMIT 1");
            $mD024 = mysql_fetch_array($D024);
            
            $D018_Aliquota_ICMS_1                    = $mD024['D053_Aliquota_ICMS_1'];
            $D018_Percentual_Reducao_ICMS_1          = $mD024['D053_Percentual_Reducao_ICMS_1'];
            $D018_Aliquota_ICMS_2                    = $mD024['D053_Aliquota_ICMS_2'];
            $D018_Percentual_Reducao_ICMS_2          = $mD024['D053_Percentual_Reducao_ICMS_2'];
            $D018_Aliquota_ICMS_Revenda              = $mD024['D053_Aliquota_ICMS_Revenda'];
            $D018_Percentual_Reducao_ICMS_Revenda    = $mD024['D053_Percentual_Reducao_ICMS_Revenda'];
            $D018_Aliquota_ICMS_Consumidor           = $mD024['D053_Aliquota_ICMS_Consumidor'];
            $D018_Percentual_Reducao_ICMS_Consumidor = $mD024['D053_Percentual_Reducao_ICMS_Consumidor'];
            $D018_UF                                 = $mD024['D018_UF'];
            $D020_Flag_ICMS                          = $mD024['D020_Flag_ICMS'];
            $D024_Valor_IPV                          = $mD024['D024_Valor_IPV'];
            $D018_Aliquota_ICMS_Importado_1          = $mD024['D053_Aliquota_ICMS_Importado'];
            $D018_Aliquota_ICMS_Importado_2          = $mD024['D053_Aliquota_ICMS_Importado_2'];
            
        } elseif ($D018_Id > 0) {
            
            $mD024['D024_Flag_Revenda_Consumidor'] = $Flag_Revenda_Consumidor;
            
            $D018  = mysql_query("SELECT 
                  D053_Aliquota_ICMS_1,
                  D053_Percentual_Reducao_ICMS_1,
                  D053_Aliquota_ICMS_2,
                  D053_Percentual_Reducao_ICMS_2,
                  D053_Aliquota_ICMS_Revenda,
                  D053_Percentual_Reducao_ICMS_Revenda,
                  D053_Aliquota_ICMS_Consumidor,
                  D053_Percentual_Reducao_ICMS_Consumidor,
                  D053_Aliquota_ICMS_Importado,
                  D018_UF,
                  D053_Aliquota_ICMS_Importado_2
                  FROM D018
                  LEFT JOIN D053 ON D053_D018_Id=D018_Id AND D053_C004_Id='{$C004_Id}'
                  WHERE D018_Id='{$D018_Id}'
                  LIMIT 1");
            $mD018 = mysql_fetch_array($D018);
            
            $D018_Aliquota_ICMS_1                    = $mD018['D053_Aliquota_ICMS_1'];
            $D018_Percentual_Reducao_ICMS_1          = $mD018['D053_Percentual_Reducao_ICMS_1'];
            $D018_Aliquota_ICMS_2                    = $mD018['D053_Aliquota_ICMS_2'];
            $D018_Percentual_Reducao_ICMS_2          = $mD018['D053_Percentual_Reducao_ICMS_2'];
            $D018_Aliquota_ICMS_Revenda              = $mD018['D053_Aliquota_ICMS_Revenda'];
            $D018_Percentual_Reducao_ICMS_Revenda    = $mD018['D053_Percentual_Reducao_ICMS_Revenda'];
            $D018_Aliquota_ICMS_Consumidor           = $mD018['D053_Aliquota_ICMS_Consumidor'];
            $D018_Percentual_Reducao_ICMS_Consumidor = $mD018['D053_Percentual_Reducao_ICMS_Consumidor'];
            $D018_UF                                 = $mD018['D018_UF'];
            $D018_Aliquota_ICMS_Importado_1          = $mD018['D053_Aliquota_ICMS_Importado'];
            $D018_Aliquota_ICMS_Importado_2          = $mD018['D053_Aliquota_ICMS_Importado_2'];
        }
        if ($D006_Id > 0) {
            
            $D006  = mysql_query("
                SELECT D006_Flag_Isento_ICMS,
                      D006_Flag_Venda_Compra_Outros,
                      D006_Percentual_Reducao_ICMS,
                      D006_D059_Id,
                      D006_Flag_Isento_ICMS
                 FROM D006
                WHERE D006_Id='{$D006_Id}';
            ");
            $mD006 = mysql_fetch_array($D006);
        }
        
        if ($D009_Id > 0) {
            
            $D009  = mysql_query("
            SELECT D009_Aliquota_ICMS_Venda,
                D009_Percentual_Reducao_ICMS_Venda,
                D009_Flag_Isento_ICMS_Venda,
                D009_D049_Id,
                D049_Origem_Mercadoria
            FROM D049
                LEFT JOIN D009 ON D009_D049_Id=D049_Id
                WHERE D009_Id='{$D009_Id}'
                AND D009_C004_Id='{$C004_Id}';");
            $mD009 = mysql_fetch_array($D009);
            
            if ($mD006['D006_Flag_Venda_Compra_Outros'] != 'T') {
                
                $T046 = mysql_query("SELECT T046_Id
                    FROM T046
                    LEFT JOIN T016 ON T016_Id=T046_T016_Id
                    WHERE T046_D049_Id='{$mD009['D009_D049_Id']}'
                    AND CURRENT_DATE()>=T016_Data_Inicio
                    AND CURRENT_DATE()<=T016_Data_Final
                    LIMIT 1;");
                
                $mT046    = mysql_fetch_array($T046);
                $Tabelado = $mT046['T046_Id'];
                
                if ($Tabelado == "NULL") {
                    
                    $D041     = mysql_query("SELECT D041_Id
                    FROM D041
                    WHERE D041_D049_Id='{$mD009['D009_D049_Id']}'
                    LIMIT 1;");
                    $mD041    = mysql_fetch_array($D041);
                    $Tabelado = $mD041['D041_Id'];
                }
            }
        }

        // Quando a CFOP é isenta de ICMS, o sistema vai gerar o valor do ICMS nos campos com extenção "Oculto", para que estes valores possam ser utilizados na observação da nota
        // O tipo = 22 ou 33 é para retornar as aliquotas mesmo que a CFOP seja isenta
        // Marcelo de Paula - 18-09-2014
        if ($Tipo == 22 or $Tipo == 33){
            $mD006['D006_Flag_Isento_ICMS'] = 'N';
        }

        if($mD006['D006_Flag_Isento_ICMS'] == 'D' && ($mD006['D006_D059_Id'] == '5' || $mD006['D006_D059_Id'] == '6' || $mD006['D006_D059_Id'] == '7')){
            $mD006['D006_Flag_Isento_ICMS'] = 'S';
        }

        if ($mC004['C004_Flag_Isento_ICMS'] == 1 OR $mD006['D006_Flag_Isento_ICMS'] == 'S' OR $D020_Flag_ICMS == 'S' OR $mD009['D009_Flag_Isento_ICMS_Venda'] == 1) {
            $Aliquota_ICMS           = 0;
            $Percentual_Reducao_ICMS = 0;
        } else if ($mD006['D006_Flag_Venda_Compra_Outros'] == 'V' AND $mD024['D024_Flag_Isento_ICMS_Venda'] == 'S') {
            $Aliquota_ICMS           = 0;
            $Percentual_Reducao_ICMS = 0;
        } else if ($mD006['D006_Flag_Venda_Compra_Outros'] == 'C' AND $mD024['D024_Flag_Isento_ICMS'] == '1') {
            $Aliquota_ICMS           = 0;
            $Percentual_Reducao_ICMS = 0;
        } else if((($mD009['D049_Origem_Mercadoria'] == '1' || $mD009['D049_Origem_Mercadoria'] == '2' || $mD009['D049_Origem_Mercadoria'] == '3') AND $mD024['D024_D018_Id'] != $mC004['C004_D018_Id'] AND $mD006['D006_Flag_Isento_ICMS'] != 'S') AND ($mD024['D024_Inscricao_Estadual'] == '' OR $mD024['D024_Inscricao_Estadual'] == 'ISENTO' OR $mD024['D024_Flag_Pessoa_Fisica_Juridica'] == 'F')) {
            $Aliquota_ICMS           = $D018_Aliquota_ICMS_Importado_1;
        } else if((($mD009['D049_Origem_Mercadoria'] == '1' || $mD009['D049_Origem_Mercadoria'] == '2' || $mD009['D049_Origem_Mercadoria'] == '3') AND $mD024['D024_D018_Id'] != $mC004['C004_D018_Id'] AND $mD006['D006_Flag_Isento_ICMS'] != 'S') AND (($mD024['D024_Inscricao_Estadual'] != '' AND $mD024['D024_Inscricao_Estadual'] != 'ISENTO') OR $mD024['D024_Flag_Pessoa_Fisica_Juridica'] == 'J')) {
            $Aliquota_ICMS           = $D018_Aliquota_ICMS_Importado_2;
        } else if ($mD009['D009_Aliquota_ICMS_Venda'] > 0) {
            $Aliquota_ICMS           = $mD009['D009_Aliquota_ICMS_Venda'];
            $Percentual_Reducao_ICMS = $mD009['D009_Percentual_Reducao_ICMS_Venda'];
        } else if ($mD024['D024_Aliquota_ICMS'] > 0) {
            $Aliquota_ICMS           = $mD024['D024_Aliquota_ICMS'];
            $Percentual_Reducao_ICMS = $mD024['D024_Percentual_Reducao_ICMS'];
        } else if ($mD024['D024_Inscricao_Estadual'] == '' OR $mD024['D024_Inscricao_Estadual'] == 'ISENTO' OR $mD024['D024_Flag_Pessoa_Fisica_Juridica'] == 'F') {
            $Aliquota_ICMS           = $D018_Aliquota_ICMS_1;
            $Percentual_Reducao_ICMS = $D018_Percentual_Reducao_ICMS_2;
        } else if (($mD024['D024_Inscricao_Estadual'] != '' AND $mD024['D024_Inscricao_Estadual'] != 'ISENTO') OR $mD024['D024_Flag_Pessoa_Fisica_Juridica'] == 'J') {
            $Aliquota_ICMS           = $D018_Aliquota_ICMS_2;
            $Percentual_Reducao_ICMS = $D018_Percentual_Reducao_ICMS_1;
        }
        
        if ($D024_Valor_IPV > 0) {
            
            $Aliquota_IPV_Cliente = $D024_Valor_IPV;
            $Flag_IPV_Cliente     = 'S';
            
            $D019  = mysql_query("SELECT 
                              D019_Tipo_1,
                              D019_Percentual_Desconto_ACP
                            FROM D019
                            WHERE D019_Aliquota_ICMS='{$Aliquota_ICMS}';");
            $mD019 = mysql_fetch_array($D019);
            
            $D019_Tipo_1                  = $mD019['D019_Tipo_1'];
            $D019_Percentual_Desconto_ACP = $mD019['D019_Percentual_Desconto_ACP'];
            
        } else {
            
            if ($mD024['D024_Flag_Revenda_Consumidor'] != '1') {
                
                $D019  = mysql_query("
                    SELECT D019_IPV_Maximo,D019_Tipo_1,D019_Percentual_Desconto_ACP
                    FROM D019
                    WHERE D019_Aliquota_ICMS=0;");
                $mD019 = mysql_fetch_array($D019);
                
                $D019_Tipo_1                  = $mD019['D019_Tipo_1'];
                $D019_Percentual_Desconto_ACP = $mD019['D019_Percentual_Desconto_ACP'];
                $D019_IPV_Maximo              = $mD019['D019_IPV_Maximo'];
                
            } else {
                
                $D019 = mysql_query("
                SELECT D019_IPV_Maximo,D019_Tipo_1,D019_Percentual_Desconto_ACP
                FROM D019
                WHERE D019_Aliquota_ICMS='{$Aliquota_ICMS}';");
                
                $mD019 = mysql_fetch_array($D019);
                
                $D019_Tipo_1                  = $mD019['D019_Tipo_1'];
                $D019_Percentual_Desconto_ACP = $mD019['D019_Percentual_Desconto_ACP'];
                $D019_IPV_Maximo              = $mD019['D019_IPV_Maximo'];
                
            }
            $Aliquota_IPV_Cliente = $D019_IPV_Maximo;
            $Flag_IPV_Cliente     = 'N';
        }
        
        if ($Documento == 3) {
            $Aliquota_ICMS           = 0;
            $Percentual_Reducao_ICMS = 0;
        }
        
        if ($D009_Id > 0) {
            $D024_2  = mysql_query("
            SELECT D024_Percentual_IPV_Consumidor,
                 D024_Percentual_IPV_Fornecedor_1,
                 D024_Percentual_IPV_Fornecedor_2,
                 D024_Percentual_IPV_Fornecedor_3
            FROM D009
                 LEFT JOIN D049 ON D009_D049_Id=D049_Id
                 LEFT JOIN D024 ON D049_D024_Id=D024_Id
                 WHERE D009_Id='{$D009_Id}'
                 AND D009_C004_Id='{$C004_Id}'");
            $mD024_2 = mysql_fetch_array($D024_2);
            
            $D024_Percentual_IPV_Consumidor   = $mD024_2['D024_Percentual_IPV_Consumidor'];
            $D024_Percentual_IPV_Fornecedor_1 = $mD024_2['D024_Percentual_IPV_Fornecedor_1'];
            $D024_Percentual_IPV_Fornecedor_2 = $mD024_2['D024_Percentual_IPV_Fornecedor_2'];
            $D024_Percentual_IPV_Fornecedor_3 = $mD024_2['D024_Percentual_IPV_Fornecedor_3'];
            
            if ($Flag_IPV_Cliente == 'N') {
                if ($mD024['D024_Flag_Revenda_Consumidor'] != '1') {
                    $Aliquota_IPV_Utilizado = $D024_Percentual_IPV_Consumidor;
                } elseif ($Aliquota_ICMS == 7) {
                    $Aliquota_IPV_Utilizado = $D024_Percentual_IPV_Fornecedor_1;
                } elseif ($Aliquota_ICMS == 12) {
                    $Aliquota_IPV_Utilizado = $D024_Percentual_IPV_Fornecedor_2;
                } elseif ($Aliquota_ICMS == 17 OR $Aliquota_ICMS == 18) {
                    $Aliquota_IPV_Utilizado = $D024_Percentual_IPV_Fornecedor_3;
                }
            }
            if ($Aliquota_IPV_Utilizado == 0) {
                $Aliquota_IPV_Utilizado = $Aliquota_IPV_Cliente;
            }
        } else {
            $Aliquota_IPV_Utilizado = $Aliquota_IPV_Cliente;
        }
        
        if ($Comissao > 0) {
            $D019_Tipo_2            = $Aliquota_IPV_Utilizado / (100 / (100 - $D019_Tipo_1));
            $D019_Tipo_2            = (($D019_Tipo_2 - 1) * 100) / $D019_Tipo_2;
            $D019_Tipo_2            = (100 / (100 - ($D019_Tipo_2 + $Comissao)));
            $D019_Tipo_1            = (100 / (100 - ($D019_Tipo_1)));
            $Aliquota_IPV_Utilizado = round($D019_Tipo_1 * $D019_Tipo_2, 4);
        }
        
        if ($Documento == 3) {
            $Aliquota_IPV_Utilizado = $Aliquota_IPV_Utilizado - ($Aliquota_IPV_Utilizado * (($D019_Percentual_Desconto_ACP * 2) / 100));
        } else {
            
            if ($mD006['D006_Percentual_Reducao_ICMS'] > 0) {
                $Percentual_Reducao_ICMS = $mD006['D006_Percentual_Reducao_ICMS'];
            }
            
            if ($mC004['C004_Flag_Isento_ICMS'] == 1 OR $mD006['D006_Flag_Isento_ICMS'] == 'S' OR $D020_Flag_ICMS == 'S' OR $mD009['D009_Flag_Isento_ICMS_Venda'] == 1) {
                $Aliquota_ICMS           = 0;
                $Percentual_Reducao_ICMS = 0;
            }
            if ($Tipo == 1 AND $Tabelado > 0) {
                
                if ($mD024['D024_Flag_Revenda_Consumidor'] != '1') {
                    $D019                   = mysql_query("
                    SELECT D019_IPV_Maximo
                    FROM D019
                    WHERE D019_Aliquota_ICMS=0;");
                    $mD019                  = mysql_fetch_array($D019);
                    $Aliquota_IPV_Utilizado = $mD019['D019_IPV_Maximo'];
                } else {
                    $D019                   = mysql_query("
                    SELECT D019_IPV_Maximo
                    FROM D019
                    WHERE D019_Aliquota_ICMS='{$Aliquota_ICMS}';");
                    $mD019                  = mysql_fetch_array($D019);
                    $Aliquota_IPV_Utilizado = $mD019['D019_IPV_Maximo'];
                }
            }
        }
        
        include_once("prog/cad/cad002/excessoesIcms.php");
        if ($Tipo == 1) {
            return $Aliquota_IPV_Utilizado;
        } else if ($Tipo == 2 || $Tipo == 22) {
            return $Aliquota_ICMS;
        } else if ($Tipo == 3 || $Tipo == 33) {
            return $Percentual_Reducao_ICMS;
        }
    }

    /**
     * populaT289
     *
     * @param String $T001_D009_Id
     * @param String $T001_Quantidade   
     * @param String $T001_T014_Id
     * @param String $T001_Flag_Operacao
     * @param String $T001_Data_Lancamento
     * @return true
     */     
    function xxxpopulaT289($T001_D009_Id, $T001_T014_Id, $T001_T008_Id, $T001_Quantidade, $T001_Flag_Operacao, $T001_Data_Lancamento)
    {

        if ($T001_T014_Id>0) {

            $sqlT014 = mysql_query("SELECT T014_Id,
                                           D006_Flag_Venda_Compra_Outros,
                                           D006_Flag_Atualiza_Custo,
                                           T014_Valor_Base_ICMS_Substituicao,
                                           T014_Valor_ICMS_Substituicao_Unitario,
                                           T014_Valor_Base_ICMS / $T001_Quantidade as T014_Valor_Base_ICMS,
                                           T014_Valor_ICMS / $T001_Quantidade as T014_Valor_ICMS
                                      FROM T014
                                 LEFT JOIN D006 ON D006_Id=T014_D006_Id
                                     WHERE T014_Id = '{$T001_T014_Id}'");
            $mT014 = mysql_fetch_array($sqlT014);

        } else if ($T001_T008_Id>0){

            $sqlT014 = mysql_query("SELECT T008_Id as T014_Id,
                                           D006_Flag_Venda_Compra_Outros,
                                           D006_Flag_Atualiza_Custo,
                                           T008_Valor_Base_ICMS_Substituicao as T014_Valor_Base_ICMS_Substituicao,
                                           T008_Valor_ICMS_Substituicao_Unitario as T014_Valor_ICMS_Substituicao_Unitario,
                                           T008_Valor_Base_ICMS / $T001_Quantidade as T014_Valor_Base_ICMS,
                                           T008_Valor_ICMS / $T001_Quantidade as T014_Valor_ICMS
                                      FROM T008
                                 LEFT JOIN D006 ON D006_Id=T008_D006_Id
                                     WHERE T008_Id = '{$T001_T008_Id}'");
            $mT014 = mysql_fetch_array($sqlT014);

            $T001_T014_Id = $T001_T008_Id;

        }

        log("Entrou no T289");

        $sqlT289 = mysql_query("SELECT T289_Id,
                                       T289_Quantidade_Saldo
                                  FROM T289
                                 WHERE T289_T014_Id = '{$T001_T014_Id}'");
        $mT289 = mysql_fetch_array($sqlT289);

        // Verifica se a CFOP atualiza custo e se é uma CFOP de compra
        if ($mT014['D006_Flag_Atualiza_Custo']=='S' && $mT014['D006_Flag_Venda_Compra_Outros']=='C'){

            // Caso seja Cancelada a nota, será zerado o estoque
            if ($T001_Flag_Operacao=='C') { 
                $T001_Quantidade      =0;
                $mT289['T289_Quantidade_Saldo']=0; 
            }
            
            if ($mT289['T289_Id']<=0) {

                $valorBaseIcms = $mT014['T014_Valor_Base_ICMS_Substituicao']/$T001_Quantidade;
                $sql = "INSERT INTO T289 (
                                    T289_D009_Id, 
                                    T289_T014_Id,
                                    T289_Quantidade,
                                    T289_Quantidade_Saldo,
                                    T289_Flag_Operacao,
                                    T289_Data_Lancamento,
                                    T289_Valor_Base_ICMS_Substituicao_Unitario,
                                    T289_Valor_ICMS_Substituicao_Unitario,
                                    T289_Base_ICMS_Substituto_Unitario,
                                    T289_Valor_ICMS_Substituto_Unitario
                                 ) VALUES (
                                    '{$T001_D009_Id}', 
                                    '{$T001_T014_Id}', 
                                    '{$T001_Quantidade}',
                                    '{$T001_Quantidade}',
                                    '{$T001_Flag_Operacao}',
                                    '{$T001_Data_Lancamento}',
                                    '{$valorBaseIcms}',
                                    '{$mT014['T014_Valor_ICMS_Substituicao_Unitario']}',
                                    '{$mT014['T014_Valor_Base_ICMS']}',
                                    '{$mT014['T014_Valor_ICMS']}')";
                  $cErro = mysql_error();
                  log("T289: Erro: {$cErro}");
            } else {
                $sql = "UPDATE T289 
                           SET T289_D009_Id          = '{$T001_D009_Id}', 
                               T289_Quantidade       = '{$T001_Quantidade}',
                               T289_Quantidade_Saldo = '{$mT289['T289_Quantidade_Saldo']}',
                               T289_Flag_Operacao    = '{$T001_Flag_Operacao}',
                               T289_Data_Lancamento  = '{$T001_Data_Lancamento}'
                         WHERE T289_Id               = '{$mT289['T289_Id']}'";           
            }

            $res = mysql_query($sql);
        }
    }

        /**
     * juntarlocacao
     *
     * @param Form $form
     * @return String/bool
     */ 
     // Personalização para o insert do T001 chamar a trigger PHP
    public function juntarlocacao($form,$D009_Id)
    {
        global $g;
        
        mysql_query("SET AUTOCOMMIT=0");
        mysql_query("START TRANSACTION");
        
        $T066_Id_Antigo = (int) $form->campoValorEnviado('T066_Id_Antigo');
        $T066_Id_Novo   = (int) $form->campoValorEnviado('T066_Id_Novo');

        // Verificações
        if ($T066_Id_Antigo <= 0 || $T066_Id_Novo <= 0) {
            return 'Os dois códigos devem ser preenchidos.';
        }
        if ($T066_Id_Antigo == $T066_Id_Novo) {
            return 'Os códigos não podem ser o mesmo.';
        }

        $T066_Antigo = mysql_query("SELECT * from T066 WHERE T066_Id='{$T066_Id_Antigo}'");
        if (mysql_error()) {
            mysql_query('ROLLBACK');
            return 'Erro interno 1';
        }
        $mT066_Antigo = mysql_fetch_assoc($T066_Antigo);
        log("juntarlocacao - T066_Antigo: SELECT * from T066 WHERE T066_Id='{$T066_Id_Antigo}'");
        log("juntarlocacao - mT066_Antigo: ".json_encode($mT066_Antigo));

        $T066_Novo = mysql_query("SELECT * from T066 WHERE T066_Id='{$T066_Id_Novo}'");
        if (mysql_error()) {
            mysql_query('ROLLBACK');
            return 'Erro interno 2';
        }
        $mT066_Novo = mysql_fetch_assoc($T066_Novo);
        log("juntarlocacao - T066_Novo: SELECT * from T066 WHERE T066_Id='{$T066_Id_Novo}'");
        log("juntarlocacao - mT066_Novo: ".json_encode($mT066_Novo));

        $somandoT066=mysql_query("SELECT SUM(T066_Quantidade_Estoque) as Quantidade_Estoque_Total FROM T066 WHERE T066_Id='{$T066_Id_Antigo}' OR T066_Id='{$T066_Id_Novo}'");
        $mSomandoT066=mysql_fetch_array($somandoT066);
        $quantidade_Estoque_Total = $mSomandoT066['Quantidade_Estoque_Total'];
        log("SUM(T066_Quantidade_Estoque): ".$quantidade_Estoque_Total);

        $D009=mysql_query("SELECT * FROM D009 LEFT JOIN D049 ON D049_Id=D009_D049_Id LEFT JOIN D001 ON D001_Id=D049_D001_Id WHERE D009_Id='{$D009_Id}'");
        $mD009=mysql_fetch_assoc($D009);
        log("juntarlocacao - D009: SELECT * FROM D009 LEFT JOIN D049 ON D049_Id=D009_D049_Id LEFT JOIN D001 ON D001_Id=D049_D001_Id WHERE D009_Id='{$D009_Id}'");
        log("juntarlocacao - mD009: ".json_encode($mD009));

        $retorno = gJuntarCodigos('T066', $T066_Id_Antigo, $T066_Id_Novo, false, array('T066A'), false);

        if ($retorno !== true) {
            mysql_query('ROLLBACK');
            return 'Erro juntar T066: ' . $retorno;
        }

        $T066=mysql_query("SELECT T066_D004_Id from T066 WHERE T066_Id='{$T066_Id_Novo}'");
        $mT066=mysql_fetch_array($T066);
        $T001=mysql_query("SELECT T001_Id FROM T001 WHERE T001_T066_Id='{$T066_Id_Novo}'");
        while ($mT001=mysql_fetch_array($T001)) {
            mysql_query("UPDATE T001 SET T001_D004_Id='{$mT066['T066_D004_Id']}' WHERE T001_Id='{$mT001['T001_Id']}'");
        }

        $sql = <<<EOT
            INSERT INTO T001 (
                T001_D024_Id,
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
            ) VALUES (
                '{$mD009['D049_D024_Id']}', 
                '{$mD009['D009_Id']}', 
                '{$T066_Id_Novo}', 
                '{$mD009['D009_C004_Id']}', 
                '{$mD009['D001_Codigo_Produto']}', 
                current_date(), 
                'JUNÇÃO LOCAÇÃO MANUAL - {$T066_Id_Novo}+{$T066_Id_Antigo}',
                '{$quantidade_Estoque_Total}',
                'M',
                'B',
                '0',
                '',
                '0',
                'N',
                '0',
                '{$mT066_Novo['T066_D004_Id']}'
            );
EOT;
        
        $res = mysql_query($sql);
        if (!$res) {
            mysql_query('ROLLBACK');
            return 'Erro interno 10';
        }

        // $retorno = $this->D001_reprocessa_historico(false, $D009_Id, 50, true);
        // Precisa reprocessar inteiro porque se não pode dar furo no calculo depois
        $retorno = $this->D001_reprocessa_historico(false, $D009_Id, 0, true);
        // $retorno = $this->D001_reprocessa_historico(false, $D009_Id);
        if ($retorno !== true) {
            mysql_query('ROLLBACK');
            return 'Erro ao reprocessar o histórico, Por favor, contate o suporte técnico. <br />Erro: ' . $retorno;
        }
        
        mysql_query("COMMIT");
        mysql_query("SET AUTOCOMMIT=1");
        
        return true;

    }

        /**
     * atualizaPrecoTabelaOrcamento
     *
     * @param Array $mT004
     * @return true
     */ 
    public function atualizaPrecoTabelaOrcamento($mT004, $forcarUpdate = false, $TotalPorcentagemIPV = 0,$TotalPorcentagemICF = 0)
    {
        global $g; 

        error_log("entrou atualizaPrecoTabelaOrcamento");

        //return true;

		require_once('bibliotecas/classes/VEN001.php');
        $VEN001 = new VEN001();
        
        if(empty($mT004['T004_T219_Id']) && !empty($mT004['T006_T219_Id'])){
            mysql_query("UPDATE T004 SET T004_T219_Id = '{$mT004['T006_T219_Id']}' WHERE T004_Id = '{$mT004['T004_Id']}'", false);
            $mT004['T004_T219_Id'] = $mT004['T006_T219_Id'];
        }

        // Se não tiver T218_Id zera o T219_Id, são casos em que o vendedor apenas solicita prazo para compras
        $mT004['T004_T219_Id'] = ($mT004['T219_T218_Id'] > 0) ? $mT004['T004_T219_Id'] : 0;

        //Retorno do demonstrativo e preço da cotação/OC
        $precoCotacao             = $this->cad002RetornaPrecoCotacao($mT004['D009_Id'], $mT004['T003_Flag_Lista_Preco'], $mT004['T003_D024_Id'], false, $mT004['T004_D006_Id'], $mT004['T004_Aliquota_ICMS'], $mT004['T004_T219_Id'], false, 4, $mT004['T004_Aliquota_PIS'], $mT004['T004_Aliquota_COFINS'], $mT004['T004_Aliquota_IPI']);
        $demonstrativoCotacao     = $precoCotacao[1];
        $custoMedio               = ($g['C031']['atualizarCustoVendaComCustoMedio'] == 'S') ? $precoCotacao[2] : 0;
        $quantidadeEstoque        = ($g['C031']['atualizarCustoVendaComCustoMedio'] == 'S') ? $precoCotacao[3] : 0;
        $precoCotacao             = gCorrigeNumeroInverte($precoCotacao[0]);
        $demonstrativoCotacao     = mysql_real_escape_string(serialize($demonstrativoCotacao));

        //Retorno do demonstrativo e preço da ultima compra/registro estoque altera custo
        $precoCompra              = $this->cad002RetornaPrecoUltimaCompra($mT004['D009_Id'], $mT004['T003_Flag_Lista_Preco'], $mT004['T003_D024_Id'], $mT004['T003_Flag_ACP'], $mT004['T004_D006_Id'], $mT004['T004_Aliquota_ICMS'], 4, $mT004['T004_Aliquota_PIS'], $mT004['T004_Aliquota_COFINS'], $mT004['T004_Aliquota_IPI']);
        $demonstrativoCompra      = $precoCompra[1];
        $precoCompra              = gCorrigeNumeroInverte($precoCompra[0]);
        $demonstrativoCompra      = mysql_real_escape_string(serialize($demonstrativoCompra));

        //Retorno do demonstrativo e preço da tabela do fornecedor
        $precoTabela              = $this->cad002RetornaPrecoTabela($mT004['D009_Id'], $mT004['T003_Flag_Lista_Preco'], $mT004['T003_D024_Id'], $mT004['T003_Flag_ACP'], $mT004['T004_D006_Id'], $mT004['T004_Aliquota_ICMS'], $mT004['T004_Aliquota_PIS'], $mT004['T004_Aliquota_COFINS'], $mT004['T004_Aliquota_IPI'], $TotalPorcentagemIPV, $TotalPorcentagemICF, $mT004['T004_Aliquota_IRPJ'], $mT004['T004_Aliquota_CSLL']);
        $demonstrativoPrecoTabela = $precoTabela[1];
        $valorPrecoTabela         = gCorrigeNumeroInverte($precoTabela[0]);
        $demonstrativoPrecoTabela = mysql_real_escape_string(serialize($demonstrativoPrecoTabela));

        //Retorno do demonstrativo de custo da tabela do fornecedor
        $demonstrativoCustoTabela = $this->cad002demonstrativoCustoTabela($mT004['D009_Id']);
        $demonstrativoCustoTabela = mysql_real_escape_string($demonstrativoCustoTabela);

        //Método retornaPrecoVenda atualizado
        $dadosPrecoVenda          = $this->retornaPrecoVenda($mT004['D009_Id'], $mT004['T003_Flag_Lista_Preco'], $mT004['T003_D024_Id'], false, $mT004['T004_D006_Id'], $mT004['T004_Aliquota_ICMS'], $mT004['T004_T219_Id'], false, $mT004['T003_Casas_Decimais_Preco'], $mT004['T004_Aliquota_PIS'], $mT004['T004_Aliquota_COFINS'], $mT004['T004_Aliquota_IPI'], $mT004['T004_ST_VA'], $mT004['T004_Percentual_Reducao_ICMS_ST'], $mT004['T004_ST_ICMS_Interno'], $mT004['T004_Percentual_Reducao_ICMS'], $mT004['T004_ICMS_Inter_Estadual'], $mT004['T004_Aliquota_IRPJ'], $mT004['T004_Aliquota_CSLL'], $mT004['T004_Valor_Frete_Unitario'], $TotalPorcentagemIPV, $TotalPorcentagemICF, false, $mT004['T003A_Flag_Ratear_Frete_Custo']);
        $precoVendaProduto        = gCorrigeNumeroInverte($dadosPrecoVenda[0]);
        $demonstrativoPrecoVenda  = mysql_real_escape_string(serialize($dadosPrecoVenda[1]));
        $custoMedio               = ($g['C031']['atualizarCustoVendaComCustoMedio'] == 'S') ? $dadosPrecoVenda[2] : 0;
        $custoProduto             = $dadosPrecoVenda[4];
        $origemProduto            = $dadosPrecoVenda[5];

        //Retorno do preço de transferencia
        if($mT004['T004_Flag_Transferencia'] == 'S' && $mT004['T004_Quantidade'] > $mT004['D009_Quantidade_Estoque_Liquido']){
            $sqlD009_Transferencia = mysql_query("SELECT D009_Id FROM D009 WHERE D009_D049_Id = '{$mT004['D049_Id']}' AND D009_C004_Id = '{$mT004['T004_C004_Id_Transferencia']}'");
            $rowD009_Transferencia = mysql_fetch_array($sqlD009_Transferencia);
            $D009_Id               = $rowD009_Transferencia['D009_Id'];

            $dadosPrecoVenda                  = $this->cad002RetornaPrecoTransferencia($D009_Id, $mT004['T004_C004_Id_Transferencia'], $g['C004']['C004_D024_Id'], $mT004['T004_T003_Id']);
            $precoVendaProduto                = gCorrigeNumeroInverte($dadosPrecoVenda[0]);
            $valorPrecoTransferencia          = gCorrigeNumeroInverte($dadosPrecoVenda[0]);
            $demonstrativoPrecoVenda          = mysql_real_escape_string(serialize($dadosPrecoVenda[1]));
            $demonstrativoPrecoTransferencia  = mysql_real_escape_string(serialize($dadosPrecoVenda[1]));
            $custoProduto                     = $dadosPrecoVenda[4];
            $origemProduto                    = $dadosPrecoVenda[5];
            $valorCustoTransferencia          = $dadosPrecoVenda[4];
            $origemProdutoTransferencia       = $dadosPrecoVenda[5];
        } else {
            $sqlD009_Transferencia = mysql_query("SELECT D009_Id FROM D009 WHERE D009_D049_Id = '{$mT004['D049_Id']}' AND D009_C004_Id = '{$g['C031']['C004PrecoTransferenciaGridProdutos']}'");
            $rowD009_Transferencia = mysql_fetch_array($sqlD009_Transferencia);
            $D009_Id               = $rowD009_Transferencia['D009_Id'];

            $dadosPrecoTransferencia          = $this->cad002RetornaPrecoTransferencia($D009_Id, $g['C031']['C004PrecoTransferenciaGridProdutos'], $g['C004']['C004_D024_Id'], $mT004['T004_T003_Id']);
            $valorPrecoTransferencia          = gCorrigeNumeroInverte($dadosPrecoTransferencia[0]);
            $demonstrativoPrecoTransferencia  = mysql_real_escape_string(serialize($dadosPrecoTransferencia[1]));
            $valorCustoTransferencia          = $dadosPrecoTransferencia[4];
            $origemProdutoTransferencia       = $dadosPrecoTransferencia[5];
        }

        /*return retornaPrecoVenda
        array(
          0  $preco,
          1  $demonstrativo,
          2  $custoMedio,
          3  $quantidadeEstoque,
          4  $custoProduto,
          5  $origemMercadoria,
          6  $possuiNFEntrada
        );*/

        if($precoVendaProduto != $mT004['T004_Valor_Preco_Original'] ||
            $custoProduto != $mT004['T004_Valor_Custo_Unitario'] ||
            $valorPrecoTabela != $mT004['T004_Valor_Preco_Tabela'] ||
            $valorPrecoTransferencia != $mT004['T004_Valor_Preco_Transferencia'] ||
            $mT004['D009_T001_Id'] != $mT004['T004_T001_Id'] ||
            $origemProduto != $mT004['T004_Flag_Origem_Produto'] || $forcarUpdate === true){
            if (empty($mT004['T004_T219_Id'])) {

                mysql_query("UPDATE T004
                                SET T004_Valor_Preco_Original              = '{$precoVendaProduto}',
                                    T004_Valor_Preco_Original_Bruto        = '{$precoVendaProduto}',
                                    T004_Valor_Preco_Compra                = '{$precoCompra}',
                                    T004_Valor_Preco_Tabela                = '{$valorPrecoTabela}',
                                    T004_Valor_Preco_Transferencia         = '{$valorPrecoTransferencia}',
                                    T004_Valor_Custo_Transferencia         = '{$valorCustoTransferencia}',
                                    T004_Valor_Custo_Unitario              = '{$custoProduto}',
                                    T004_Percentual_Desconto               = ((T004_Valor_Preco_Original-T004_Valor_Preco_Unitario)/T004_Valor_Preco_Original)*100,
                                    T004_T001_Id                           = '{$mT004['D009_T001_Id']}',
                                    T004_Flag_Origem_Produto               = '{$origemProduto}',
                                    T004_Flag_Origem_Produto_Transferencia = '{$origemProdutoTransferencia}',
                                    T004_Demonstrativo_Preco               = '{$demonstrativoCompra}',
                                    T004_Demonstrativo_Preco_Cotacao       = '',
                                    T004_Demonstrativo_Preco_Tabela        = '{$demonstrativoPrecoTabela}',
                                    T004_Demonstrativo_Custo_Tabela        = '{$demonstrativoCustoTabela}',
                                    T004_Demonstrativo_Preco_Venda         = '{$demonstrativoPrecoVenda}',
                                    T004_Demonstrativo_Preco_Transferencia = '{$demonstrativoPrecoTransferencia}'
                              WHERE T004_Id = '{$mT004['T004_Id']}'");

                //if($mT004['T004_T003_Id'] == 310844 || $mT004['T004_T003_Id'] == 310854 || $mT004['T004_T003_Id'] == 310993 || $mT004['T004_T003_Id'] == 310916){
                    $teste = mysql_query("SELECT T004_Id,
                                                 T004_Codigo_Produto,
                                                 T004_Valor_Preco_Unitario,
                                                 T004_Percentual_Desconto,
                                                 T004_Quantidade,
                                                 T004_Valor_Total_Preco,
                                                 T004_Valor_Preco_Original
                                            FROM T004
                                           WHERE T004_Id = '{$mT004['T004_Id']}'");
                                       //AND ROUND((T004_Valor_Preco_Unitario * T004_Quantidade),2) != T004_Valor_Total_Preco
                    //if(mysql_num_rows($teste) > 0){
                        $mTeste = mysql_fetch_array($teste);
                        $totalCalculado = $mTeste['T004_Valor_Preco_Unitario'] * $mTeste['T004_Quantidade'];
                        $totalCalculado = number_format($totalCalculado,2,'.','');
                        
                        //echo "\n\n 1 - Codigo {$mTeste['T004_Codigo_Produto']} do orcamento {$mT004['T004_T003_Id']} - T004_Valor_Preco_Unitario query : {$mTeste['T004_Valor_Preco_Unitario']} | T004_Valor_Preco_Original query : {$mTeste['T004_Valor_Preco_Original']} | T004_Percentual_Desconto query : {$mTeste['T004_Percentual_Desconto']} | precoVendaProduto : {$precoVendaProduto} | totalCalculado : {$totalCalculado} - totalGravado : {$mTeste['T004_Valor_Total_Preco']} \n\n";

                        if($totalCalculado != $mTeste['T004_Valor_Total_Preco']){

                            echo "\n\n Codigo {$mTeste['T004_Codigo_Produto']} do orcamento {$mT004['T004_T003_Id']} - T004_Valor_Preco_Unitario query : {$mTeste['T004_Valor_Preco_Unitario']} | T004_Percentual_Desconto query : {$mTeste['T004_Percentual_Desconto']} | precoVendaProduto : {$precoVendaProduto} | totalCalculado : {$totalCalculado} - totalGravado : {$mTeste['T004_Valor_Total_Preco']} \n\n";
                            echo "\n\n Teste acesso empresa : {$g['empresaAtual']}\n\n";
                            $logFile = $g['pathDados'] . 'logs/log_script_atualizacao_preco.log';

                            $erro = "Item {$mTeste['T004_Codigo_Produto']} do orcamento {$mT004['T004_T003_Id']} - T004_Valor_Preco_Unitario query : {$mTeste['T004_Valor_Preco_Unitario']} | T004_Percentual_Desconto query : {$mTeste['T004_Percentual_Desconto']} | precoVendaProduto : {$precoVendaProduto} | totalCalculado : {$totalCalculado} - totalGravado : {$mTeste['T004_Valor_Total_Preco']}" . PHP_EOL;
                            file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] " . $erro . PHP_EOL, FILE_APPEND);

                        }

                    //}
               // }                                  
                            
                if($origemProduto != $mT004['T004_Flag_Origem_Produto']){
                    // pode mudar a aliquota do ICMS por conta da origem
                    $VEN001->atualizarAliquotas($mT004['T004_T003_Id'], null, $mT004['T004_Id'], null);

                    if($mT004['T004_T003_Id'] == 310844 || $mT004['T004_T003_Id'] == 310854 || $mT004['T004_T003_Id'] == 310993 || $mT004['T004_T003_Id'] == 310916){
                        // Aqui são testes que estavam sendo realizdos nesses orçamentos sobre os preços unitarios que estavam mudando
                        $teste = mysql_query("SELECT T004_Id,
                                                     T004_Codigo_Produto,
                                                     T004_Valor_Preco_Unitario,
                                                     T004_Percentual_Desconto,
                                                     T004_Quantidade,
                                                     T004_Valor_Total_Preco
                                                FROM T004
                                               WHERE T004_Id = '{$mT004['T004_Id']}'");
                                        //AND ROUND((T004_Valor_Preco_Unitario * T004_Quantidade),2) != T004_Valor_Total_Preco
                        
                        //if(mysql_num_rows($teste) > 0){
                            $mTeste = mysql_fetch_array($teste);
                            $totalCalculado = $mTeste['T004_Valor_Preco_Unitario'] * $mTeste['T004_Quantidade'];

                            if($totalCalculado != $mTeste['T004_Valor_Total_Preco']){
                                echo "\n\nCodigo {$mTeste['T004_Codigo_Produto']} do orcamento {$mT004['T004_T003_Id']} apos atualizarAliquotas - T004_Valor_Preco_Unitario query : {$mTeste['T004_Valor_Preco_Unitario']} | T004_Percentual_Desconto query : {$mTeste['T004_Percentual_Desconto']} |precoVendaProduto : {$precoVendaProduto} | totalCalculado : {$totalCalculado} - totalGravado : {$mTeste['T004_Valor_Total_Preco']} \n\n";
                                $logFile = $g['pathDados'] . 'logs/log_script_atualizacao_preco.log';

                                $erro = "Item {$mTeste['T004_Codigo_Produto']} do orcamento {$mT004['T004_T003_Id']} apos atualizarAliquotas - T004_Valor_Preco_Unitario query : {$mTeste['T004_Valor_Preco_Unitario']} | T004_Percentual_Desconto query : {$mTeste['T004_Percentual_Desconto']} | precoVendaProduto : {$precoVendaProduto} | totalCalculado : {$totalCalculado} - totalGravado : {$mTeste['T004_Valor_Total_Preco']}" . PHP_EOL;
                                $testeLog = file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] " . $erro . PHP_EOL, FILE_APPEND);
                            }
                        //}
                    }

                }

                return true;
            }

            mysql_query("UPDATE T004
                                 SET T004_Valor_Preco_Original              = '{$precoVendaProduto}',
                                     T004_Valor_Preco_Original_Bruto        = '{$precoVendaProduto}',
                                     T004_Valor_Preco_Compra                = '{$precoCompra}',
                                     T004_Valor_Preco_Cotacao               = '{$precoCotacao}',
                                     T004_Valor_Preco_Tabela                = '{$valorPrecoTabela}',
                                     T004_Valor_Preco_Transferencia         = '{$valorPrecoTransferencia}',
                                     T004_Valor_Custo_Transferencia         = '{$valorCustoTransferencia}',
                                     T004_Valor_Custo_Unitario              = '{$custoProduto}',
                                     T004_Percentual_Desconto               = ((T004_Valor_Preco_Original-T004_Valor_Preco_Unitario)/T004_Valor_Preco_Original)*100,
                                     T004_T001_Id                           = '{$mT004['D009_T001_Id']}',
                                     T004_Flag_Origem_Produto               = '{$origemProduto}',
                                     T004_Flag_Origem_Produto_Transferencia = '{$origemProdutoTransferencia}',
                                     T004_Demonstrativo_Preco               = '{$demonstrativoCompra}',
                                     T004_Demonstrativo_Preco_Cotacao       = '{$demonstrativoCotacao}',
                                     T004_Demonstrativo_Preco_Tabela        = '{$demonstrativoPrecoTabela}',
                                     T004_Demonstrativo_Custo_Tabela        = '{$demonstrativoCustoTabela}',
                                     T004_Demonstrativo_Preco_Venda         = '{$demonstrativoPrecoVenda}',
                                     T004_Demonstrativo_Preco_Transferencia = '{$demonstrativoPrecoTransferencia}'
                               WHERE T004_Id = '{$mT004['T004_Id']}'");

            mysql_query("UPDATE T219
                           LEFT JOIN T004 ON T004_T219_Id=T219_Id
                                 SET T219_Valor_Preco_Venda           = T004_Valor_Preco_Original,
                                     T219_Valor_Preco_Venda_Negociado = T004_Valor_Preco_Unitario,
                                     T219_Valor_Custo_Medio_Unitario  = '{$custoMedio}'
                               WHERE T004_Id = '{$mT004['T004_Id']}'");

            mysql_query("UPDATE T225
                           LEFT JOIN T219 ON T219_Id=T225_T219_Id
                           LEFT JOIN T004 ON T004_T219_Id=T219_Id
                                 SET T225_Valor_Preco_Venda           = T004_Valor_Preco_Original,
                                     T225_Valor_Preco_Venda_Negociado = T004_Valor_Preco_Unitario,
                                     T225_Valor_Custo_Medio_Unitario  = '{$custoMedio}'
                               WHERE T004_Id = '{$mT004['T004_Id']}'");

            if($origemProduto != $mT004['T004_Flag_Origem_Produto']){
                // pode mudar a aliquota do ICMS por conta da origem
                $VEN001->atualizarAliquotas($mT004['T004_T003_Id'], null, $mT004['T004_Id'], null);
            }

        }
        
        return true;
    }

    /**
     * atualizarAliquotas
     *
     * @param String $T003_Id
     * @param String $C004_Id
     * @return String/bool
     */
    public function atualizarAliquotas($T003_Id= null, $C004_Id = false, $T004_Id = null, $arrayParametros = null)
    {
        // Personalizacao teste para ver se aqui esta o problema onde está zerando alguns valores de desconto
        // retirando esse campo do UPDATE T004_Percentual_Desconto = ((T004_Valor_Preco_Original - T004_Valor_Preco_Unitario) / T004_Valor_Preco_Original) * 100,
        // Esse código pode ser removido, teste da personalizacao 23/01/2025

        if(!isset($arrayParametros)){
            //Verifica se foi passado um T004 específico
            if($T004_Id){
                $extra = "AND T004_Id = '{$T004_Id}'";
            }

            $cClausula = "SELECT
            T004_Id,
            D009_Id,
            D001_Codigo_Produto,
            D001_Peso_Unitario_Kg,
            T003_Flag_Lista_Preco,
            T003_D024_Id,
            T003_Flag_Acp,
            T004_D006_Id,
            D001_Cubagem_Unitaria,
            D009_Preco_Pauta_ST,
            ifnull(T004_T219_Id,0) as T004_T219_Id,
            T004_Flag_Origem_Produto
            FROM T003
            LEFT JOIN T004 ON T003_Id=T004_T003_Id
            LEFT JOIN T006 ON T004_Id=T006_T004_Id
            LEFT JOIN D009 ON D009_Id=T004_D009_Id
            LEFT JOIN D049 ON D049_Id=D009_D049_Id
            LEFT JOIN D001 ON D001_Id=D049_D001_Id
                WHERE T003_Id='{$T003_Id}'
                AND T006_Id IS NULL {$extra}";

            $cmysql_T004 = mysql_query("$cClausula");
            $erro = mysql_error();
            if (!empty($erro)) {
                return "Erro consultando itens do orcamento: $erro $cClausula";
            }
            while($mT004 = mysql_fetch_array($cmysql_T004)){
                $D009_Id = $mT004['D009_Id'];
                $T003_Flag_Lista_Preco = $mT004['T003_Flag_Lista_Preco'];
                $T004_D006_Id = $mT004['T004_D006_Id'];
                $T004_T219_Id = $mT004['T004_T219_Id']; 
                $D001_Peso_Unitario_Kg = $mT004['D001_Peso_Unitario_Kg'];
                $D001_Cubagem_Unitaria = $mT004['D001_Cubagem_Unitaria'];
                $T004_Preco_Pauta_ST   = $mT004['D009_Preco_Pauta_ST'];
                $T004_Flag_Origem_Produto = $mT004['T004_Flag_Origem_Produto'];
            }
        }else{
            $D009_Id = $arrayParametros['D009_Id'];
            $T003_Flag_Lista_Preco = $arrayParametros['T003_Flag_Lista_Preco'];
            $T004_D006_Id          = $arrayParametros['T004_D006_Id'];
            $T004_T219_Id          = $arrayParametros['T004_T219_Id'];
            $D001_Peso_Unitario_Kg = $arrayParametros['D001_Peso_Unitario_Kg'];
            $D001_Cubagem_Unitaria = $arrayParametros['D001_Cubagem_Unitaria'];
            $T004_Preco_Pauta_ST   = $arrayParametros['D009_Preco_Pauta_ST'];
            $T004_Flag_Origem_Produto = $arrayParametros['T004_Flag_Origem_Produto'];
        }

        global $g;
        require_once('bibliotecas/classes/FIS003.php');
        $FIS003 = new FIS003();
        require_once('bibliotecas/classes/CAD002.php');
        $CAD002 = new CAD002();
        
        // Felipe Carrano 19/10/2023
        //Método retornaPrecoVenda utilizado apenas para pegar a origem do produto, não precisa das aliquotas
        //Chamando o aliquotas depois porque pode ter mudado a origem do produto e com isso pode mudar a aliquota do ICMS e IPI
      /*$Aliquotas = $this->retornaAliquotas($D009_Id, $T003_Id, false, false, $T004_Id);
        $demonstrativoNCM = $FIS003->demonstrativoNCM($Aliquotas['D005_Id']);
        $demonstrativoNCM = mysql_real_escape_string($demonstrativoNCM); */
        //$dadosPrecoVenda = $CAD002->retornaPrecoVenda($D009_Id, $T003_Flag_Lista_Preco, $T003_D024_Id, false, $T004_D006_Id, $Aliquotas['Aliquota_ICMS'], $T004_T219_Id, false, 4, $Aliquotas['Aliquota_PIS'], $Aliquotas['Aliquota_COFINS'], $Aliquotas['Aliquota_IPI'], $Aliquotas['ST_VA'], $Aliquotas['Percentual_Reducao_ICMS_ST'], $Aliquotas['ST_ICMS_Interno'], $Aliquotas['Percentual_Reducao_ICMS'], $Aliquotas['ICMS_Inter_Estadual'], $Aliquotas['Aliquota_IRPJ'], $Aliquotas['Aliquota_CSLL']);
        $dadosPrecoVenda = $CAD002->retornaPrecoVenda($D009_Id, $T003_Flag_Lista_Preco, $T003_D024_Id, false, $T004_D006_Id, 0, $T004_T219_Id, false, 4, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        $origemProduto   = $dadosPrecoVenda[5];

        if($T004_Flag_Origem_Produto != $origemProduto){
            // se alterou a origem pode mudar a aliquota do ICMS, atualiza aliquotas novamente
            mysqli_query("UPDATE T004 SET T004_Flag_Origem_Produto = '{$origemProduto}' WHERE T004_Id = '{$T004_Id}'");
        }

        $Aliquotas        = $this->retornaAliquotas($D009_Id, $T003_Id, false, false, $T004_Id);
        $demonstrativoNCM = $FIS003->demonstrativoNCM($Aliquotas['D005_Id']);
        $demonstrativoNCM = mysql_real_escape_string($demonstrativoNCM);

        if ($g['C031']['atualizarAliquotaUnidadeProduto']=='S'){
            $updateD037 =  "T004_D037_Id = D001_D037_Id, ";
        }

        mysql_query("UPDATE T004 
                  LEFT JOIN D009 ON D009_ID = T004_D009_Id 
                  LEFT JOIN D049 ON D049_Id = D009_D049_Id 
                  LEFT JOIN D001 on D001_Id = D049_D001_Id 
                        SET T004_D005_Id                            = '{$Aliquotas['D005_Id']}',
                            T004_Classificacao_Fiscal               = '{$Aliquotas['D005_Classificacao_Fiscal']}',
                            T004_Aliquota_IPI                       = '{$Aliquotas['Aliquota_IPI']}',
                            T004_Percentual_Reducao_IPI             = '{$Aliquotas['Percentual_Reducao_IPI']}',
                            T004_Aliquota_ICMS                      = '{$Aliquotas['Aliquota_ICMS']}',
                            T004_Aliquota_ICMS_Oculto               = '{$Aliquotas['Aliquota_ICMS_Oculto']}',
                            T004_Percentual_Reducao_ICMS            = '{$Aliquotas['Percentual_Reducao_ICMS']}',
                            T004_Percentual_Reducao_ICMS_ST         = '{$Aliquotas['Percentual_Reducao_ICMS_ST']}',
                            T004_Flag_ST                            = '{$Aliquotas['Flag_ST']}',
                            T004_ST_VA                              = '{$Aliquotas['ST_VA']}',
                            T004_ICMS_Inter_Estadual                = '{$Aliquotas['ICMS_Inter_Estadual']}',
                            T004_ST_ICMS_Interno                    = '{$Aliquotas['ST_ICMS_Interno']}',
                            T004_D059_Id                            = '{$Aliquotas['D059_Id']}',
                            T004_Flag_Situacao_Tributaria           = '{$Aliquotas['Flag_Situacao_Tributaria']}',
                            T004_ICMS_Inter_Estadual_MVA            = '{$Aliquotas['ST_ICMS_Inter_Estadual_MVA']}',
                            T004_ST_ICMS_Interno_MVA                = '{$Aliquotas['ST_ICMS_Interno_MVA']}',
                            T004_Flag_Isento_PIS                    = '{$Aliquotas['Flag_Isento_PIS']}',
                            T004_Aliquota_PIS                       = '{$Aliquotas['Aliquota_PIS']}',
                            T004_Situacao_Tributaria_PIS            = '{$Aliquotas['Situacao_Tributaria_PIS']}',
                            T004_Aliquota_COFINS                    = '{$Aliquotas['Aliquota_COFINS']}',
                            T004_Situacao_Tributaria_COFINS         = '{$Aliquotas['Situacao_Tributaria_COFINS']}',
                            T004_Flag_Isento_COFINS                 = '{$Aliquotas['Flag_Isento_COFINS']}',
                            T004_Situacao_Tributaria_IPI            = '{$Aliquotas['Situacao_Tributaria_IPI']}',
                            T004_Peso_Unitario                      = '{$D001_Peso_Unitario_Kg}',
                            T004_Cubagem_Unitaria                   = '{$D001_Cubagem_Unitaria}',
                            T004_Peso_Bruto                         = '{$Aliquotas['D001_Peso_Unitario_Bruto']}',
                            T004_Flag_Origem_Produto                = '{$origemProduto}',
                            T004_D005_Id_Info                       = '{$demonstrativoNCM}',
                            $updateD037
                            T004_Motivo_Desoneracao_ICMS            = '{$Aliquotas['Motivo_Desoneracao_ICMS']}',
                            T004_IPV_Sem_Lucro                      = '{$Aliquotas['IPV_Sem_Lucro']}',
                            T004_Aliquota_IRPJ                      = '{$Aliquotas['Aliquota_IRPJ']}',
                            T004_Flag_Isento_IRPJ                   = '{$Aliquotas['Flag_Isento_IRPJ']}',
                            T004_Aliquota_CSLL                      = '{$Aliquotas['Aliquota_CSLL']}',
                            T004_Flag_Isento_CSLL                   = '{$Aliquotas['Flag_Isento_CSLL']}',
                            T004_UF_ICMS_Substituicao_Devido        = '{$Aliquotas['UF_ICMS_Substituicao_Devido']}',
                            T004_Percentual_Base_Operacao_Propria   = '{$Aliquotas['Percentual_Base_Operacao_Propria']}',
                            T004_Percentual_Trib_Federal            = '{$Aliquotas['Percentual_Trib_Federal']}',
                            T004_Percentual_Trib_Estadual           = '{$Aliquotas['Percentual_Trib_Estadual']}',
                            T004_Percentual_Trib_Municipal          = '{$Aliquotas['Percentual_Trib_Municipal']}',
                            T004_Aliquota_ICMS_Credito              = '{$Aliquotas['Aliquota_ICMS_Credito']}',
                            T004_Modalidade_ICMS_Substituicao       = '{$Aliquotas['Modalidade_ICMS_Substituicao']}',
                            T004_Modalidade_ICMS                    = '{$Aliquotas['Modalidade_ICMS']}',
                            T004_Preco_Pauta_ST                     = '{$T004_Preco_Pauta_ST}',
                            T004_Percentual_Partilha_ICMS_Destino   = '{$Aliquotas['Percentual_Partilha_ICMS_Destino']}',
                            T004_Percentual_Partilha_ICMS_Origem    = '{$Aliquotas['Percentual_Partilha_ICMS_Origem']}'
                      WHERE T004_Id='{$T004_Id}'");
        $erro = mysql_error();
        
        if (!empty($erro)) {
            echo "\n\nErro atualizando item do orcamento ID {$T004_Id}: $erro\n\n";
            return "Erro atualizando item do orcamento ID {$T004_Id}: $erro";
        }

        mysql_query("UPDATE T004A SET T004A_Percentual_Reducao_PIS            = '{$Aliquotas['Percentual_Reducao_PIS']}',
                                      T004A_Percentual_Reducao_COFINS         = '{$Aliquotas['Percentual_Reducao_COFINS']}',
                                      T004A_Trib_Interna_UF_Dest_Reducao_Base = '{$Aliquotas['EIP001_Trib_Interna_UF_Dest_Reducao_Base']}',
                                      T004A_Percentual_FCP                    = '{$Aliquotas['Percentual_FCP']}'
                                WHERE T004A_T004_Id = '{$T004_Id}'");

        
        mysql_query("call T004_Gravar_Totalizacao_4('{$T004_Id}');");

        mysql_free_result($cmysql_T003);

        return true;
    }

    /**
     * cad002FotoProduto
     *
     * @param intiger $D001_Id
     * @param string $Tamanho
     * @return String/html
     */
     // Personalização mudando a ordem para usar o link primeiro referente a melhoria que Lucas solicitou
     // Onde ele vai ter link para ir para o orçamento/pedido ou foto no sistema
    function cad002FotoProduto($D001_Id, $tamanhoPx=30){
        global $g, $confUsuario;

        $T144 = mysql_query("SELECT T144_Id, T144_Data_Cadastro, D001_Descricao_Produto, T144_Url, T144_Descricao, T144_Ultimo_Acesso, T144_Versao_Cache 
                               FROM T144 
                          LEFT JOIN D001 ON D001_Id=T144_D001_Id 
                              WHERE T144_D001_Id = '{$D001_Id}'
                                AND T144_Flag_Tipo = 'F'");

        if (mysql_num_rows($T144)>0) {

            $mT144 = mysql_fetch_array($T144);
    
            $idProduto = $mT144['T144_Id'];
            $pathinfo = 'img';
            $dir     = $mT144['T144_Url'];
            $dirFull = $mT144['T144_Url'];
            $dirMini = $mT144['T144_Url'];

        } else {       

            $T172=mysql_query("SELECT * FROM T172 WHERE T172_D001_Id='{$D001_Id}' ORDER BY T172_Nome_Arquivo DESC LIMIT 1");

            $mT172=mysql_fetch_array($T172);
    
            $idFoto = $mT172['T172_Id'];
            $idProduto = $mT172['T172_D001_Id'];
            $pathinfo = pathinfo($mT172['T172_Nome_Arquivo']);
            $cArquivo = "{$g['pathRaiz']}dados_usuarios/{$confUsuario['dbDatabase']}/produtos/{$idProduto}/fotos/{$idFoto}.{$pathinfo['extension']}";
            
            $dir = $g['pathRaiz'] . "dados_usuarios/{$confUsuario['dbDatabase']}/produtos/{$idProduto}/fotos/{$idFoto}.{$pathinfo['extension']}";
            $dirFull = "/hardness3/dados_usuarios/{$confUsuario['dbDatabase']}/produtos/{$idProduto}/fotos/{$idFoto}.{$pathinfo['extension']}";
            $dirMini = "/hardness3/dados_usuarios/{$confUsuario['dbDatabase']}/produtos/{$idProduto}/fotos/{$idFoto}.{$pathinfo['extension']}";
            if (!file_exists($dir)){
                $dirMini = "/hardness3/static/img/indisponivel100x100.jpg";
                $dirFull = "/hardness3/static/img/indisponivel100x100.jpg";
            }
        }

/*      Comentado uso da imagem na tabela D003

        $D003=mysql_query("SELECT D003_Imagem FROM D001 left join D003 on D003_Id=D001_D003_Id WHERE D001_Id='{$D001_Id}'");
        $mD003=mysql_fetch_array($D003);
        
        $idProduto = $mD003['D003_Imagem'];
        $pathinfo = pathinfo($mD003['D003_Imagem']);
        $dir     = $g['pathRaiz'] . "dados_usuarios/{$confUsuario['dbDatabase']}/produtos/linhas/{$mD003['D003_Imagem']}";
        $dirFull = "/hardness3/dados_usuarios/{$confUsuario['dbDatabase']}/produtos/linhas/{$mD003['D003_Imagem']}";
        $dirMini = "/hardness3/dados_usuarios/{$confUsuario['dbDatabase']}/produtos/linhas/{$mD003['D003_Imagem']}";
        if (!file_exists($dir)){
            $dirMini = "/hardness3/static/img/indisponivel100x100.jpg";
            $dirFull = "/hardness3/static/img/indisponivel100x100.jpg";
        }
*/

	    if (!empty($idProduto) && $pathinfo['extension']=='pdf') {
            $dirMini = "/hardness3/static/img/format-pdf-file.png";
        }
    
        if (empty($idProduto)) {
            $dirMini = "/hardness3/static/img/indisponivel100x100.jpg";
            $dirFull = "/hardness3/static/img/indisponivel100x100.jpg";
        }

        $foto = "<img src='{$dirMini}' style='max-width:{$tamanhoPx}px;max-height:{$tamanhoPx}px' onclick=\"abrirJanela(false, '{$g['divIdRoot']}', '{$g['divId']}', unique(), '', 'Imagem Original', '/crm/crm001/outro/crm001ImagemOriginal/', '&urlImagem=' + encodeURIComponent('{$dirFull}') + '&D001_Id=' + encodeURIComponent('{$D001_Id}'), [1100,700])\"><br>";

        return $foto;
    }

    // Felipe Kadanos - 13/04/2026
    // Personalizado para uma melhoria que inativa a locação quando o saldo for <= 0
    function D001_reprocessa_historico($D001_Id = 0, $D009_Id = 0, $rapido = 0, $venda = false)
    {

        global $g;
        $D024_Id_Fornecedor = NULL;
        $mSaldo_T066        = array();
        $mFD024             = array();
        $mCD024             = array();
        $Custo_Tabela       = NULL;

        // Melhoria desempenho atualizacao estoque em venda, não está usando nenhuma das duas classes - Felipe 12/09/2022
        //require_once('bibliotecas/classes/VEN001.php');
        //$VEN001 = new VEN001();
        //require_once('bibliotecas/classes/VEN002.php');
        //$VEN002 = new VEN002();

        if ($D009_Id > 0) {
            $D001_Where = "D009_Id = '$D009_Id'";
        } else {
            $D001_Where = "D001_Id = '$D001_Id'";
        }

        // Melhoria desempenho atualizacao estoque em venda, não está usando o $mC020 em nenhum lugar da funcao - Felipe 12/09/2022
        //$C020  = mysql_query("SELECT * FROM C020 WHERE C020_C004_Id='{$g['empresaAtual']}'");
        //$mC020 = mysql_fetch_array($C020);
        
        $cSql_D009 = mysql_query("SELECT D009_Id,
                                         D009_Quantidade_Estoque,
                                         D009_Quantidade_Estoque_Liquido,
                                         D009_Valor_Custo_Medio_Unitario,
                                         D009_Valor_Custo_Compra,
                                         D009_Valor_Melhor_Custo,
                                         D009_Valor_Preco_Tabela,
                                         D049_Flag_Nacional_Importado,
                                         D049_Flag_Tipo,
                                         D049_Quantidade_Minima_Compra,
                                         D049_Quantidade_Embalagem_Compra,
                                         D009_Percentual_Desconto_Tabela,
                                         D009_Percentual_Acrescimo_Tabela,
                                         D009_Aliquota_ICMS_Tabela,
                                         D009_Flag_Usar_Custo,  
                                         D009_Meses_MMF,  
                                         D009_Meses_Compra,
                                         D049_Id,
                                         D009_C004_Id,
                                         D049_D001_Id,
                                         D009_D069_Id,
                                         D009_ICF_1,
                                         D009_IPV_1,
                                         D009_IPV_2,
                                         D009_IPV_3,
                                         D049_IPV,
                                         D049_IPV1,
                                         D049_IPV2,
                                         D009_Valor_Custo_ST_Medio_Unitario,
                                         D009_Valor_Custo_ST_Unitario,
                                         D049_Origem_Mercadoria,
                                         D049_Percentual_Acrescimo_Custo,
                                         D009_Preco_Loja_Real,
                                         D009_T001_Id, 
                                         D149_Flag_ST,
                                         D149_UF_ST,
                                         D009_D049_Id,
                                         D001_Codigo_Produto,
                                         D001_Id,
                                         D009_Data_Ultima_Entrada,
                                         D009A_Flag_Atualizar_Estoque,
                                         IFNULL(D009A_Id,0) as D009A_Id
                                   FROM D009 AS T1
                              LEFT JOIN D049 ON D049_Id=D009_D049_Id
                              LEFT JOIN D001 ON D001_Id=D049_D001_Id
                              LEFT JOIN D149 ON D149_D005_Id=D001_D005_Id and D149_C004_Id=D009_C004_Id
                              LEFT JOIN D009A ON D009_Id=D009A_D009_Id
                                  WHERE $D001_Where");
        
        if (mysql_num_rows($cSql_D009) <= 0) {
            return "O produto não foi encontrado: D001_Id: {$D001_Id} / D009_Id: {$D009_Id}";
        }

        while ($mD009 = mysql_fetch_assoc($cSql_D009)) {

            $D001_Id = $mD009['D001_Id'];
            $D001_Codigo_Produto = $mD009['D001_Codigo_Produto'];
            $locHistSaldo = array();
        
	            $cSql_T001           = mysql_query("SELECT T001_Quantidade,
	                                                        T001_Flag_Operacao,
	                                                        T001_Flag_Atualiza_Custo,
	                                                        T001_Quantidade_Saldo,
	                                                        T001_Valor_Custo_Unitario,
	                                                        T001_Valor_Custo_Medio_Unitario,
	                                                        T001_Id,
	                                                        T001_T066_Id,
	                                                        T001_Flag_MMF,
	                                                        T001_T008_Id,
	                                                        T001_Descricao_Operacao,
	                                                        T001_D024_Id,
                                                            T001_Data_Lancamento,
                                                            if(T001_Valor_Custo_Unitario>0,T001_Valor_Custo_Unitario,T001_Valor_Preco_Unitario) T001_Valor_Preco_Unitario,
                                                            D006_Flag_Venda_Compra_Outros,
                                                            T001_Valor_ST_Unitario,
                                                            T014_Flag_Origem_Produto,
                                                            concat(year(T001_Data_Lancamento),month(T001_Data_Lancamento)) mes,
                                                            T001_Valor_Custo_Mes_Anterior,
                                                            T001_Valor_Custo_Medio_Mes_Anterior,
                                                            T001_Quantidade_Saldo_Mes_Anterior,
                                                            T001_Quantidado_Saldo_Locacao,
                                                            T066_D004_Id,
                                                            T001_T014_Id,
                                                            T001_D009_Id,
                                                            T001_Estoque_Locacao,
                                                            T066_D009_Id,
                                                            D004_Local,
                                                            D004_Descricao, 
                                                            D004_Flag_Somar_Estoque_Fisico,
                                                            T001A_Valor_Custo_Unitario_Liquido
                                                       FROM T001
                                                  LEFT JOIN D006 ON D006_Id=T001_D006_Id
                                                  LEFT JOIN T014 ON T014_Id=T001_T014_Id
                                                  LEFT JOIN T066 ON T066_Id=T001_T066_Id 
                                                  LEFT JOIN D004 on D004_Id=T066_D004_Id
                                                  LEFT JOIN T001A ON T001_Id = T001A_T001_Id
                                                      WHERE T001_D009_Id='{$mD009['D009_Id']}' 
                                                   ORDER BY T001_Data_Lancamento, T001_Id");
            
            $count = 0;

            if($rapido>0){

                $count = mysql_num_rows($cSql_T001);
                $count = ( $count - ($rapido + 10) );
               
            }

            if ($rapido>0 and $count>0) {

                mysql_data_seek($cSql_T001, ($count + 10));

                $mcSql_T001 = mysql_fetch_assoc($cSql_T001);

                $Saldo                      = $mcSql_T001['T001_Quantidade_Saldo'];
                $Custo                      = $mcSql_T001['T001_Valor_Custo_Unitario'];
                $Custo_Medio                = $mcSql_T001['T001_Valor_Custo_Medio_Unitario'];
                $Custo_Compra               = $mcSql_T001['T001_Valor_Custo_Unitario'];
                $Custo_Liquido_Compra       = $mcSql_T001['T001A_Valor_Custo_Unitario_Liquido'];
                $Custo_ST                   = $mcSql_T001['T001_Valor_ST_Unitario'];
                $Custo_ST_Medio             = $mcSql_T001['T001_Valor_ST_Unitario'];
                $Custo_Utilizado            = '';
                //$Data_Ultima_Entrada        = $mcSql_T001['T001_Data_Lancamento'];
                // Estava atualizando a data errada, se não tivesse uma entrada no T001 dentro da quantidade do parametro do rápido, atualiza a data de compra com um registro de venda
                $Data_Ultima_Entrada        = $mD009['D009_Data_Ultima_Entrada'];
                $origemMercadoria           = $mD009['D049_Origem_Mercadoria'];
                $Mes                        = $mcSql_T001['mes'];
                $Custo_Mes_Anterior         = $mcSql_T001['T001_Valor_Custo_Mes_Anterior'];
                $Custo_Medio_Mes_Anterior   = $mcSql_T001['T001_Valor_Custo_Medio_Mes_Anterior'];
                $Saldo_Mes_Anterior         = $mcSql_T001['T001_Quantidade_Saldo_Mes_Anterior'];
                $T066_Id                    = $mcSql_T001['T001_T066_Id'];
                $D009_T001_Id               = $mD009['D009_T001_Id'];
                $D009_Data_Ultima_Venda     = '';
                $D009_Data_Ultima_Saida     = '';
                $D009_Data_Ultimo_Movimento = '';

                if ($mcSql_T001['T001_Flag_MMF'] == 'S'){
                    $D009_Data_Ultima_Venda = $mcSql_T001['T001_Data_Lancamento'];
                }
                if ($mcSql_T001['T001_Flag_Operacao'] == 'S'){
                    $D009_Data_Ultima_Saida = $mcSql_T001['T001_Data_Lancamento'];
                }
                $D009_Data_Ultimo_Movimento = $mcSql_T001['T001_Data_Lancamento'];

                $atualizaEstoqueLocacao = explode('|', $mcSql_T001['T001_Estoque_Locacao']);
                $estoquePorLocacao = unserialize($atualizaEstoqueLocacao[0]);
                $estoquePorLocacaoNaoSoma = unserialize($atualizaEstoqueLocacao[1]);

                if(number_format($Saldo, 4) != number_format(array_sum($estoquePorLocacao), 4)){
                    return " <b><font style='color:red;'> INFORME AO SUPORTE TÉCNICO O ERRO: </font></b> <br/><br/>Histórico Id " . $mcSql_T001['T001_Id'] . " possui a quantidade de saldo (T001_Quantidade_Saldo) diferente da quantidade de saldo locação (T001_Quantidado_Saldo_Locacao).";
                }
 
	            }else{
                
                $Saldo                      = 0;
                $Custo                      = 0;
                $Custo_Medio                = 0;
                $Custo_Compra               = 0;
                $Custo_Liquido_Compra       = 0;
                $Custo_ST                   = 0;
                $Custo_ST_Medio             = 0;
                $Custo_Utilizado            = '';
                $Data_Ultima_Entrada        = '0000-00-00';
                $estoquePorLocacao          = array();
                $estoquePorLocacaoNaoSoma   = array();
                $origemMercadoria           = $mD009['D049_Origem_Mercadoria'];
                $Mes                        = 0;
                $Custo_Mes_Anterior         = 0;
                $Custo_Medio_Mes_Anterior   = 0;
                $Saldo_Mes_Anterior         = 0;
                $D009_T001_Id               = 0;
                $D009_Data_Ultima_Venda     = '';
                $D009_Data_Ultima_Saida     = '';
	            $D009_Data_Ultimo_Movimento = '';


	            }

	            $t001LinhasLidas   = 0;
	            $t001LinhasPuladas = 0;

                foreach ($estoquePorLocacao as $T066_Id_Inicial => $SaldoPorLocacaoInicial) {
                    if ($SaldoPorLocacaoInicial > 0) {
                        $locacaoJaTeveEstoque[$T066_Id_Inicial] = true;
                    }
                }

                foreach ($estoquePorLocacaoNaoSoma as $T066_Id_Inicial => $SaldoPorLocacaoInicial) {
                    if ($SaldoPorLocacaoInicial > 0) {
                        $locacaoJaTeveEstoque[$T066_Id_Inicial] = true;
                    }
                }

	            while ($mT001 = mysql_fetch_assoc($cSql_T001)) {
	                $t001LinhasLidas++;
	                // Segurança extra: nunca processar histórico fora da empresa/D009 corrente (em caso de dados inconsistentes).
                    // Teve caso em alguns clientes que processou os dados com o T001 vinculado ao D009 da outra empresa, causando problema no custo e saldo
	                if ($mT001['T001_D009_Id'] !== $mD009['D009_Id']) {
	                    $t001LinhasPuladas++;
	                    continue;
	                }
	                
	                // Corrige histórico de histórico com locação de outra empresa
	                if ($mT001['T066_D009_Id']!=$mD009['D009_Id']){
	                    $T066_Id_Correto = mysql_query("select T066_Id, D004_Id from T066 left join D004 on D004_Id=T066_D004_Id where T066_D009_Id='{$mD009['D009_Id']}' and REPLACE(D004_Local,' ','')=REPLACE('{$mT001['D004_Local']}',' ','')");
                        $mT066_Id_Correto = mysql_fetch_array($T066_Id_Correto);
                        mysql_query("update T001 set T001_T066_Id='{$mT066_Id_Correto['T066_Id']}' where T001_Id='{$mT001['T001_Id']}'");
                        $mT001['T066_D004_Id'] = $mT066_Id_Correto['D004_Id'];
                     }

                    if ($mT001['mes']!=$Mes) {
                        $Mes = $mT001['mes'];
                        $Custo_Mes_Anterior = $Custo_Reais;
                        $Custo_Medio_Mes_Anterior = $Custo_Medio;
                        $Saldo_Mes_Anterior = $Saldo;
                    }

                    $D009_Data_Ultimo_Movimento = $mT001['T001_Data_Lancamento'];

                    if ($mT001['T001_Flag_Operacao'] != 'C') {
                        
                        if (($mT001['T001_Flag_Atualiza_Custo'] == 'S') and ($mT001['T001_Flag_Operacao'] != 'S')) {
                            if (($Saldo + $mT001['T001_Quantidade']) > 0 and $Saldo > 0 and $Custo_Medio > 0 and $mT001['T001_Flag_Operacao'] != 'B') {
                                $Custo_Medio = (($Saldo * $Custo_Medio) + ($mT001['T001_Valor_Preco_Unitario'] * $mT001['T001_Quantidade'])) / ($Saldo + $mT001['T001_Quantidade']);
                            } else {
                                $Custo_Medio = $mT001['T001_Valor_Preco_Unitario'];
                            }
                            $D009_T001_Id = $mT001['T001_Id'];
                            
                            // Custo ST Médio
                            if (($Saldo + $mT001['T001_Quantidade']) > 0 and $Saldo > 0 and $Custo_ST_Medio > 0 and $mT001['T001_Flag_Operacao'] != 'B') {
                                $Custo_ST_Medio = (($Saldo * $Custo_ST_Medio) + ($mT001['T001_Valor_ST_Unitario'] * $mT001['T001_Quantidade'])) / ($Saldo + $mT001['T001_Quantidade']);
                            } else {
                                $Custo_ST_Medio = $mT001['T001_Valor_ST_Unitario'];
                            }
                            
                            $Custo_Compra         = $mT001['T001_Valor_Custo_Unitario'];
                            $Custo_Liquido_Compra = $mT001['T001A_Valor_Custo_Unitario_Liquido'];

                            $Custo_ST             = $mT001['T001_Valor_ST_Unitario'];
                            $Data_Ultima_Entrada  = $mT001['T001_Data_Lancamento'];
                            $origemMercadoria     = $mT001['T014_Flag_Origem_Produto'];
                            
                            $D024_Id              = $mT001['T001_D024_Id'];
                            $D024_Id_Fornecedor   = $mT001['T001_D024_Id'];
                            $mFD024['{$D024_Id}'] = $D024_Id;
                        }

                        // por locação
                        $T066_Id = $mT001['T001_T066_Id'];
                        
                        
                        if($mT001['D004_Flag_Somar_Estoque_Fisico'] == 'S' || empty($mT001['D004_Flag_Somar_Estoque_Fisico'])){
                            if (!empty($T066_Id) && !isset($estoquePorLocacao[$T066_Id])) {
                                $estoquePorLocacao[$T066_Id] = 0;
                            }
                        } else {
                            if (!empty($T066_Id) && !isset($estoquePorLocacaoNaoSoma[$T066_Id])) {
                                $estoquePorLocacaoNaoSoma[$T066_Id] = 0;
                            }
                        }

                        $saldoT066Anterior = 0;
                        if (!empty($T066_Id)) {
                            if($mT001['D004_Flag_Somar_Estoque_Fisico'] == 'S' || empty($mT001['D004_Flag_Somar_Estoque_Fisico'])){
                                $saldoT066Anterior = $estoquePorLocacao[$T066_Id];
                            } else {
                                $saldoT066Anterior = $estoquePorLocacaoNaoSoma[$T066_Id];
                            }
                        }

                        if ($mT001['T001_Flag_Operacao'] == 'S') {

                            if ($mT001['T001_Flag_MMF'] == 'S'){
                                $D009_Data_Ultima_Venda = $mT001['T001_Data_Lancamento'];
                            }
                            $D009_Data_Ultima_Saida = $mT001['T001_Data_Lancamento'];

                            if ($mT001['D006_Flag_Venda_Compra_Outros'] == 'V') {
                                $D024_Id             = $mT001['T001_D024_Id'];
                                $mCD024['{$D024_Id'] = $D024_Id;
                            }
                            // por locacao
                            if (!empty($T066_Id)) {
                            if($mT001['D004_Flag_Somar_Estoque_Fisico'] == 'S' || empty($mT001['D004_Flag_Somar_Estoque_Fisico'])){
                                    $estoquePorLocacao[$T066_Id] -= $mT001['T001_Quantidade'];
                            } else {
                                    $estoquePorLocacaoNaoSoma[$T066_Id] -= $mT001['T001_Quantidade'];
                                }
                            }
                            if ($mT001['T001_T008_Id'] > 0 && $venda !== true){
                            $this->populaT290($mT001['T001_D009_Id'], $mT001['T001_T008_Id'], $mT001['T001_Quantidade'], $mT001['T001_Flag_Operacao'], $mT001['T001_Data_Lancamento']); 
                            }
                        } else if ($mT001['T001_Flag_Operacao'] == 'E') {
                            //if ($T066_Id>0) { $mSaldo_T066[$T066_Id]+=$mT001['T001_Quantidade']; }
                            // por locacao
                            if (!empty($T066_Id)) {
                                if($mT001['D004_Flag_Somar_Estoque_Fisico'] == 'S' || empty($mT001['D004_Flag_Somar_Estoque_Fisico'])){
                                    $estoquePorLocacao[$T066_Id] += $mT001['T001_Quantidade'];
                                } else {
                                    $estoquePorLocacaoNaoSoma[$T066_Id] += $mT001['T001_Quantidade'];
                                }
                            }
                            // Popula a tabela T289 apenas com nota de entrada
                            if ( ( $mT001['T001_T014_Id'] > 0 || $mT001['T001_T008_Id'] > 0) && $venda !== true ) {
                                $this->populaT289($mT001['T001_D009_Id'], $mT001['T001_T014_Id'], $mT001['T001_T008_Id'], $mT001['T001_Quantidade'], $mT001['T001_Flag_Operacao'], $mT001['T001_Data_Lancamento']); 
                            }
                        } else if ($mT001['T001_Flag_Operacao'] == 'B') {
                            //if ($T066_Id>0) { $mSaldo_T066[$T066_Id]=$mT001['T001_Quantidade']; }
                            // por locacao
                            if (!empty($T066_Id)) {
                                if($mT001['D004_Flag_Somar_Estoque_Fisico'] == 'S' || empty($mT001['D004_Flag_Somar_Estoque_Fisico'])){
                                    $estoquePorLocacao[$T066_Id] = $mT001['T001_Quantidade'];
                                } else {
                                    $estoquePorLocacaoNaoSoma[$T066_Id] = $mT001['T001_Quantidade'];
                                }
                            }
                        }
                        $Saldo = array_sum($estoquePorLocacao);

                        if (!empty($T066_Id)) {
                            if($mT001['D004_Flag_Somar_Estoque_Fisico'] == 'S' || empty($mT001['D004_Flag_Somar_Estoque_Fisico'])){
                                $saldoT066Atual = $estoquePorLocacao[$T066_Id];
                            } else {
                                $saldoT066Atual = $estoquePorLocacaoNaoSoma[$T066_Id];
                            }

                            if (!isset($locHistSaldo[$T066_Id])) {
                                $locHistSaldo[$T066_Id] = array();
                            }
                            $locHistSaldo[$T066_Id][] = array(
                                'anterior' => (float) $saldoT066Anterior,
                                'atual' => (float) $saldoT066Atual
                            );
                        }
                    }

                    // Gera um Update em T289 caso a nota de compra tenha sido cancelada
                    if ($mT001['T001_Flag_Operacao'] == 'C' && $mT001['D006_Flag_Venda_Compra_Outros'] == 'C' && $mT001['T001_T014_Id'] > 0 && $venda !== true) {
                        $this->populaT289($mT001['T001_D009_Id'], $mT001['T001_T014_Id'], $mT001['T001_T008_Id'], $mT001['T001_Quantidade'], $mT001['T001_Flag_Operacao'], $mT001['T001_Data_Lancamento']);
                    }

                    // Gera um Update em T290 caso a nota de venda tenha sido cancelada
                    if ($mT001['T001_Flag_Operacao'] == 'C' && $mT001['D006_Flag_Venda_Compra_Outros'] == 'V' && $mT001['T001_T008_Id'] > 0 && $venda !== true) {
                    $this->populaT290($mT001['T001_D009_Id'], $mT001['T001_T008_Id'], $mT001['T001_Quantidade'], $mT001['T001_Flag_Operacao'], $mT001['T001_Data_Lancamento']); 
                    }

                    if($mT001['D004_Flag_Somar_Estoque_Fisico'] == 'S' || empty($mT001['D004_Flag_Somar_Estoque_Fisico'])){
                        $saldoT066 = $estoquePorLocacao[$T066_Id];
                    } else {
                        $saldoT066 = $estoquePorLocacaoNaoSoma[$T066_Id];
                    }
                    
                    $atualizaEstoqueLocacao = serialize($estoquePorLocacao) . '|' . serialize($estoquePorLocacaoNaoSoma);

	                $Custo_Reais = $Custo_Compra;
	                if ((number_format($Saldo, 2) != number_format($mT001['T001_Quantidade_Saldo'], 2)) 
	                or (number_format($saldoT066, 2) != number_format($mT001['T001_Quantidado_Saldo_Locacao'], 2))
	                or (number_format($Custo_Medio, 2) != number_format($mT001['T001_Valor_Custo_Medio_Unitario'], 2)) 
	                or (number_format($mT001['T001_Valor_Custo_Unitario'], 2) != number_format($Custo_Reais, 2))
	                or (($mT001['T001_Valor_Custo_Unitario'] <= 0) and ($Custo_Reais > 0))
	                or (number_format($Saldo_Mes_Anterior, 2) != number_format($mT001['T001_Quantidade_Saldo_Mes_Anterior'], 2))
	                or (number_format($Custo_Mes_Anterior, 2) != number_format($mT001['T001_Valor_Custo_Mes_Anterior'], 2))
	                or (number_format($Custo_Medio_Mes_Anterior, 2) != number_format($mT001['T001_Valor_Custo_Medio_Mes_Anterior'], 2))
	                or ($atualizaEstoqueLocacao != $mT001['T001_Estoque_Locacao'])) {
	                    mysql_query("UPDATE T001  
	                                        LEFT JOIN D006 ON D006_Id=T001_D006_Id
	                                             SET T001_Quantidade_Saldo='$Saldo',
                                                 T001_Quantidado_Saldo_Locacao='{$saldoT066}',
                                                 T001_Valor_Custo_Unitario=if(IFNULL(D006_Flag_Venda_Compra_Outros,'')!='C','$Custo_Reais',T001_Valor_Custo_Unitario),
                                                 T001_Valor_Custo_Medio_Unitario='$Custo_Medio',
                                                 T001_Valor_Custo_Mes_Anterior='$Custo_Mes_Anterior',
                                                 T001_Valor_Custo_Medio_Mes_Anterior='$Custo_Medio_Mes_Anterior',
                                                 T001_Quantidade_Saldo_Mes_Anterior='$Saldo_Mes_Anterior',
                                                 T001_Estoque_Locacao='$atualizaEstoqueLocacao'
	                                           WHERE T001_Id='{$mT001['T001_Id']}'");
	                }
	
	            }

                // Teve caso em alguns clientes que processou os dados com o T001 vinculado ao D009 da outra empresa, causando problema no custo e saldo
                // Se não processou nada por algum erro de T001 errado, manda para o proesso automatico novamente
	            $t001LinhasProcessadas = ($t001LinhasLidas - $t001LinhasPuladas);
               
	            if ($t001LinhasLidas > 0 && $t001LinhasProcessadas <= 0) {
                    if (!empty($mD009['D009A_Id']) && $mD009['D009A_Id'] > 0) {
                        mysqli_query("UPDATE D009A
                                         SET D009A_Flag_Atualizar_Estoque = 'S'
                                       WHERE D009A_D009_Id = '{$mD009['D009_Id']}'");
                    } else {
                        mysqli_query("INSERT INTO D009A (D009A_D009_Id, D009A_Flag_Atualizar_Estoque)
                                        VALUES ('{$mD009['D009_Id']}', 'S')");
                    }
                    continue;                
	            } 

	            // por locação
	            foreach ($estoquePorLocacao as $T066_Id => $SaldoPorLocacao) {
	                mysql_query("UPDATE T066 SET T066_Quantidade_Estoque = '{$SaldoPorLocacao}' WHERE T066_Id = '{$T066_Id}' AND T066_D009_Id='{$mD009['D009_Id']}'");
	            }            
	            // por locação
	            foreach ($estoquePorLocacaoNaoSoma as $T066_Id => $SaldoPorLocacao) {
	                mysql_query("UPDATE T066 SET T066_Quantidade_Estoque = '{$SaldoPorLocacao}' WHERE T066_Id = '{$T066_Id}' AND T066_D009_Id='{$mD009['D009_Id']}'");
	            }  

                $this->atualizaFlagAtivoLocacao($locHistSaldo);
            
            
            if ( $venda === true ) {
                // Case seja atualização estoque modo VENDA atualiza estoque liquido das locações e finaliza a function

                // T066_Quantidade_Estoque_Liquido
                mysql_query("UPDATE T066 
                                SET T066_Quantidade_Estoque_Liquido = (T066_Quantidade_Estoque-D009_Quantidade_Pedido_Por_Locacao(T066_D009_Id, T066_Id))
                              WHERE T066_D009_Id = '{$mD009['D009_Id']}'");

                mysql_query("UPDATE D009
                                SET D009_Quantidade_Estoque='{$Saldo}',
                                    D009_Data_Ultima_Venda='{$D009_Data_Ultima_Venda}',
                                    D009_Data_Ultima_Saida='{$D009_Data_Ultima_Saida}',
                                    D009_Data_Ultimo_Movimento='{$D009_Data_Ultimo_Movimento}'
                              WHERE D009_Id='{$mD009['D009_Id']}'");                
                
                //Flag utilizada no processo automático para atualizar estoque
                if($mD009['D009A_Id'] > 0){
                    mysqli_query("UPDATE D009A SET D009A_Flag_Atualizar_Estoque = 'S'
                                             WHERE D009A_D009_Id = '{$mD009['D009_Id']}'");
                } else {
                    mysqli_query("INSERT INTO D009A (D009A_D009_Id, 
                                                     D009A_Flag_Atualizar_Estoque
                                            )VALUES('{$mD009['D009_Id']}',
                                                    'S')");
                }


                // Melhoria desempenho atualizacao estoque em venda, vai fazer pela chamada do crontab - Felipe 12/09/2022
                //if($Saldo <= 0){
                    // Se o estoque zerar, atualiza o custo para pegar a regra configurada do custo com estoque 0
                //    $this->atualizarCustoProduto($mD009['D009_Id'], $dadosCusto);               
                //} 

                continue;

            }   

            $Saldo_Regras = $Saldo;            
            $dadosCusto = array(
                'Custo_Medio'           => $Custo_Medio, 
                'Custo_Compra'          => $Custo_Compra, 
                'Saldo_Regras'          => $Saldo_Regras, 
                'Data_Ultima_Entrada'   => $Data_Ultima_Entrada, 
                'Custo_Liquido_Compra'  => $Custo_Liquido_Compra, 
            );
            
            //Atualiza o custo do produto
            $this->atualizarCustoProduto($mD009['D009_Id'], $dadosCusto);

            //Atualiza os dados estatisticos utilizados em compras de estoque
            $this->atualizarEstatisticasCompra($mD009['D009_Id'], $Saldo);

            if ($Custo < 0) {
                $Custo = 0;
            }
            
            if ($mD009['D009_ICF_1'] <= 0) {
                $mD009['D009_ICF_1'] = 0;
            }
            //if ($Custo <= 0 or ($Saldo <= 0 AND $Custo_Tabela > 0)) {
            //    $Custo = $Custo_Tabela;
            //    $Data_Alteracao_Custo = $Data_Alteracao_Tabela;
            //    $Custo_Utilizado = 'T';
            //}



			// Verifica caso o campo US ST esteja preenchida na NCM se a UF destino está na lista
			$D018_UF_ST = mysql_query("SELECT D018_UF FROM D018 WHERE D018_Id='{$g['c004']['C004_D018_Id']}'");
			$mD018_UF_ST = mysql_fetch_array($D018_UF_ST);
            if ($mD009['D149_Flag_ST'] == 'S' && $mD009['D149_UF_ST'] !='') {
                $pos = strpos($mD009['D149_UF_ST'], $mD018_UF_ST['D018_UF']);
                if ($pos === false) {
                    $mD009['D149_Flag_ST'] = "N";
                }
            }


            $arrayPrecoLoja = $this->RetornaPrecoLoja($mD009);

            $preco = $arrayPrecoLoja['preco'];
            $demonstrativo = $arrayPrecoLoja['demonstrativo'];

            $diasMP=$g['C031']['diasMedioPrazo'];
            $data = strtotime("+$diasMP day", strtotime("now"));
            $dataMP = date("Y-m-d", $data); 
            
            $retorno=mysql_query("select sum(T055_Quantidade) as QteOC from T055 left join T075 on T075_Id=T055_T075_Id where T055_D009_Id='{$mD009['D009_Id']}' AND ifnull(T075_Data_Estoque,'0000-00-00')='0000-00-00' and T075_Data_Previsao<='{$dataMP}' and T075_Data_Previsao>0 and T075_Flag_MP = 1");
            $retorno=mysql_fetch_array($retorno);            
            $qteOC=$retorno['QteOC'];
            
            $retorno=mysql_query("SELECT T075_Id FROM T075 LEFT JOIN T055 ON T075_Id=T055_T075_Id LEFT JOIN D009 ON T055_D009_Id=D009_Id WHERE T055_D009_Id='{$mD009['D009_Id']}' and T075_Data_Previsao<='{$dataMP}' and T075_Flag_MP = 1 group by T075_Id");
            $qtePed=0;
            while ($ret=mysql_fetch_array($retorno)){
                $retorno1=mysql_query("select sum(T006_Quantidade) 
                    from T006 
                    left join T005 on T005_Id=T006_T005_Id 
                    where T006_D009_Id='{$mD009['D009_Id']}'
                    and T005_Flag_Status!=5
                    and T005_Flag_Status!='7'
                    and T005_Flag_Status!='8'
                    and T005_Flag_Status!='11'
                    and T006_T075_Id='{$ret['T075_Id']}'
                    and (T005_T005_Id_Agrupado<=0 OR T005_T005_Id_Agrupado IS NULL)");
                $ret1=mysql_fetch_array($retorno1);
                $qtePed+=$ret1[0];
            }
            $D009_Quantidade_Estoque_Medio_Prazo= ($qteOC-$qtePed);

             // D009_Quantidade_DF=D009_Saldo_DF(D009_Id),
             // D009_Quantidade_DM=D009_Saldo_DM(D009_Id),
            mysql_query("UPDATE D009
                             SET D009_Quantidade_Estoque='$Saldo',
                                 D009_T001_Id=IF(D009_Valor_Custo_Compra>0,'{$D009_T001_Id}',0),
                                 D009_Valor_Custo_ST_Medio_Unitario='{$Custo_ST_Medio}',
                                 D009_Valor_Custo_ST_Unitario='{$Custo_ST}',
                                 D009_Quantidade_OC=D009_Quantidade_OC_3(D009_Id,0),
                                 D009_Quantidade_OP=D009_Quantidade_OP(D009_Id),
                                 D009_Quantidade_OE=D009_Saldo_OE(D009_Id,0),
                                 D009_Quantidade_Estoque_Tabela=D009_Quantidade_Estoque_Tabela(D009_Id),
                                 D009_Quantidade_Estoque_Medio_Prazo='{$D009_Quantidade_Estoque_Medio_Prazo}',
                                 D009_Data_Reprocessa_Historico=now(),
                                 D009_Preco_Loja_Real = TRUNCATE('{$preco}', 2), 
                                 D009_Preco_Loja_Real_T001_Id = '{$mD009['D009_T001_Id']}', 
                                 D009_Preco_Loja_Real_Demonstrativo = '{$demonstrativo}',
                                 D009_Data_Ultima_Venda='{$D009_Data_Ultima_Venda}',
                                 D009_Data_Ultima_Saida='{$D009_Data_Ultima_Saida}',
                                 D009_Data_Ultimo_Movimento='{$D009_Data_Ultimo_Movimento}'
                           WHERE D009_Id='{$mD009['D009_Id']}'");

            $sqlSaldo = "SELECT (D009_Quantidade_Estoque-D009_Saldo_OE(D009_Id,0)-D009_Quantidade_Estoque_Loja) as Saldo FROM D009 WHERE D009_Id='{$mD009['D009_Id']}'";
            $resSaldo = mysql_query($sqlSaldo);
            $rowSaldo = mysql_fetch_assoc($resSaldo);
            $Saldo = $rowSaldo['Saldo'];

            if ($D009_Id>0) {
                /* Como o D009_Id é maior que zero(foi enviado por parametro) força o recalculo(chama trigger D009_trigger_estoque_real)
                dos estoques de todos os D009 do produto, isso acontece pois utiliza a chave estrangeira dos D009, a D049_Id no where.
                Isso faz com que na trigger todos os D009 são chamados para UPDATE. Utilizamos mysqli para não chamar as triggers
                do php e economizar em performance*/
                mysqli_query("UPDATE D009
                                 SET D009_Quantidade_Estoque_Fora = 0
                               WHERE D009_D049_Id = '{$mD009['D009_D049_Id']}'");
            } else {
                /*Força o recalculo do estoque líquido (o estoque da LOJA pode ter mudado, e ele é deduzido do líquido na trigger)
                Recalcula todos os itens do D009 pois estamos num loop a partir da D001(linha 1078)*/
                mysqli_query("UPDATE D009
                                 SET D009_Quantidade_Estoque_Liquido=0 
                               WHERE D009_Id='{$mD009['D009_Id']}'");
            }

            //Flag utilizada no processo automático para atualizar estoque
            //Processou o estoque completo, altera flag para N
            mysqli_query("UPDATE D009A SET D009A_Flag_Atualizar_Estoque = 'N'
                                     WHERE D009A_D009_Id = '{$mD009['D009_Id']}'");
            // Estoque por locação
            $this->processaEstoqueLiquidoPorLocacao($mD009['D009_Id']);

            // Reprocessa estoque dos pedidos
            $this->processaEstoquePedidos($mD009['D009_Id'], $Saldo);

            // Reprocessa OC dos pedidos
            $this->reprocessaOCPedidos($mD009['D009_Id']);

            // Reprocessa OP dos pedidos
            $this->reprocessaOPPedidos($mD009['D009_Id']);


            // Atualiza preço tabela orçamento 
            if($g['C031']['atualizaPrecoTabelaOrcamento'] == 'S'){
                $T004 = mysql_query("SELECT D009_Valor_Custo_Unitario,
                                            D009_ICF_1,
                                            D049_Origem_Mercadoria,
                                            D009_T001_Id,
                                            D009_Id,
                                            D049_Id,
                                            T003_Flag_Lista_Preco,
                                            T003_D024_Id,
                                            T003_Flag_ACP,
                                            T004_D006_Id,
                                            T004_Aliquota_ICMS,
                                            T004_Aliquota_PIS,
                                            T004_Aliquota_COFINS,
                                            T004_Aliquota_IPI,
                                            D009_T001_Id,
                                            T004_T219_Id,
                                            T004_Id,
                                            T219_Flag_Comprar,
                                            T004_Flag_Origem_Produto,
                                            T004_D005_Id,
                                            T004_D006_Id,
                                            T004_T003_Id,
                                            T219_Flag_Origem_Produto,
                                            T219_Valor_Custo_Unitario,
                                            T004_ST_VA,
                                            T004_Percentual_Reducao_ICMS_ST,
                                            T004_ST_ICMS_Interno,
                                            T004_Percentual_Reducao_ICMS,
                                            T004_ICMS_Inter_Estadual,
                                            T004_Aliquota_IRPJ,
                                            T004_Aliquota_CSLL,
                                            T004_Flag_Transferencia,
                                            D009_Quantidade_Estoque_Liquido,
                                            T004_Quantidade,
                                            T004_C004_Id_Transferencia,
                                            T004_Valor_Preco_Original,
                                            T004_Valor_Custo_Unitario,
                                            T004_Valor_Preco_Tabela,
                                            T004_Valor_Preco_Transferencia,
                                            T004_T001_Id,
                                            T006_T219_Id,
                                            T219_T218_Id,
                                            T003A_Flag_Ratear_Frete_Custo,
                                            T003_Casas_Decimais_Preco                                            
                                            FROM T004
                                        LEFT JOIN T006 ON T006_T004_Id=T004_Id
                                        LEFT JOIN D009 ON D009_Id=T004_D009_Id
                                        LEFT JOIN D049 ON D049_Id=D009_D049_Id
                                        LEFT JOIN T003 ON T003_Id=T004_T003_Id
                                        LEFT JOIN T001 ON T001_Id=D009_T001_Id
                                        LEFT JOIN T219 ON T219_Id=T004_T219_Id
                                        LEFT JOIN T003A ON T003_Id=T003A_T003_Id
                                            WHERE T004_D009_Id='{$mD009['D009_Id']}'
                                            AND (T006_Id IS NULL OR (IFNULL(T004_T219_Id,0) <= 0 AND T004_Flag_Comprar = 'S'))
                                            AND T003_Data_Emissao BETWEEN DATE_SUB(CURDATE(), INTERVAL 90 DAY) AND CURRENT_DATE()
                                            AND T003_Flag_Perdido!='S'");
                while ($mT004 = mysql_fetch_array($T004)) {
                    //Método de atualização do preço tabela dos produtos
                    $this->atualizaPrecoTabelaOrcamento($mT004);
                }
            }

            if($g['C031']['atualizaPrecoTabelaPedido'] == 'S'){
                // Atualiza preço tabela pedido 
                $T006 = mysql_query("SELECT D009_Valor_Custo_Unitario,
                                            D009_ICF_1,
                                            D049_Origem_Mercadoria,
                                            D009_T001_Id,
                                            D009_Id,
                                            D049_Id,
                                            T005_Flag_Lista_Preco,
                                            T005_D024_Id,
                                            T005_Flag_ACP,
                                            T005_C004_Id,
                                            T006_D006_Id,
                                            T005_Aliquota_ICMS,
                                            T006_Flag_Destino_Produto,
                                            D009_T001_Id,
                                            T006_Id,
                                            T005_Id,
                                            T219_Flag_Origem_Produto,
                                            T006_D005_Id,
                                            T006_Flag_Origem_Produto,
                                            T219_Flag_Comprar,
                                            T219_Valor_Custo_Unitario,
                                            T006_T219_Id,
                                            T006_T225_Id,
                                            T006_Aliquota_ICMS,
                                            T006_Aliquota_PIS,
                                            T006_Aliquota_COFINS,
                                            T006_Aliquota_IPI,
                                            T006_D009_Id,
                                            T006_T005_Id,
                                            T006_Valor_Preco_Original,
                                            T006_Valor_Custo_Unitario,
                                            ifnull(T005_T224_Id,0) as T005_T224_Id,
                                            T006_Valor_Preco_Tabela,
                                            T006_ST_VA,
                                            T006_Percentual_Reducao_ICMS_ST,
                                            T006_ST_ICMS_Interno,
                                            T006_Percentual_Reducao_ICMS,
                                            T006_ICMS_Inter_Estadual,
                                            T006_Aliquota_IRPJ,
                                            T006_Aliquota_CSLL,
                                            T006_Flag_Transferencia,
                                            T006_Quantidade_Estoque,
                                            T006_Quantidade,
                                            T006_C004_Id_Transferencia,
                                            T006_Valor_Preco_Transferencia,
                                            T006_T001_Id,
                                            T005A_Flag_Ratear_Frete_Custo,
                                            T005_Casas_Decimais_Preco
                                    FROM T006
                                LEFT JOIN T008 ON T008_T006_Id=T006_Id
                                LEFT JOIN T007 ON T007_Id=T008_T007_Id
                                LEFT JOIN D009 ON D009_Id=T006_D009_Id
                                LEFT JOIN D049 ON D049_Id=D009_D049_Id
                                LEFT JOIN T005 ON T005_Id=T006_T005_Id
                                LEFT JOIN T001 ON T001_Id=D009_T001_Id
                                LEFT JOIN T219 ON T219_Id=T006_T219_Id
                                LEFT JOIN T005A ON T005_Id=T005A_T005_Id
                                    WHERE T006_D009_Id='{$mD009['D009_Id']}'
                                        AND (T008_Id IS NULL OR T007_Flag_Cancelada='S')
                                        AND T005_Flag_Status!='8'
                                        AND (T005_T005_Id_Agrupado<=0 OR T005_T005_Id_Agrupado IS NULL)");
                while ($mT006 = mysql_fetch_array($T006)) {
                    //Método de atualização do preço tabela dos produtos
                    $this->atualizaPrecoTabelaPedido($mT006);
                }
            }
        }

        // Reprocessa estoque similares
        // Melhoria desempenho atualizacao estoque em venda, vai fazer pela chamada do crontab - Felipe 12/09/2022
        if (!empty($D001_Codigo_Produto) && $venda !== true) {
            $this->reprocessaEstoqueSimilares($D001_Id, $D001_Codigo_Produto);
        }

        mysql_free_result($cSql_D009);
        mysql_free_result($cSql_T001);
        mysql_free_result($retorno);
        mysql_free_result($T004);
        mysql_free_result($T006);

        return true;
    }

    // Felipe Kadanos - 13/04/2026
    // Melhoria para inativar a locação.
    // Se a locação PENDENTE tiver Saldo atual 0 e não tiver nenhuma NF Recebida ou DI pendente que ainda não foi finalizada o sistema inativa essa locação.
    // Para o resto das locações o sistema ve se a locação ja teve saldo positivo e se o saldo atual for <= 0 inativa ela.
    public function atualizaFlagAtivoLocacao($locHistSaldo) {
        foreach ($locHistSaldo as $T066_Id => $hist) {
            if (empty($T066_Id)) {
                continue;
            }

            $flagAtivo = 'S';

            // Ve as movimentação da loc
            $teveSaldoPos = false;
            $saldoAtual = 0;
            foreach ($hist as $mov) {
                $anteriorMov = isset($mov['anterior']) ? (float) $mov['anterior'] : 0;
                $atualMov = isset($mov['atual']) ? (float) $mov['atual'] : 0;
                $saldoAtual = $atualMov;
                if ($anteriorMov > 0 || $atualMov > 0) {
                    // Tem saldo positivo no historico
                    $teveSaldoPos = true;
                }
            }

            // Verifica se a locação é pendente
            $sqlLoc = "SELECT 1 FROM T066
                            LEFT JOIN D004 ON D004_Id = T066_D004_Id
                        WHERE T066_Id = '{$T066_Id}'
                            AND (D004_Id = '1812' OR D004_Local = 'PENDENTE')
                        LIMIT 1";
            $resLoc = mysqli_query($sqlLoc);
            $locPen = ($resLoc && mysql_num_rows($resLoc) > 0); // true | false

            // Fluxo locação pendente: inativa somente se NÃO houver NF/DI pendente
            if ($locPen) {
                // Verifica se existe uma NF de entrada pendente para locação PENDENTE
                $sql = "SELECT 1 FROM T001
                            LEFT JOIN T014 ON T014_Id = T001_T014_Id
                            LEFT JOIN T013 ON T013_Id = T014_T013_Id
                            LEFT JOIN D006 ON D006_Id = T014_D006_Id
                        WHERE T001_T066_Id = '{$T066_Id}'
                            AND IFNULL(T013_Flag_Cancelada, 'N') != 'S'
                            AND IFNULL(D006_Flag_Venda_Compra_Outros, '') = 'C'
                            AND IFNULL(T013_Flag_Estoque_Finalizado, 'N') != 'S'
                        LIMIT 1";
                $resNFe = mysqli_query($sql);
                $temNFe = ($resNFe && mysql_num_rows($resNFe) > 0); // true | false

                // Verifica se existe uma DI pendente para locação PENDENTE
                $di = "SELECT 1 FROM T055
                           LEFT JOIN T075 ON T075_Id = T055_T075_Id
                       WHERE T055_T066_Id = '{$T066_Id}'
                           AND IFNULL(T075_Data_Estoque, '0000-00-00') = '0000-00-00'
                       LIMIT 1";
                $resDi = mysqli_query($di);
                $temDi = ($resDi && mysql_num_rows($resDi) > 0); // true | false

                log("loc {$T066_Id} [PENDENTE] - saldoAtual: {$saldoAtual} - temNFe: " . (($temNFe) ? 'Sim' : 'Não') . " - temDi: " . (($temDi) ? 'Sim' : 'Não'));
                if ($saldoAtual <= 0 && !$temNFe && !$temDi) {
                    log("inativa loc {$T066_Id} [PENDENTE Saldo atual 0 e sem NF/DI pendente]");
                    $flagAtivo = 'N';
                }
            } else {
                // Fluxo padrão
                log("loc {$T066_Id} [PADRAO] - teveSaldoPos: " . (($teveSaldoPos) ? 'Sim' : 'Não') . " - saldoAtual: {$saldoAtual}");
                if ($teveSaldoPos && $saldoAtual <= 0) {
                    log("inativa loc {$T066_Id} [PADRAO]");
                    $flagAtivo = 'N';
                }
            }

            $T066A = mysql_query("SELECT T066A_Id,
                                         IFNULL(T066A_Flag_Ativo, 'S') as T066A_Flag_Ativo
                                    FROM T066A
                                   WHERE T066A_T066_Id = '{$T066_Id}'");
            if ($T066A && mysql_num_rows($T066A) > 0) {
                $mT066A = mysql_fetch_assoc($T066A);

                if ($mT066A['T066A_Flag_Ativo'] != $flagAtivo) {
                    mysql_query("UPDATE T066A
                                    SET T066A_Flag_Ativo = '{$flagAtivo}'
                                  WHERE T066A_T066_Id = '{$T066_Id}'");
                }

                continue;
            }

            if (mysql_num_rows($T066A) <= 0) {
                mysql_query("INSERT INTO T066A (T066A_T066_Id, T066A_Flag_Ativo)
                                  VALUES ('{$T066_Id}', '{$flagAtivo}')");
            }
        }
    }
}





















