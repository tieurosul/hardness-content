CREATE FUNCTION `TRIB_Valor_ST`(xValor_Base_ICMS_Substituicao decimal(20,2),xPercentual_Reducao_ICMS_ST decimal(5,2), xST_ICMS_Interno decimal(5,2), xValor_Total_Custo_Final decimal(20,5), xPercentual_Reducao_ICMS decimal(5,2), xICMS_Inter_Estadual decimal(5,2), xST_VA decimal(10,2), xUF_Origem varchar(2), xUF_Destino varchar(2), xDestino_Produto varchar(1), xPreco_Pauta_ST decimal(20,10), xModalidade varchar(1)) RETURNS decimal(20,2)
BEGIN
	DECLARE xValorOrigem DECIMAL(20,6);
	DECLARE xValorDestino DECIMAL(20,6);
	DECLARE xValorST DECIMAL(20,2);

    SET xValorOrigem  = ((xValor_Base_ICMS_Substituicao*if(xPercentual_Reducao_ICMS_ST>0,((100-xPercentual_Reducao_ICMS_ST)/100),1))*(xST_ICMS_Interno/100));
    # Personalizado pedido Anderson CHA em 07/05/2021 para alterar xST_ICMS_Inter_Estadual para xST_ICMS_Interno no valor destino
    #SET xValorDestino = ((xValor_Total_Custo_Final*if(xPercentual_Reducao_ICMS>0,((100-xPercentual_Reducao_ICMS)/100),1))*xST_ICMS_Interno/100);
    # Aline CHA 09/02/22 - solicitou alteração que não deve ter redução do ICMS no calculo da ST
    SET xValorDestino = (xValor_Total_Custo_Final*(xICMS_Inter_Estadual/100));
    SET xValorST = xValorOrigem - xValorDestino;

	RETURN IF(xValorST<0,0,xValorST);
END




