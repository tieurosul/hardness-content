CREATE FUNCTION `T003_Prazos`(xT003_Id int(11)) RETURNS varchar(255) CHARSET latin1
BEGIN
    DECLARE xTotal_Parcelas INT(2) DEFAULT 0;
    DECLARE xTotal_Prazos VARCHAR(255);
    DECLARE xRetorno VARCHAR(255) DEFAULT '';
    DECLARE xPrazos_Dias INT(5) DEFAULT 0;
    DECLARE xNumero_Parcela  INT(3) DEFAULT 0;
    DECLARE xFlag_ACP VARCHAR(1) DEFAULT ' ';
    DECLARE xFlag_Gera_Contas VARCHAR(1) DEFAULT ' ';

    SELECT T003_Flag_ACP,
           D006_Flag_Gera_Contas
      INTO xFlag_ACP,
           xFlag_Gera_Contas
      FROM T003
      LEFT JOIN D006 ON D006_Id=T003_D006_Id
     WHERE T003_Id=xT003_Id;
       SELECT SUM(1),
              CONCAT(GROUP_CONCAT(T089_Prazos_Dias SEPARATOR '/'),' dias'),
              T089_Prazos_Dias,
              T089_Numero_Parcela
         INTO xTotal_Parcelas,
              xTotal_Prazos,
              xPrazos_Dias,
              xNumero_Parcela
         FROM T089
         WHERE T089_T003_Id=xT003_Id
         GROUP BY T089_T003_Id;
         IF xTotal_Parcelas > 0 THEN
            IF xTotal_Parcelas = 1 AND xPrazos_Dias = 0 THEN
               SET xRetorno = 'A VISTA ANTECIPADO (Opção via cartão crédito em até 6x)';
            ELSE
               SET xRetorno = xTotal_Prazos;
            END IF;
         ELSE
            SET xRetorno = '';
         END IF;

    RETURN xRetorno;
END



