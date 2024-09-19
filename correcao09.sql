UPDATE maquinas_produtos AS main
JOIN (
    SELECT
        qtd,
        id_mp

    FROM estoque

    JOIN (
        SELECT MIN(id) AS id

        FROM estoque

        WHERE es = 'E'

        GROUP BY id_mp
    ) AS aux ON aux.id = estoque.id
) AS estq ON estq.id_mp = main.id
SET minimo = qtd;