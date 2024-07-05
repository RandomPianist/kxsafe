ALTER TABLE retiradas ADD COLUMN data DATE AFTER observacao;

UPDATE retiradas SET data = DATE(created_at);

DELETE FROM log WHERE tabela = 'retiradas' AND id NOT IN (

SELECT id

FROM (
    SELECT
        MAX(id) AS id,
        tabela,
        fk
        
    FROM log

    WHERE tabela = 'retiradas'

    GROUP BY
        tabela,
        fk
) AS main

);