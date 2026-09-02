-- Corrige estrutura P002 (tipo de orcamento) e coluna T003A_P002_Id
-- Backup da tabela P002 antiga (estrutura: P002_P001_Id / P002_D020_Id)

DROP TABLE IF EXISTS P002_backup_cidades;
RENAME TABLE P002 TO P002_backup_cidades;

CREATE TABLE P002 (
    P002_Id int unsigned NOT NULL AUTO_INCREMENT,
    P002_Tipo_Orcamento varchar(255) NOT NULL,
    PRIMARY KEY (P002_Id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Tipos de orcamento (personalizacao ven001)';

INSERT INTO P002 (P002_Id, P002_Tipo_Orcamento) VALUES
    (1, 'Padrao'),
    (2, 'Revenda'),
    (3, 'Consumidor'),
    (4, 'Exportacao');

SET @col_exists = (
    SELECT COUNT(*)
      FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'T003A'
       AND COLUMN_NAME = 'T003A_P002_Id'
);

SET @sql = IF(
    @col_exists = 0,
    'ALTER TABLE T003A ADD COLUMN T003A_P002_Id int unsigned DEFAULT NULL',
    'SELECT ''T003A_P002_Id ja existe'' AS info'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
