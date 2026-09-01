CREATE FUNCTION `T005_Prazos`(xT005_Id int(11)) RETURNS varchar(255)
BEGIN
    DECLARE xTotal_Parcelas INT(2) DEFAULT 0;
    DECLARE xTotal_Prazos VARCHAR(255);
    DECLARE xRetorno VARCHAR(255) DEFAULT 'A COMBINAR';
    DECLARE xPrazos_Dias INT(5) DEFAULT 0;
    DECLARE xNumero_Parcela  INT(3) DEFAULT 0;
    DECLARE xFlag_ACP VARCHAR(1) DEFAULT ' ';
    DECLARE xFlag_Gera_Contas VARCHAR(1) DEFAULT ' ';
    SELECT T005_Flag_ACP,
           D006_Flag_Gera_Contas
      INTO xFlag_ACP,
           xFlag_Gera_Contas
      FROM T005
      LEFT JOIN D006 ON D006_Id=T005_D006_Id
     WHERE T005_Id=xT005_Id;
    IF xFlag_ACP!=2 AND xFlag_Gera_Contas!='N' THEN
       SELECT SUM(1),
              CONCAT(GROUP_CONCAT(T090_Prazos_Dias SEPARATOR '/'),' dias'),
              T090_Prazos_Dias,
              T090_Numero_Parcela
         INTO xTotal_Parcelas,
              xTotal_Prazos,
              xPrazos_Dias,
              xNumero_Parcela
         FROM T090
        WHERE T090_T005_Id=xT005_Id
        GROUP BY T090_T005_Id;

        IF xTotal_Parcelas > 0 THEN
           IF xTotal_Parcelas = 1 AND xPrazos_Dias = 0 THEN
              SET xRetorno = 'A VISTA ANTECIPADO';
           ELSE
              SET xRetorno = xTotal_Prazos;
           END IF;
        ELSE
           SET xRetorno = '';
        END IF;
    END IF;
    RETURN xRetorno;
END


