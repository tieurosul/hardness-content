CREATE PROCEDURE `T055_Gravar_Totalizacao`(xT055_Id int(11))
BEGIN
    DECLARE xT075_Valor_SISCOMEX DECIMAL(16,6);
    DECLARE xT075_Valor_Imposto_Importacao DECIMAL(16,6);
    DECLARE xT075_Valor_IPI DECIMAL(16,6);
    DECLARE xT075_Valor_PIS DECIMAL(16,6);
    DECLARE xT075_Valor_COFINS DECIMAL(16,6);
    DECLARE xT075_Valor_ICMS DECIMAL(16,6);
    DECLARE xT075_Valor_THC DECIMAL(16,6);
    DECLARE xT075_Valor_Frete_Incluso DECIMAL(16,6);
    DECLARE xT075_Valor_Frete DECIMAL(16,6);
    DECLARE xT055_Valor_Frete DECIMAL(16,6);
    DECLARE xT075_Valor_Seguro DECIMAL(16,6);
    DECLARE xT075_Valor_Despesa DECIMAL(16,6);
    DECLARE xT075_Valor_Total_Custo DECIMAL(16,6);
    DECLARE xT075_Valor_VMLE DECIMAL(16,6);
    DECLARE xT055_Valor_Total_Custo DECIMAL(16,6);
    DECLARE xT055_Valor_Total_Custo_CC DECIMAL(16,6);
    DECLARE xPeso_Total DECIMAL(16,6);
    DECLARE xPeso_Item DECIMAL(16,6);
    DECLARE xT075_Id INT(10);
    DECLARE xAliquota_ICMS DECIMAL(5,2);
    DECLARE xAliquota_ICMS_Normal DECIMAL(5,2);
    DECLARE xAliquota_ICMS_Presumido DECIMAL(5,2);
    DECLARE xT075_Aliquota_ICMS_PIS_COFINS DECIMAL(5,2);
    DECLARE xValor_Base_IPI DECIMAL(19,6);
    DECLARE xValor_Base_II DECIMAL(19,6);
    DECLARE xValor_Base_PIS_COFINS DECIMAL(19,6);
    DECLARE xValor_Base_ICMS DECIMAL(19,6);
    DECLARE xPercentual_Reducao_ICMS DECIMAL(19,4);
    DECLARE xPercentual_Reducao_ICMS_Presumido DECIMAL(19,4);
    DECLARE xValor_Despesas_Acessorias DECIMAL(19,4) DEFAULT 0;
    DECLARE xT075_Cotacao_ME DECIMAL(10,4);
    DECLARE xT075_Cotacao_CC DECIMAL(10,4);
    DECLARE xFlag_Frete VARCHAR(1);
    DECLARE xT075_Valor_AFRMM DECIMAL(16,6);
    DECLARE xT075_Somar_PIS_Custo CHAR(1);
    DECLARE xT075_Somar_COFINS_Custo CHAR(1);
    DECLARE xT075_Somar_Seguro_Custo CHAR(1);
    DECLARE xT075_Somar_AFRMM_Custo CHAR(1);
    DECLARE xT075_Somar_ICMS_Pagar_Custo CHAR(1);
    DECLARE xT075_Somar_IPI_Custo CHAR(1);
    
    SELECT T055_T075_Id,
           T055_Valor_Custo_Unitario * T055_Quantidade ,
           T055_Peso_Kg_Liquido_Total,
           T055_Flag_Frete
      INTO xT075_Id,
           xT055_Valor_Total_Custo,
           xPeso_Item,
           xFlag_Frete
      FROM T055
     WHERE T055_Id=xT055_Id;

    SELECT sum(T055_Peso_Kg_Liquido_Total)
      INTO xPeso_Total
      FROM T055
     WHERE T055_T075_Id=xT075_Id
       AND T055_Flag_Frete = 'S';
       
    SELECT IF(SUM(T168_Valor)=0 OR SUM(T168_Valor) IS NULL,1,SUM(T168_Valor)) 
      INTO xT075_Valor_Despesa
      FROM T168
     WHERE T168_T075_Id=xT075_Id;

    SELECT (T075_Aliquota_ICMS/100),
           (100-T075_Aliquota_ICMS),
           (T075_Aliquota_ICMS_Presumido/100),
           if(T075_Percentual_Reducao_ICMS>0,(T075_Percentual_Reducao_ICMS/100),1),
           if(ifnull(T075_Percentual_Reducao_ICMS_Presumido,0)>0,(T075_Percentual_Reducao_ICMS_Presumido/100),0),
           (T075_Aliquota_ICMS_PIS_COFINS/100),
           T075_Valor_SISCOMEX,
           T075_Valor_Imposto_Importacao,
           T075_Valor_IPI,
           T075_Valor_PIS,
           T075_Valor_Cofins,
           T075_Valor_ICMS,
           T075_Valor_THC,
           T075_Valor_Frete_Incluso,
           T075_Valor_Frete,
           T075_Valor_Seguro,
           T075_Valor_AFRMM,
           T075_Valor_VMLE,
           T075_Cotacao_ME,
           T075_Somar_PIS_Custo,
           T075_Somar_COFINS_Custo,
           T075_Somar_Seguro_Custo,
           T075_Somar_AFRMM_Custo,
           T075_Somar_ICMS_Pagar_Custo,
           T075_Somar_IPI_Custo,
           IFNULL(T075_Cotacao_CC,0)
      INTO xAliquota_ICMS,
           xAliquota_ICMS_Normal,
           xAliquota_ICMS_Presumido,
           xPercentual_Reducao_ICMS,
           xPercentual_Reducao_ICMS_Presumido,
           xT075_Aliquota_ICMS_PIS_COFINS,
           xT075_Valor_SISCOMEX,
           xT075_Valor_Imposto_Importacao,
           xT075_Valor_IPI,
           xT075_Valor_PIS,
           xT075_Valor_Cofins,
           xT075_Valor_ICMS,
           xT075_Valor_THC,
           xT075_Valor_Frete_Incluso,
           xT075_Valor_Frete,
           xT075_Valor_Seguro,
           xT075_Valor_AFRMM,
           xT075_Valor_VMLE,
           xT075_Cotacao_ME,
           xT075_Somar_PIS_Custo,
           xT075_Somar_COFINS_Custo,
           xT075_Somar_Seguro_Custo,
           xT075_Somar_AFRMM_Custo,
           xT075_Somar_ICMS_Pagar_Custo,
           xT075_Somar_IPI_Custo,
           xT075_Cotacao_CC
      FROM T075
      WHERE T075_Id=xT075_Id;

      SELECT
        (T055_Aliquota_ICMS/100),
        (T055_Aliquota_ICMS_Presumido/100),
        (T055_Aliquota_ICMS_PIS_COFINS/100)
      INTO
        xAliquota_ICMS,
        xAliquota_ICMS_Presumido,
        xT075_Aliquota_ICMS_PIS_COFINS
      FROM T055
      WHERE T055_Id=xT055_Id;
      
      SET xT055_Valor_Total_Custo_CC = xT055_Valor_Total_Custo * IF(xT075_Cotacao_CC>0,xT075_Cotacao_CC,xT075_Cotacao_ME);
      SET xT055_Valor_Total_Custo = xT055_Valor_Total_Custo * xT075_Cotacao_ME;
      SET xT075_Valor_VMLE = xT075_Valor_VMLE * xT075_Cotacao_ME;
      SET xT075_Valor_Frete = xT075_Valor_Frete * xT075_Cotacao_ME;
      
      IF xFlag_Frete = 'S' THEN
         SET xT055_Valor_Frete      = (xT075_Valor_Frete)*(xPeso_Item/xPeso_Total);
      ELSE
         SET xT055_Valor_Frete      = 0;
      END IF;
      
/*SET xValor_Base_II         = ((xT075_Valor_Seguro+xT075_Valor_THC)*(xT055_Valor_Total_Custo/xT075_Valor_VMLE))+xT055_Valor_Frete;*/
/*SET xValor_Base_IPI        = ((xT075_Valor_Seguro+xT075_Valor_THC)*(xT055_Valor_Total_Custo/xT075_Valor_VMLE))+xT055_Valor_Frete;*/
/*SET xValor_Base_PIS_COFINS = ((xT075_Valor_Seguro+xT075_Valor_THC)*(xT055_Valor_Total_Custo/xT075_Valor_VMLE))+xT055_Valor_Frete;*/
/*SET xValor_Base_ICMS       = ((xT075_Valor_Seguro+xT075_Valor_THC+xT075_Valor_SISCOMEX)*(xT055_Valor_Total_Custo/xT075_Valor_VMLE))+xT055_Valor_Frete;*/
/*SET xT075_Valor_THC        = (xT075_Valor_THC)*(xT055_Valor_Total_Custo/xT075_Valor_VMLE);*/

	  SET xValor_Base_II         = ((xT075_Valor_Seguro+xT075_Valor_THC)*(xPeso_Item/xPeso_Total))+xT055_Valor_Frete;
	  SET xValor_Base_IPI        = ((xT075_Valor_Seguro+xT075_Valor_THC)*(xPeso_Item/xPeso_Total))+xT055_Valor_Frete;
	  SET xValor_Base_PIS_COFINS = ((xT075_Valor_Seguro+xT075_Valor_THC)*(xPeso_Item/xPeso_Total))+xT055_Valor_Frete;
	  SET xValor_Base_ICMS       = ((xT075_Valor_Seguro+xT075_Valor_THC+xT075_Valor_SISCOMEX)*(xPeso_Item/xPeso_Total))+xT055_Valor_Frete;
	  SET xT075_Valor_THC        = (xT075_Valor_THC)*(xPeso_Item/xPeso_Total);

      SET xT075_Valor_SISCOMEX   = (xT075_Valor_SISCOMEX)*(xT055_Valor_Total_Custo/xT075_Valor_VMLE);
      SET xT075_Valor_Seguro     = (xT075_Valor_Seguro)*(xT055_Valor_Total_Custo/xT075_Valor_VMLE);
      SET xT075_Valor_Despesa    = (xT075_Valor_Despesa)*(xT055_Valor_Total_Custo/xT075_Valor_VMLE);
      SET xT075_Valor_AFRMM      = (xT075_Valor_AFRMM)*(xT055_Valor_Total_Custo/xT075_Valor_VMLE);
      
        UPDATE T055 SET T055_Valor_Total_Custo                   = T055_Valor_Custo_Unitario * T055_Quantidade,
                        T055_Valor_Total_Despesas_Aduaneira      = xT075_Valor_SISCOMEX,
                        T055_Valor_Despesas_Aduaneira            = T055_Valor_Total_Despesas_Aduaneira / T055_Quantidade,
                        T055_Valor_Total_Despesas                = xT075_Valor_Despesa,
                        T055_Valor_Despesas                      = T055_Valor_Total_Despesas / T055_Quantidade,
                        T055_Valor_Total_Frete                   = xT055_Valor_Frete,
                        T055_Valor_Frete                         = T055_Valor_Total_Frete  / T055_Quantidade,
                        T055_Valor_Total_Aduaneira               = xT055_Valor_Total_Custo+T055_Valor_Total_Frete+T055_Valor_Total_Despesas_Aduaneira,
                        T055_Valor_Aduaneira                     = xT075_Valor_THC,
                        T055_Valor_Total_AFRMM                   = xT075_Valor_AFRMM,
                        T055_Valor_AFRMM                         = T055_Valor_Total_AFRMM / T055_Quantidade,
                        T055_Valor_Base_Imposto_Importacao       = xT055_Valor_Total_Custo+xValor_Base_II,
                        T055_Valor_Imposto_Importacao            = T055_Valor_Base_Imposto_Importacao*(T055_Aliquota_Imposto_Importacao/100),
                        T055_Valor_Base_IPI                      = xT055_Valor_Total_Custo+xValor_Base_IPI+T055_Valor_Imposto_Importacao,
                        T055_Valor_IPI                           = T055_Valor_Base_IPI * ( T055_Aliquota_IPI / 100 ),
                        T055_Multiplicador_X                     = ( 1 + xT075_Aliquota_ICMS_PIS_COFINS * ( (T055_Aliquota_Imposto_Importacao/100) + (T055_Aliquota_IPI/100) * ( 1 + (T055_Aliquota_Imposto_Importacao/100) ) ) ) / ( ( 1 - (T055_Aliquota_PIS/100) - (T055_Aliquota_COFINS/100) ) * ( 1 - xT075_Aliquota_ICMS_PIS_COFINS ) ),
                        T055_Valor_Base_PIS                      = xT055_Valor_Total_Custo + xValor_Base_PIS_COFINS,
                        T055_Valor_PIS                           = ( T055_Aliquota_PIS/100 ) *  T055_Valor_Base_PIS,
                        T055_Valor_Base_COFINS                   = xT055_Valor_Total_Custo + xValor_Base_PIS_COFINS,
                        T055_Valor_COFINS                        = ( T055_Aliquota_COFINS/100 ) *  T055_Valor_Base_COFINS,
                        T055_Valor_Base_ICMS_Sem_Reducao         = xT055_Valor_Total_Custo + xValor_Base_ICMS + T055_Valor_PIS + T055_Valor_COFINS + T055_Valor_IPI + T055_Valor_Imposto_Importacao + T055_Valor_Total_AFRMM,
                        T055_Valor_Base_ICMS_Normal              = round( ( T055_Valor_Base_ICMS_Sem_Reducao * 100 ) / xAliquota_ICMS_Normal , 2),                                                                                                                           
                        T055_Valor_Base_ICMS_Presumido           = round( ( ( T055_Valor_Base_ICMS_Sem_Reducao * 100 ) / xAliquota_ICMS_Normal ) * IF(xPercentual_Reducao_ICMS_Presumido>0, xPercentual_Reducao_ICMS_Presumido, 1) , 2 ),
                        T055_Valor_ICMS                          = (T055_Valor_Base_ICMS_Normal * xAliquota_ICMS) - ((T055_Valor_Base_ICMS_Normal * xAliquota_ICMS) * xPercentual_Reducao_ICMS_Presumido),
                        T055_Valor_ICMS_Presumido                = T055_Valor_Base_ICMS_Presumido * xAliquota_ICMS_Presumido,
                        T055_Valor_ICMS_Pagar                    = T055_Valor_ICMS - T055_Valor_ICMS_Presumido,
                        T055_Peso_Kg_Liquido_Total               = T055_Peso_Kg_Liquido_Unitario * T055_Quantidade,
                        T055_Peso_Kg_Liquido_Total_Fatura        = T055_Peso_Kg_Liquido_Unitario_Fatura * T055_Quantidade,
                        T055_Peso_Lb_Liquido_Unitario            = T055_Peso_Kg_Liquido_Unitario * 2.2,
                        T055_Peso_Lb_Liquido_Total               = T055_Peso_Lb_Liquido_Unitario * T055_Quantidade,
                        T055_Aliquota_Extra                      = 0.00,
                        T055_Valor_Total_ICMS                    = T055_Valor_ICMS,
                        T055_Valor_Total_ICMS_Presumido          = T055_Valor_ICMS_Presumido,
                        T055_Valor_Total_ICMS_Pagar              = T055_Valor_ICMS_Pagar,
                        T055_Valor_Total_IPI                     = T055_Valor_IPI,
                        T055_Valor_Total_Imposto_Importacao      = T055_Valor_Imposto_Importacao,
                        T055_Valor_Total_COFINS                  = T055_Valor_COFINS,
                        T055_Valor_Total_PIS                     = T055_Valor_PIS,
                        T055_Valor_Total_Extra                   = T055_Valor_Extra,
                        T055_Valor_Total_Base_IPI                = T055_Valor_Base_IPI,
                        T055_Valor_Total_Base_ICMS_Sem_Reducao   = T055_Valor_Base_ICMS_Sem_Reducao,
                        T055_Valor_Total_Base_ICMS_Normal        = T055_Valor_Base_ICMS_Normal,
                        T055_Valor_Total_Base_ICMS_Presumido     = T055_Valor_Base_ICMS_Presumido,
                        T055_Valor_Total_Base_COFINS             = T055_Valor_Base_COFINS,
                        T055_Valor_Total_Base_PIS                = T055_Valor_Base_PIS,
                        T055_Valor_Total_Base_Imposto_Importacao = T055_Valor_Base_Imposto_Importacao,
                        T055_Valor_Total_Preco_Nota_Fiscal       = xT055_Valor_Total_Custo + T055_Valor_Imposto_Importacao + T055_Valor_PIS + T055_Valor_COFINS,
                        T055_Valor_Preco_Unitario_Nota_Fiscal    = T055_Valor_Total_Preco_Nota_Fiscal / T055_Quantidade,
                        T055_Valor_Total_Despesas_Acessorias_Nota_Fiscal = xT075_Valor_SISCOMEX + xT075_Valor_THC + xT075_Valor_Seguro + xT075_Valor_Despesa + xT075_Valor_AFRMM,
						#T055_Valor_Imposto_Importacao            = (( T055_Percentual_Acrescimo_Impostos/100 ) *  T055_Valor_Imposto_Importacao) + T055_Valor_Imposto_Importacao,
                        #T055_Valor_IPI                           = (( T055_Percentual_Acrescimo_Impostos/100 ) *  T055_Valor_IPI) + T055_Valor_IPI,
                        #T055_Valor_PIS                           = (( T055_Percentual_Acrescimo_Impostos/100 ) *  T055_Valor_PIS) + T055_Valor_PIS,
                        #T055_Valor_COFINS                        = (( T055_Percentual_Acrescimo_Impostos/100 ) *  T055_Valor_COFINS) + T055_Valor_COFINS,
                        T055_Valor_Total_Custo_Final             = IF(T055_Cotacao_CC>0,(T055_Valor_Custo_Unitario * T055_Quantidade)*T055_Cotacao_CC,xT055_Valor_Total_Custo_CC) +
                                                                   T055_Valor_Total_Despesas_Aduaneira +
                                                                   T055_Valor_Imposto_Importacao +
                                                                   T055_Valor_Aduaneira +
                                                                   T055_Valor_Total_Despesas +
                                                                   T055_Valor_Total_Frete +
                                                                   IF(xT075_Somar_PIS_Custo = 'S', T055_Valor_PIS, 0) +
                                                                   IF(xT075_Somar_COFINS_Custo = 'S', T055_Valor_COFINS, 0) +
                                                                   IF(xT075_Somar_Seguro_Custo = 'S', xT075_Valor_Seguro, 0) +
                                                                   IF(xT075_Somar_AFRMM_Custo = 'S', T055_Valor_Total_AFRMM, 0) +
                                                                   IF(xT075_Somar_ICMS_Pagar_Custo = 'S', T055_Valor_ICMS_Pagar, 0) +
                                                                   IF(xT075_Somar_IPI_Custo = 'S', T055_Valor_IPI, 0), 
                        T055_Valor_Custo_Final_Unitario          = T055_Valor_Total_Custo_Final / T055_Quantidade
                  WHERE T055_Id=xT055_Id;
END;












