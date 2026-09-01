-- Register C031 keys for external freight quote API (VEN001).
INSERT INTO C031 (C031_C004_Id, C031_Campo, C031_Valor_Padrao, C031_Valor, C031_Observacao)
SELECT C004_Id, 'apiFreteUrl', '', '', 'URL da API externa de cotação de frete (orçamento VEN001).'
FROM C004
WHERE NOT EXISTS (
    SELECT 1 FROM C031 x WHERE x.C031_C004_Id = C004.C004_Id AND x.C031_Campo = 'apiFreteUrl'
);

INSERT INTO C031 (C031_C004_Id, C031_Campo, C031_Valor_Padrao, C031_Valor, C031_Observacao)
SELECT C004_Id, 'apiFreteToken', '', '', 'Token/chave de autenticação da API de frete (Bearer ou conforme contrato).'
FROM C004
WHERE NOT EXISTS (
    SELECT 1 FROM C031 x WHERE x.C031_C004_Id = C004.C004_Id AND x.C031_Campo = 'apiFreteToken'
);

INSERT INTO C031 (C031_C004_Id, C031_Campo, C031_Valor_Padrao, C031_Valor, C031_Observacao)
SELECT C004_Id, 'apiFreteTimeout', '15', '15', 'Timeout em segundos para chamadas à API de frete.'
FROM C004
WHERE NOT EXISTS (
    SELECT 1 FROM C031 x WHERE x.C031_C004_Id = C004.C004_Id AND x.C031_Campo = 'apiFreteTimeout'
);

INSERT INTO C031 (C031_C004_Id, C031_Campo, C031_Valor_Padrao, C031_Valor, C031_Observacao)
SELECT C004_Id, 'apiFreteMapaD022', '{}', '{}', 'JSON opcional: código da opção da API => D022_Id (ex: {"PAC":"12","SEDEX":"13"}).'
FROM C004
WHERE NOT EXISTS (
    SELECT 1 FROM C031 x WHERE x.C031_C004_Id = C004.C004_Id AND x.C031_Campo = 'apiFreteMapaD022'
);
