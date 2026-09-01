-- Register C031 key for external freight quote API (VEN001).
INSERT INTO C031 (C031_C004_Id, C031_Campo, C031_Valor_Padrao, C031_Valor, C031_Observacao)
SELECT C004_Id, 'apiFreteUrl', '', '', 'URL do endpoint de cotação de frete (orçamento VEN001).'
FROM C004
WHERE NOT EXISTS (
    SELECT 1 FROM C031 x WHERE x.C031_C004_Id = C004.C004_Id AND x.C031_Campo = 'apiFreteUrl'
);
