CREATE TABLE IF NOT EXISTS C031A (
    C031A_Id int unsigned NOT NULL AUTO_INCREMENT,
    C031A_C004_Id int unsigned NOT NULL,
    C031A_Campo varchar(100) NOT NULL,
    C031A_Valor text,
    PRIMARY KEY (C031A_Id),
    KEY IDX_C031A_C004_Campo (C031A_C004_Id, C031A_Campo)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Configuracoes API de frete por empresa';

INSERT INTO C031A (C031A_C004_Id, C031A_Campo, C031A_Valor)
SELECT 1, 'freteApiUrl', 'https://TODO-url-da-api-de-frete/calcular'
 WHERE NOT EXISTS (
    SELECT 1 FROM C031A WHERE C031A_C004_Id = 1 AND C031A_Campo = 'freteApiUrl'
);

INSERT INTO C031A (C031A_C004_Id, C031A_Campo, C031A_Valor)
SELECT 1, 'freteApiToken', 'TODO-token-da-api-de-frete'
 WHERE NOT EXISTS (
    SELECT 1 FROM C031A WHERE C031A_C004_Id = 1 AND C031A_Campo = 'freteApiToken'
);
