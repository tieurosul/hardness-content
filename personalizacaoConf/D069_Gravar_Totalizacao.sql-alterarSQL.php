CREATE PROCEDURE `D069_Gravar_Totalizacao`(xD069_Id int(11), xD069_C007_Id_Atualizacao_Custo int(11))
BEGIN
        DECLARE xD070_Id int;
        DECLARE xD009_Id int;
        DECLARE xPercentual_Desconto DECIMAL(20,10) DEFAULT 0;
        DECLARE xPercentual_Desconto_Bruto DECIMAL(20,10) DEFAULT 0;
        DECLARE xMultiplicador_Despesa DECIMAL(20,10) DEFAULT 0;
        DECLARE xMultiplicador_Despesa_Recebida DECIMAL(20,10) DEFAULT 0;
        DECLARE xMultiplicador_Despesas_Acessorias DECIMAL(20,10) DEFAULT 0;
        DECLARE xMultiplicador_Despesas_Acessorias_Recebida DECIMAL(20,10) DEFAULT 0;
        DECLARE xMultiplicador_Frete DECIMAL(20,10) DEFAULT 0;
        DECLARE xMultiplicador_Frete_Recebida DECIMAL(20,10) DEFAULT 0;
        DECLARE xFlag_Revenda_Consumidor VARCHAR(1);
        DECLARE xFlag_Devolucao VARCHAR(1);
        DECLARE xFlag_Tipo_Devolucao VARCHAR(10);
        DECLARE xValor_Total_Frete DECIMAL(19,2) DEFAULT 0;
        DECLARE xValor_Total_Frete_Recebida DECIMAL(19,2) DEFAULT 0;
        DECLARE xValor_Total_Despesas_Acessorias DECIMAL(10,2) DEFAULT 0;
        DECLARE xValor_Total_Despesas_Acessorias_Recebida DECIMAL(10,2) DEFAULT 0;
        DECLARE xValor_Total_Agregados DECIMAL(10,2) DEFAULT 0;
        DECLARE xValor_Total_Desconto DECIMAL(10,2) DEFAULT 0;
        DECLARE xValor_Total_Desconto_Recebida DECIMAL(10,2) DEFAULT 0;
        DECLARE xFlag_ST VARCHAR(3);
        DECLARE xFlag_Venda_Compra_Outros VARCHAR(1);
        DECLARE xAliquota_ICMS_1 DECIMAL(5,2);
        DECLARE xD006_Flag_Somar_IPI_Base_ICMS ENUM('S','N') DEFAULT 'N';
        DECLARE xD006_Flag_Somar_Despesas_Base_IPI ENUM('S','N') DEFAULT 'N';
        DECLARE xD006_Flag_Somar_Despesas_Base_ST ENUM('S','N') DEFAULT 'N';
        DECLARE xD006_Flag_Isento_PIS ENUM('S','N') DEFAULT 'N';
        DECLARE xD006_Situacao_Tributaria_PIS VARCHAR(2) default '01';
        DECLARE xD006_Flag_Isento_COFINS ENUM('S','N') DEFAULT 'N';
        DECLARE xD006_Situacao_Tributaria_COFINS VARCHAR(2) default '01';
        DECLARE xD059_Codigo_ST varchar(3);
        DECLARE xD049_Flag_Nacional_Importado varchar(1);
        DECLARE xD006_Flag_Ajustar_MVA varchar(1);
        DECLARE xD069_Data_Atualizacao_Custo date;
        DECLARE xD009_Data_Atualizacao_Custo_Fornecedor date;
        DECLARE xD006_Flag_Forma_Calculo_COFINS varchar(1) default 'V';
        DECLARE xD006_Flag_Forma_Calculo_PIS varchar(1) default 'V';
        DECLARE xAliquota_ICMS_Interna DECIMAL(5,2);
        DECLARE xValorDIFAL DECIMAL(15,5) DEFAULT '0.00000';
        DECLARE xAliquotaDIFAL DECIMAL(5,2);
        DECLARE xD069_Valor_Custo_Unitario DECIMAL(20,10);
        DECLARE xD069_Valor_Base_ICMS DECIMAL(10,2);
        DECLARE xD006_Flag_Somar_DIFAL_Custo VARCHAR(1);

        SELECT       D069_D070_Id,
                     D006_Flag_Venda_Compra_Outros,
                     D049_Flag_Nacional_Importado,
                     D006_Flag_Somar_IPI_Base_ICMS,
                     D006_Flag_Somar_Despesas_Base_IPI,
                     D006_Flag_Somar_Despesas_Base_ST,
                     D006_Flag_Forma_Calculo_COFINS,
                     D006_Flag_Forma_Calculo_PIS,
                     D069_Percentual_Desconto_Bruto/100,
                     D006_Flag_Ajustar_MVA,
                     D069_Data_Atualizacao_Custo,
                     D009_Id,
                     D009_Data_Atualizacao_Custo_Fornecedor,
                     D069_ICMS_Inter_Estadual,
                     D006_Flag_Somar_DIFAL_Custo
            INTO xD070_Id,
                     xFlag_Venda_Compra_Outros,
                     xD049_Flag_Nacional_Importado,
                     xD006_Flag_Somar_IPI_Base_ICMS,
                     xD006_Flag_Somar_Despesas_Base_IPI,
                     xD006_Flag_Somar_Despesas_Base_ST,
                     xD006_Flag_Forma_Calculo_COFINS,
                     xD006_Flag_Forma_Calculo_PIS,
                     xPercentual_Desconto_Bruto,
                     xD006_Flag_Ajustar_MVA,
                     xD069_Data_Atualizacao_Custo,
                     xD009_Id,
                     xD009_Data_Atualizacao_Custo_Fornecedor,
                     xAliquota_ICMS_Interna,
                     xD006_Flag_Somar_DIFAL_Custo
            FROM D069
            LEFT JOIN D070 ON D070_Id=D069_D070_Id
            LEFT JOIN D006 ON D006_Id=D069_D006_Id
            LEFT JOIN D009 ON D009_Id=D069_D009_Id
            LEFT JOIN D049 ON D049_Id=D009_D049_Id
            LEFT JOIN D024 ON D024_Id=D070_D024_Id  
         WHERE D069_Id=xD069_Id
         LIMIT 1;

        UPDATE D069 
        LEFT JOIN D009 ON D009_Id=D069_D009_Id
        LEFT JOIN D006 ON D006_Id=D069_D006_Id
        LEFT JOIN D070 ON D070_Id=D069_D070_Id
                                set     D069_Valor_Preco_Unitario = D069_Valor_Preco_Original - (D069_Valor_Preco_Original * (ifnull(D069_Percentual_Desconto,0)/100)),
                                        D069_Valor_Preco_Sem_Desconto_Unitario = D069_Valor_Preco_Unitario,
                                        D069_Quantidade = 1,
                                        D069_Valor_Total_Preco = D069_Valor_Preco_Unitario*D069_Quantidade,
                                        D069_Valor_Total_Preco_Sem_Desconto = D069_Valor_Preco_Sem_Desconto_Unitario * D069_Quantidade,
                                        D069_Peso_Total = D069_Peso_Unitario*D069_Quantidade,
                                        D069_Peso_Bruto_Total = D069_Peso_Bruto*D069_Quantidade,
                                        D069_Valor_Total_Despesas=(D069_Valor_Total_Preco*xMultiplicador_Despesa)+xValor_Total_Agregados,
                                        D069_Valor_Despesas_Unitario=D069_Valor_Total_Despesas/D069_Quantidade,
                                        D069_Valor_Total_Agregados=xValor_Total_Agregados,
                                        D069_Valor_Base_IPI=If(D069_Aliquota_IPI>0,(D069_Valor_Total_Preco+if(xD006_Flag_Somar_Despesas_Base_IPI='S',D069_Valor_Total_Frete+D069_Valor_Total_Despesas_Acessorias,0))*If(D069_Percentual_Reducao_IPI>0,(D069_Percentual_Reducao_IPI/100),1),0),
                                        D069_Valor_IPI=D069_Valor_Base_IPI*(D069_Aliquota_IPI/100),
                                        D069_Valor_Total_Custo_Final=(D069_Valor_Total_Preco+if(xD006_Flag_Somar_Despesas_Base_ST='S',D069_Valor_Total_Despesas,0)+if(xD006_Flag_Somar_IPI_Base_ICMS='S',D069_Valor_IPI,0)),
                                        D069_Valor_Custo_Final_Unitario=D069_Valor_Total_Custo_Final/D069_Quantidade,
                                        D069_Valor_Base_ICMS=If(D069_Aliquota_ICMS>0,D069_Valor_Total_Custo_Final*if(D069_Percentual_Reducao_ICMS>0,((100-D069_Percentual_Reducao_ICMS)/100),1),0),
                                        D069_Valor_ICMS=D069_Valor_Base_ICMS*(D069_Aliquota_ICMS/100),                                        
                                        D069_ST_VA_Ajustada = IF(xD006_Flag_Ajustar_MVA = 'S', ( ( (1+(D069_ST_VA/100)) *   ( ( 1 - ( IF(D069_Percentual_Reducao_ICMS > 0, (((100-D069_Percentual_Reducao_ICMS)/100)*D069_ICMS_Inter_Estadual), D069_ICMS_Inter_Estadual) / 100 ) ) / ( 1 - ( D069_ST_ICMS_Interno / 100 ) ) ) ) - 1 ) * 100, D069_ST_VA),
                                        D069_Valor_Base_ICMS_Substituicao=IF(D069_Flag_ST='S',(D069_Valor_Total_Preco+if(xD006_Flag_Somar_Despesas_Base_ST='S',D069_Valor_Total_Despesas,0)+D069_Valor_IPI)*((D069_ST_VA_Ajustada/100)+1),0),
                                        D069_Valor_ICMS_Substituicao=(D069_Valor_Base_ICMS_Substituicao*(D069_ST_ICMS_Interno/100)),
                                        D069_Valor_ICMS_Substituicao_Retencao=((D069_Valor_Base_ICMS_Substituicao*if(D069_Percentual_Reducao_ICMS_ST>0,((100-D069_Percentual_Reducao_ICMS_ST)/100),1))*(D069_ST_ICMS_Interno/100))-((D069_Valor_Total_Custo_Final*if(D069_Percentual_Reducao_ICMS>0,((100-D069_Percentual_Reducao_ICMS)/100),1))*(D069_ICMS_Inter_Estadual/100)),
                                        D069_Valor_ICMS_Substituicao_Retencao=IF(D069_ST_ICMS_Interno>0 and D069_ICMS_Inter_Estadual>0,IF(D069_Valor_ICMS_Substituicao_Retencao<0,0,D069_Valor_ICMS_Substituicao_Retencao),(D069_Valor_Total_Preco+D069_Valor_IPI)*(D069_Percentual_ST/100)),
                                        D069_Valor_Base_PIS=if(D069_Flag_Isento_PIS='S',0,if(xD006_Flag_Forma_Calculo_PIS='L',D069_Valor_Total_Preco-D069_Valor_Total_Custo,D069_Valor_Total_Preco)),
                                        D069_Aliquota_PIS=if(D069_Flag_Isento_PIS='S',0,D069_Aliquota_PIS),
                                        D069_Valor_PIS=if(D069_Aliquota_PIS>0,D069_Valor_Base_PIS*(D069_Aliquota_PIS/100),0),
                                        D069_Valor_Base_COFINS=if(D069_Flag_Isento_COFINS='S',0,if(xD006_Flag_Forma_Calculo_COFINS='L',D069_Valor_Total_Preco-D069_Valor_Total_Custo,D069_Valor_Total_Preco)),
                                        D069_Aliquota_COFINS=if(D069_Flag_Isento_COFINS='S',0,D069_Aliquota_COFINS),
                                        D069_Valor_COFINS=if(D069_Aliquota_COFINS>0,D069_Valor_Base_COFINS*(D069_Aliquota_COFINS/100),0),
                                        D069_Total_Cubagem = IFNULL(D069_Cubagem_Unitaria,0) * IFNULL(D069_Quantidade,0),
                                        D069_Valor_Trib_Transparencia=IFNULL(D069_Valor_Total_Preco,0)*(IFNULL(D069_Percentual_Trib_Transparencia,0)/100),
                                        D069_Valor_Frete=(IFNULL(D069_Valor_Total_Preco,0)+IFNULL(D069_Valor_IPI,0)+IFNULL(D069_Valor_ICMS_Substituicao_Retencao,0)+IFNULL(D069_Valor_Financeiro,0)) * (IFNULL(D069_Percentual_Frete,0)/100),
                                        D069_Aliquota_ICMS_Diferenca_Aliquotas = IF(xD006_Flag_Somar_DIFAL_Custo = 'S',  if(xAliquota_ICMS_Interna - D069_Aliquota_ICMS > 0,xAliquota_ICMS_Interna - D069_Aliquota_ICMS, 0), 0),
                                        D069_Valor_ICMS_Diferenca_Aliquotas = IF(xD006_Flag_Somar_DIFAL_Custo = 'S',  (D069_Aliquota_ICMS_Diferenca_Aliquotas / 100) * D069_Valor_Base_ICMS, 0),
                                        D069_Valor_Total_Custo=D069_Valor_Total_Preco+IFNULL(D069_Valor_IPI,0)+IFNULL(D069_Valor_ICMS_Substituicao_Retencao,0)+IFNULL(D069_Valor_Frete,0)+IFNULL(D069_Valor_Total_Frete,0)+IFNULL(D069_Valor_Financeiro,0)+IFNULL(D069_Valor_ICMS_Diferenca_Aliquotas,0),
                                        D069_Data_Atualizacao_Custo=IF(D069_Valor_Custo_Unitario != D069_Valor_Total_Custo, CURDATE(), D069_Data_Atualizacao_Custo),
                                        D069_C007_Id_Atualizacao_Custo=IF(D069_Valor_Custo_Unitario != D069_Valor_Total_Custo, xD069_C007_Id_Atualizacao_Custo, D069_C007_Id_Atualizacao_Custo),
                                        D069_Valor_Custo_Unitario=D069_Valor_Total_Custo,
                                        D069_Data_Hora_Ultima_Alteracao = CURRENT_TIMESTAMP(), 
                                        D069_C007_Id_Ultima_Alteracao = xD069_C007_Id_Atualizacao_Custo,
                                        D069_Percentual_ST=IF(D070_Flag_Calcula_Percentual_ST='S',D069_Valor_ICMS_Substituicao_Retencao/(D069_Valor_Total_Preco+D069_Valor_IPI)*100,D069_Percentual_ST)
                            WHERE D069_Id=xD069_Id;
        
        IF xD069_Data_Atualizacao_Custo != xD009_Data_Atualizacao_Custo_Fornecedor THEN
           UPDATE D009 SET D009_Data_Atualizacao_Custo_Fornecedor=xD069_Data_Atualizacao_Custo WHERE D009_Id=xD009_Id;
        END IF;
END;



