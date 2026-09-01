<?php
namespace hardness;
/**
* Alterar (sobre-escrever) Métodos de uma Classe
*/

class VEN012 extends VEN012_ {
	// defina os métodos para sobreescrever

	public function insereCorreios($T005_Id,$T007_Id) {
        global $g;

        $sqlT005 = mysql_query("SELECT T005_C004_Id,T005_D024_Id,T005_D148_Id_Entrega,T005_D036_Id,T005_Valor_Total_Produtos FROM T005 WHERE T005_Id='{$T005_Id}'");
        $mT005 = mysql_fetch_array($sqlT005);

        $valorDeclarado = 'N';
        if($mT005['T005_Valor_Total_Produtos']>=str_replace(",", ".", $g['C031']['ValorDeclaradoCorreios'])){
            $valorDeclarado = 'S';
            log("valor declarado");
        }
        
        $sql =  mysql_query("insert into T268 
                    (T268_C004_Id,
                    T268_T007_Id,
                    T268_D024_Id,
                    T268_D148_Id,
                    T268_D036_Id,
                    T268_Servico_Adicional_VD,
                    T268_Valor_Declarado) 
                values (
                    '{$mT005['T005_C004_Id']}',
                    '{$T007_Id}',
                    '{$mT005['T005_D024_Id']}',
                    '{$mT005['T005_D148_Id_Entrega']}',
                    '{$mT005['T005_D036_Id']}',
                    '{$valorDeclarado}',
                    '{$mT005['T005_Valor_Total_Produtos']}')");
        $erro = mysql_error();
        if(!empty($erro)){
            log("Erro insert t268:".$erro);
        }

        // Felipe Kadanos - EUROSUL FORNECEDO - 46316 - 28/01/2026
        // Melhoria a pedido do Miguel para realizar a integração com os Correios
        $sql = "SELECT T007_Numero_Nota_Fiscal, T007_Transportadora_CNPJ FROM T007 WHERE T007_Id = '{$T007_Id}'";
        $row = mysql_fetch_assoc(mysql_query($sql));

        if(in_array($row['T007_Transportadora_CNPJ'], ["34028316000103", "34.028.316/0001-03"])){
            log("<b>--- Entrou integração correios NF ---</b>");
            // http://147.79.82.140:5000/swagger/index.html
            // http://147.79.82.140:5000/correios/{$row['T007_Numero_Nota_Fiscal']}/pedidos

            $url = "http://147.79.82.140:5000/correios/{$row['T007_Numero_Nota_Fiscal']}/pedidos";
            log("URL: " . $url);

            $payload = [
                "pedidos" => $row['T007_Numero_Nota_Fiscal']
            ];
            
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ]);

            $response = curl_exec($ch);
            log("Response: " . $response);

            if ($response === false) {
                log("Erro ao conectar na API dos Correios: " . curl_error($ch));
                curl_close($ch);
                return false;
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode < 200 || $httpCode >= 300) {
                log("API dos Correios retornou HTTP {$httpCode}");
                return false;
            }

            $pedCorreio = json_decode($response, true);

            if (!is_array($pedCorreio) || !isset($pedCorreio['NumeroPedidoCorreios'])) {
                log("Resposta inesperada da API dos Correios");
                
                return false;
            }

            log("Pedido Correios: " . $pedCorreio['NumeroPedidoCorreios']);
            $updt = "UPDATE T007A SET T007A_Ordem_Compra = '{$pedCorreio['NumeroPedidoCorreios']}' WHERE T007A_T007_Id = '{$T007_Id}'";
            mysql_query($updt);

            if ($erro = mysql_error()) {
                log("Erro ao atualizar T007A com Numero Pedido Correios: " . $erro);
                return false;
            }
        }
       
       return true;
    }

}
