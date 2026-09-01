CREATE FUNCTION `D009_Quantidade_Pedido_Por_Locacao`(xD009_Id INT(11), xT066_Id INT(11)) RETURNS DECIMAL(14,3)
BEGIN
     DECLARE xT006_Quantidade DECIMAL(14,3);
     DECLARE xT238_Quantidade_Separacao DECIMAL(14,3);
      SELECT SUM(T006_Quantidade),
             SUM(T238_Quantidade_Separacao)
        INTO xT006_Quantidade,
             xT238_Quantidade_Separacao
        FROM T006 
   LEFT JOIN T005 ON T006_T005_Id=T005_Id
   LEFT JOIN D009 ON D009_Id=T006_D009_Id
   LEFT JOIN D049 ON D049_Id=D009_D049_Id
   LEFT JOIN D006 ON D006_Id=T006_D006_Id
   LEFT JOIN T007 ON T007_T005_Id=T005_Id and T007_Flag_Cancelada!='S'
   LEFT JOIN T005A ON T005A_T005_Id=T005_Id
   LEFT JOIN T238 ON T238_T006_Id = T006_Id AND T238_T066_Id = xT066_Id 
       WHERE T006_D009_Id=xD009_Id
         AND (T006_T066_Id = xT066_Id OR IFNULL(T238_Quantidade_Separacao, 0) > 0)
         AND IFNULL(T006_T075_Id,0) <= 0
         AND (T005_T005_Id_Agrupado<=0 OR T005_T005_Id_Agrupado IS NULL)
         AND IFNULL(T006_C004_Id_Estoque, D009_C004_Id) = D009_C004_Id
         AND ((T006_Flag_Status!='3'
         AND (T005_Flag_Status!='4' OR (T005_Flag_Status='4' AND T005_Flag_Expedicao = '2' AND T007_Id is null))
         AND T005_Flag_Status!='5'
         AND T005_Flag_Status!='7'
         AND T005_Flag_Status!='11'
         AND T005_Flag_Status!='8'
         AND T005_Flag_Status!='26')
          OR T005_Flag_Status='')
         AND D006_Flag_Estoque='D'  
         AND IFNULL(T005A_Flag_Tipo,1) !='2';     
   IF xT006_Quantidade IS NULL THEN
      SET xT006_Quantidade = 0;
   END IF;
   IF xT238_Quantidade_Separacao > 0 THEN
      SET xT006_Quantidade = xT238_Quantidade_Separacao;
   END IF;
   RETURN xT006_Quantidade;
END;
