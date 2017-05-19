DELIMITER ++ ;
DROP PROCEDURE IF EXISTS temp_cdr_did_2015_02_04 ++
CREATE PROCEDURE temp_cdr_did_2015_02_04 ()
    READS SQL DATA
    MODIFIES SQL DATA
BEGIN
    DECLARE l_existe_columna tinyint(1);
    
    SET l_existe_columna = 0;

    /* Verificar existencia de columna cdr.did que debe agregarse */
    SELECT COUNT(*) INTO l_existe_columna 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'asteriskcdrdb' 
        AND TABLE_NAME = 'cdr' 
        AND COLUMN_NAME = 'did';
    IF l_existe_columna = 0 THEN
        ALTER TABLE cdr
        ADD COLUMN `did` varchar(50) NOT NULL DEFAULT '';
    END IF;
END;
++
DELIMITER ; ++

CALL temp_cdr_did_2015_02_04();
DROP PROCEDURE IF EXISTS temp_cdr_did_2015_02_04;
