CREATE TABLE IF NOT EXISTS P007 (
    P007_Id int unsigned NOT NULL AUTO_INCREMENT,
    P007_T003_Id int unsigned NOT NULL,
    P007_C004_Id int unsigned NOT NULL,
    P007_Opcao_Externa_Id varchar(50) NOT NULL,
    P007_Valor_Frete decimal(20,2) NOT NULL DEFAULT '0.00',
    P007_Prazo_Entrega varchar(20) DEFAULT NULL,
    P007_Transportadora varchar(255) DEFAULT NULL,
    P007_Quem_Paga varchar(10) DEFAULT NULL,
    P007_Flag_Selecionada char(1) NOT NULL DEFAULT 'N',
    P007_Data_Calculo datetime NOT NULL,
    P007_Data_Selecao datetime DEFAULT NULL,
    P007_C007_Id int unsigned DEFAULT NULL,
    PRIMARY KEY (P007_Id),
    KEY IDX_P007_T003_Id (P007_T003_Id),
    KEY IDX_P007_T003_Selecionada (P007_T003_Id, P007_Flag_Selecionada)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Opcoes de frete vinculadas ao orcamento (ven001)';
