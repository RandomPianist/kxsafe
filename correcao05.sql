DELETE log

FROM log

JOIN (
    SELECT
        fk,
        MIN(id) AS id
    FROM log
    WHERE tabela = 'retiradas'
    GROUP BY fk
    HAVING COUNT(id) > 1
) AS aux ON aux.id = log.id;