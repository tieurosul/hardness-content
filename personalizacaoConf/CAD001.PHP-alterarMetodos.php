<?php
namespace hardness;
/**
* Alterar (sobre-escrever) Métodos de uma Classe
*/

class CAD001 extends CAD001_ {
	// defina os métodos para sobreescrever

    public function pesquisaTerceirosByjg($tCep)
    {
        /**
        * WebService ByJG
        * http://www.byjg.com.br/
        * Consulta sem limites.
        * Deve retornar False caso nao tenha encontrado o CEP
        */
        header('Content-Type: text/html; charset=UTF-8');
        $isRetorno = false;

        $username  = "hardness";
        $password  = "hard3871";

        $url = "http://www.byjg.com.br/site/webservice.php/ws/cep?httpmethod=obterlogradouroauth";
        $url .= "&cep=" . urlencode($tCep);
        $url .= "&usuario=" . urlencode($username);
        $url .= "&senha=" . urlencode($password);

        log($url);
        if ($result = file_get_contents($url))
        {
            log("result: ".htmlspecialchars($result));
            $result = utf8_decode($result);
            if (strpos($result,"não encontrado")!==FALSE OR strpos($result,"senha inválidos")!==FALSE)
            {
                return false;
            } 
          
            $aux = explode("|", $result);
            $mByjg = explode(",", $aux[1]);
                    
            for ($i=count($mByjg);$i<5;$i++) $mByjg[] = "";

                $mByjg[0] = trim($mByjg[0]);
                $mByjg[1] = trim($mByjg[1]);             
                $mByjg[2] = trim($mByjg[2]);
                $mByjg[3] = trim($mByjg[3]);
                $mByjg[4] = trim($mByjg[4]);            

                $mDados['Cep']             = $this->removerAcentos($tCep);
                $mDados['Logradouro']      = $this->removerAcentos($mByjg[0]);
                $mDados['Bairro']          = $this->removerAcentos($mByjg[1]);
                $mDados['Cidade']          = $this->removerAcentos($mByjg[2]);
                $mDados['UF']              = $this->removerAcentos($mByjg[3]);
                $mDados['IBGE_Cidade']     = $this->removerAcentos($mByjg[4]);
                $mDados['IBGE_UF']         = substr($mByjg[4],0,2);
                            
            return $mDados;
        }
        else
        {            
            log("result: ".htmlspecialchars($result));
            return $isRetorno;
        }            

        return false;
    }

	    /**
     * Calcular limite
     * 
     * @return bool/String
     */  
    public function calcularLimite()
    {
        mysql_query("SET autocommit=0");
        mysql_query("start transaction"); 
        
        // Total valor faturado e total a receber nos últimos 6 meses
		$limite = mysql_query("SELECT SUM(T002_Valor_Total) AS T002_Valor_Total,
									  SUM(CASE WHEN T002_Data_Recebimento = '0000-00-00' THEN T002_Valor_Saldo ELSE 0 END) AS valor_receber,
									  T002_D024_Id,
                                      D024_Flag_Liberado
								 FROM T002
							LEFT JOIN D024 ON D024_Id = T002_D024_Id  
						        WHERE T002_Data_Emissao BETWEEN CURDATE() - INTERVAL 6 MONTH AND CURDATE()
								  AND D024_Flag_Ativo = 'S'
							 GROUP BY T002_D024_Id");
        
        $cErro = mysql_error();
        if(!empty($cErro)){
            mysql_query("rollback");
            return "ERRO: ".$cErro; 
        }
        
        while($mLimite = mysql_fetch_array($limite)){
            
            $valorLimite = $mLimite['T002_Valor_Total'] - $mLimite['valor_receber'];
			$valorLimite = ($valorLimite > 0 ) ? $valorLimite : 0;

            if($mLimite['D024_Flag_Liberado'] == 'N'){
                $valorLimite = 0;
            }
			
            mysqli_query("UPDATE D024 SET D024_Valor_Limite = '{$valorLimite}' 
								    WHERE D024_Id = '{$mLimite['T002_D024_Id']}'");
            
            // verifica se ocorreu algum erro 
            $cErro = mysql_error();
            if(!empty($cErro)){
                mysql_query("rollback");
                return "ERRO: ".$cErro; 
            }
        }
        
        mysql_query("COMMIT");
        
        return true;
        //return false;
    }
}




